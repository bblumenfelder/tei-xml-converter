<?php


namespace App\ServiceClasses\Glossarium\Auxilium;


use App\APIs\Morph\api_classes\CLTK;
use App\APIs\Morph\Perseids;
use App\APIs\Morph\Wordnet;

abstract class Auxilium {

    /**
     * @param array|string $formen
     * @return \Illuminate\Support\Collection
     */
    public static function ask($formen)
    {
        $formen = collect($formen);
        try {
            return $formen->mapToGroups(function ($form) {
                try {
                    $analysis = self::analyze($form);
                } catch (\Exception $exception) {
                    $analysis = [];
                }

                return [$form => $analysis];
            });
        } catch (\Exception $exception) {
            return $formen->mapToGroups(function ($form) {
                return [$form => collect()];
            });
        }
    }



    public static function analyze(string $form)
    {
        return self::api($form);
    }



    public static function api(string $form)
    {
        $LemmataBag = collect(json_decode(Wordnet::lemmatize($form), true));

        return $LemmataBag->map(function ($LemmaBag) {
            $LemmaForm = $LemmaBag['lemma']['lemma'];
            $LemmaMorph = $LemmaBag['lemma']['morpho'];
            $POSLetter = $LemmaMorph[0];
            $Wordnet_Analysis = Wordnet::stemPOS($LemmaForm, $POSLetter);
            $PrincipalParts = explode(' ', $Wordnet_Analysis['principal_parts']);
            $IrregularForms = explode(' ', $Wordnet_Analysis['irregular_forms']);
            switch ($POSLetter) {
                case 'a':
                    $Wortart = 'adjektiv';
                    $MorphInfo = Auxilium::extractAdjektivInfo($LemmaForm, $LemmaMorph, $PrincipalParts, $IrregularForms);
                    break;
                case 'n':
                    $Wortart = 'nomen';
                    $MorphInfo = Auxilium::extractSubstantivInfo($LemmaForm, $LemmaMorph, $PrincipalParts);
                    break;
                case 'v':
                    $Wortart = 'verb';
                    $MorphInfo = Auxilium::extractVerbInfo($LemmaForm, $LemmaMorph, $PrincipalParts);
                    break;
                default:
                    $Wortart = 'aliud';
                    $MorphInfo = Auxilium::extractAndereInfo($LemmaForm, $LemmaMorph);
                    break;
            }



            return ((collect())->put('lemma', $LemmaForm)->put('wortart', $Wortart)->put('MorphInfo', $MorphInfo));
        });
    }



    public static function extractAdjektivInfo($LemmaForm, $LemmaMorph, $PrincipalParts, $IrregularForms)
    {
        $KomparativSuperlativ = collect(['komparativ' => '', 'superlativ' => '']);
        (collect($IrregularForms))->each(function ($IrregularForm, $Key) use ($KomparativSuperlativ) {
            $MorphoFormArray = explode('=', $IrregularForm);
            if (substr($MorphoFormArray[0], 0, -7) === 'acs' && $MorphoFormArray[0][7] === 'n' && $Key == 0) {
                $KomparativSuperlativ->put('komparativ', $MorphoFormArray[1]);
            };
            if (substr($MorphoFormArray[0], 0, -7) === 'ass' && $MorphoFormArray[0][7] === 'n' && $MorphoFormArray[0][6] === 'm') {
                $KomparativSuperlativ->put('superlativ', $MorphoFormArray[1]);
            };
        });
        if ($KomparativSuperlativ['komparativ'] === '' || $KomparativSuperlativ['superlativ'] === '') {
            $KomparativSuperlativ = Auxilium::extractKomparativSuperlativ($LemmaForm);

        }
        $LookupTableEndungen = [
            'a' => '1endig',
            'c' => '2endig',
            'm' => '3endig',
            'f' => '3endig',
            'n' => '3endig',
        ];

        return [
            'lemma' => $LemmaForm,
            'fb_stamm' => $PrincipalParts[0] ?? '',
            'fb_dklasse' => strval($LemmaMorph[8]) === '1' ? 'ao' : '3kons',
            'fb_genera' => $LookupTableEndungen[ $LemmaMorph[7] ],
            'fb_hat_komparativ' => $KomparativSuperlativ['komparativ'] !== '',
            'fb_komparativ' => $KomparativSuperlativ['komparativ'] ?? '',
            'fb_hat_superlativ' => $KomparativSuperlativ['superlativ'] !== '',
            'fb_superlativ' => $KomparativSuperlativ['superlativ'] ?? '',
        ];
    }



    public static function extractSubstantivInfo($LemmaForm, $LemmaMorph, $PrincipalParts)
    {
        $GenusLookupTable = [
            "m" => "m",
            "f" => "f",
            "n" => "n",
            "c" => "mf"];
        $DKlasseLookupTable = [
            "1" => "a",
            "2" => "o",
            "3" => "3dekl",
            "4" => "u",
            "5" => "e",
        ];

        return [
            'lemma' => $LemmaForm,
            'fb_stamm' => $PrincipalParts[0] ?? '',
            'fb_genus' => $GenusLookupTable[ strval($LemmaMorph[6]) ],
            'fb_dklasse' => $DKlasseLookupTable[ strval($LemmaMorph[8]) ],
        ];

    }



    public static function extractAndereInfo($LemmaForm, $LemmaMorph)
    {
        $PerseidsResult = Perseids::tag($LemmaForm);
        $KKlasseLookupTable = [
            'pronoun' => 'pronomen',
            'conjunction' => 'subj',
            'adverb' => 'adv',
            'preposition' => 'praep',

        ];

        return [];
    }



    public static function extractVerbInfo($LemmaForm, $LemmaMorph, $PrincipalParts)
    {

        $KKlasseLookupTable = [
            '1' => 'a',
            '2' => 'e',
            '3' => 'k',
            '4' => 'i',
        ];
        $StammerweiterungLookupTable = [
            'a' => 'a',
            'e' => 'e',
            'k' => '',
            'ki' => 'i',
            'i' => 'i',
        ];
        $InfinitivAktivEndungLookupTable = [
            'a' => 'are',
            'e' => 'ere',
            'k' => 'ere',
            'ki' => 'ere',
            'i' => 'ire',
        ];
        $InfinitivPassivEndungLookupTable = [
            'a' => 'ari',
            'e' => 'eri',
            'k' => 'i',
            'ki' => 'i',
            'i' => 'iri',
        ];

        $Wordnet_Kklasse = $KKlasseLookupTable[ strval($LemmaMorph[8]) ];
        $fb_kklasse = $LemmaMorph[9] === 'i' ? 'ki' : $Wordnet_Kklasse;
        $fb_ppp = $PrincipalParts[2] ? $PrincipalParts[2] . 'us' : '';
        $fb_pfa = $PrincipalParts[2] ? $PrincipalParts[2] . 'urus' : '';
        $fb_ist_deponens = $LemmaMorph[5] == 'd';
        if ($fb_ist_deponens) {
            $InfinitivForm = $PrincipalParts[0] . $InfinitivPassivEndungLookupTable[ $fb_kklasse ];
            $fb_1pssgpf = $PrincipalParts[1] !== '-' ? $PrincipalParts[1] . 'us sum' : '';
        }
        else {
            $InfinitivForm = $PrincipalParts[0] . $InfinitivAktivEndungLookupTable[ $fb_kklasse ];
            $fb_1pssgpf = $PrincipalParts[1] !== '-' ? $PrincipalParts[1] . 'i' : '';
        }

        return [
            'lemma' => $InfinitivForm,
            'fb_kklasse' => $fb_kklasse,
            'fb_stamm' => ($PrincipalParts[0] ?? '') . $StammerweiterungLookupTable[ $Wordnet_Kklasse ],
            'fb_ist_deponens' => $fb_ist_deponens,
            'fb_1pssgpr' => $LemmaForm,
            'fb_1pssgpf' => Auxilium::replaceSemivowels($fb_1pssgpf),
            'fb_ppp' => $fb_ppp !== '-us' ? $fb_ppp : '',
            'fb_hat_ppp' =>  $fb_ppp !== '' && $fb_ppp !== '-us' ? 1 : 0,
            'fb_pfa' => $fb_pfa !== '-urus' ? $fb_pfa : '',
            'fb_hat_pfa' => $fb_pfa !== '' && $fb_pfa !== '-urus' ? 1 : 0,
        ];
    }



    private static function extractKomparativSuperlativ($LemmaForm)
    {
        $CompleteDeclension = collect(json_decode(CLTK::decline($LemmaForm), true));
        $Komparativ = collect();
        $Superlativ = collect();
        $CompleteDeclension->each(function ($DeclensionArray) use ($Komparativ, $Superlativ) {
            //[0] represents Form
            //[1] represents morpho-Tag
            if (substr($DeclensionArray[1], -3) === 'mnc' && $DeclensionArray[1][2] === 's') {
                $Komparativ->push($DeclensionArray[0]);
            };
            if (substr($DeclensionArray[1], -3) === 'mns' && $DeclensionArray[1][2] === 's') {
                $Superlativ->push($DeclensionArray[0]);
            };
        });

        return [
            'komparativ' => $Komparativ[0] ?? '',
            'superlativ' => $Superlativ[0] ?? '',
        ];
    }



    private static function replaceSemivowels(string $string)
    {
        $search = ['aui', 'eui', 'iui', 'uui'];
        $replace = ['avi', 'evi', 'ivi', 'uvi'];

        return str_replace($search, $replace, $string);
    }
}
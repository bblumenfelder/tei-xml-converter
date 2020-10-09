<?php


namespace App\ServiceClasses\Glossarium\VocabCheck;


use App\APIs\Morph\MorphAPI;
use App\Exports\VocabCheckDeclinationExport;
use App\Traits\Services\comparesMorphoStrings;
use App\Traits\Services\findsVocab;
use Maatwebsite\Excel\Facades\Excel;

//use Maatwebsite\Excel\Excel;

class VocabCheck {

    use comparesMorphoStrings;
    use findsVocab;

    /**
     * @var string
     */
    private static $source;
    /**
     * @var MorphAPI
     */
    private static $MorphAPI;
    /**
     * @var MorphAPI
     */
    private static $HermeneusAPI;
    private static $Result_Wort_AlleFormen;
    /**
     * @var \Illuminate\Support\Collection
     */
    private static $Result_Vocab_Alle;
    /**
     * @var string
     */
    private static $wortart;
    /**
     * @var \Illuminate\Support\Collection
     */
    private static $Result_Vocab_False;
    /**
     * @var \Illuminate\Support\Collection
     */
    private static $Result_Vocab_True_Probable;
    /**
     * @var bool
     */
    private static $separated = false;
    /**
     * @var string
     */
    private static $API;



    /**
     * VocabCheck constructor.
     * @param string $source
     * @param string $API
     * @return static
     */
    public static function source(string $source = "db", string $API)
    {
        static::$source = $source;
        static::$API = $API;

        return new static;
    }



    /**
     * @param string $wortart
     * @param array $ids
     * @return static
     */
    public static function checkByDecl(string $wortart, array $ids = [])
    {
        $vocab = static::findVocabStatic($wortart, $ids);
        static::$wortart = $wortart;
        static::$Result_Vocab_Alle = $vocab->map(function ($wort) {
            $AlleFormen = $wort->append("alle_formen")->alle_formen;
            $Morpho = static::getDelinationAPI($wort);

            return $AlleFormen->map(function ($Form) use ($Morpho, $wort) {
                $Result_EinzelneForm = collect();
                $HermeneusPOS = static::getHermeneusPOS($Form);
                $FoundForm = $Morpho[ $Form ] ?? ["form" => false];
                $FoundPOS = $Morpho[ $Form ]["morpho"] ?? "-";
                $Result_EinzelneForm->put('Lemma', $wort->lemma);
                $Result_EinzelneForm->put('Form_hermeneus', $Form);
                $Result_EinzelneForm->put("Form_API", $FoundForm["form"]);
                $Result_EinzelneForm->put("POS_API", $FoundPOS);
                $Result_EinzelneForm->put("POS_hermeneus", $HermeneusPOS);
                $Result_EinzelneForm->put('hasCorrectPOS', static::hasCorrectPOS($FoundPOS, $HermeneusPOS));

                return $Result_EinzelneForm;
            });


        })->flatten(1);

        return new static;
    }



    /**
     * Is Hermeneus-POS concordant, probably concordant or concordant?
     * @param $FoundPOS
     * @param $HermeneusPOS
     * @return string
     */
    public static function hasCorrectPOS($FoundPOS, $HermeneusPOS)
    {
        if (in_array($FoundPOS, $HermeneusPOS)) {
            return "true";
        }
        else if (static::hasTwoConcordances($FoundPOS, $HermeneusPOS)) {
            return "probable";
        }
        else {
            return "false";
        }
    }



    /**
     * Will create different tables for hasCorrectPOS:"false" and hasCorrectPOS:"true/probable"
     */
    public static function separate()
    {
        static::$separated = true;
        static::$Result_Vocab_True_Probable = collect();

        static::$Result_Vocab_False = static::$Result_Vocab_Alle->filter(function ($VocabCheckResult) {
            if ($VocabCheckResult["POS_API"] !== "-" && $VocabCheckResult["hasCorrectPOS"] == "false") {
                return $VocabCheckResult;
            }
            elseif ($VocabCheckResult["hasCorrectPOS"] != "false") {
                static::$Result_Vocab_True_Probable->push($VocabCheckResult);
            }
        });

        return new static;
    }



    /**
     * Export results
     * @return bool
     */
    public static function export()
    {
        $Filename = "exports\\vocab_checks\\" . date('Y-m-d') . "_VocabCheck_" . static::$API . "_" . static::$wortart;
        if (static::$separated) {
            Excel::store(new VocabCheckDeclinationExport(static::$Result_Vocab_True_Probable), $Filename . "_true_probable.xlsx");
            Excel::store(new VocabCheckDeclinationExport(static::$Result_Vocab_False), $Filename . "_false.xlsx");

        }
        else {
            return Excel::store(new VocabCheckDeclinationExport(static::$Result_Vocab_Alle), $Filename . "_unsorted.xlsx");

        }
    }



    private static function getHermeneusPOS($form)
    {
        return MorphAPI::source("hermeneus")->tag($form)->get()->toArray();
    }



    private static function getDelinationAPI($wort)
    {
        return MorphAPI::source(static::$API)->decline($wort->append("dictionary_headword")->dictionary_headword)->convertDecline();
    }



}
<?php

namespace App\Traits\Glossarium;


use App\Adjektiv;
use App\Eigenname;
use App\Helpers\Helper;
use App\Nomen;
use App\Numerale;
use App\Partikel;
use App\Pronomen;
use App\Verb;
use App\Wendung;
use App\Vocab;
use phpDocumentor\Reflection\Types\Boolean;

trait insertsManyVocabWords {

    /**
     * @param array $Wordlist
     * @param $Morph
     * @return \Illuminate\Support\Collection
     */
    private function massInsert(array $Wordlist, $Morph)
    {
        $InsertedVocab = collect();
        foreach ($Wordlist as $WordObject) {
            $VocabModel = Vocab::$StringModelLookUpTable[ $WordObject['wortart'] ];
            if ( ! empty($WordObject['vocab'])) {
                $Vocab = new $VocabModel($WordObject['vocab']);
                $Vocab->save();
                if ($Vocab->is_morphologisierbar && $Morph === true) {
                    try {
                        $Vocab->morph();
                    } catch (\Exception $exception) {
                        Helper::writeMorphErrorLog('insertsManyVocabWords.php', '[CAUGHT]' . $exception->getMessage());
                    }
                }
                $InsertedVocab->push($Vocab);
            }
        }

        return $InsertedVocab;
    }



    /**
     * Returns Array of vocab-patterns with additional info
     * for mass-inserts (e.g. in Vue-Components)
     * @return array
     */
    private function getVocabBlueprints()
    {
        return ['adjektiv' => new Adjektiv(),
                'nomen' => new Nomen(),
                'numerale' => new Numerale(),
                'partikel' => new Partikel(),
                'pronomen' => new Pronomen(),
                'verb' => new Verb(),
                'wendung' => new Wendung(),
                'eigenname' => new Eigenname(),
        ];
    }
}
<?php


namespace App\ServiceClasses\VocabComposition;

use App\Buch;
use App\Lerneinheit;
use App\Vocab;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Will create a flat list of vocab from an array of vocab, lerneinheiten, reihen and buecher
 * Class VocabComposer
 * @package App\ServiceClasses
 */
class VocabComposer {

    protected $scope;
    protected $list;
    protected $vocab_raw;



    public function __construct(array $scope, bool $vocab_raw = false)
    {
        // Should all vocab-attributes be returned?
        $this->vocab_raw = $vocab_raw;
        $this->scope = $this->sanitize($scope);
        $this->list = collect();
    }



    /**
     * Sanitizes scope with only necessary properties
     * @param $scope
     * @return mixed
     */
    public function sanitize($scope)
    {
        $scopeVocab = collect(array_map(function ($item) {
            return collect([
                'id' => $item['id'],
                'lemma' => $item['lemma'],
                'wortart' => $item['wortart'],
            ]);
        }, $scope['vocab']));
        $scopeLerneinheiten = collect(array_map(function ($item) {
            return collect([
                'id' => $item['id'],
                'name' => $item['name'],
            ]);
        }, $scope['lerneinheiten']));
        $scopeBuecher = collect(array_map(function ($item) {
            return collect([
                'id' => $item['id'],
                'voller_titel' => $item['voller_titel'],
            ]);
        }, $scope['buecher']));
        $scopeReihen = collect(array_map(function ($item) {
            return collect([
                'id' => $item['id'],
                'name' => $item['name'],
            ]);
        }, $scope['reihen']));

        return collect([
            'vocab' => $scopeVocab,
            'lerneinheiten' => $scopeLerneinheiten,
            'buecher' => $scopeBuecher,
            'reihen' => $scopeReihen,
        ]);
    }



    /**
     * Returns list of vocab for scope
     * @return mixed
     */
    public function get()
    {
        return $this->list->unique(function ($wort) {
            return $wort['lemma'] . $wort['id'];
        });
    }



    /**
     * Returns sanitized scope
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }



    /**
     * Creates list from scope
     */
    public function compose()
    {
        $this->composeFromVocab()->composeFromLerneinheiten()->composeFromBuecher()->composeFromReihen();

        return $this;
    }



    public function composeFromVocab()
    {
        $this->scope['vocab']->each(function ($item) {
            $vocabResult = (Vocab::$StringModelLookUpTable[ $item['wortart'] ])::find($item['id']);
            if ($vocabResult->lemma === $item['lemma']) {
                $this->list->push($vocabResult);
            }
        });

        return $this;
    }



    /**
     * If argument given, lerneinheiten are read from it
     * @param bool $lerneinheiten
     * @return $this
     */
    public function composeFromLerneinheiten($lerneinheiten = false)
    {
        if ($lerneinheiten) {
            $lerneinheiten->each(function ($lerneinheit) {
                // All vocab attributes? Or only necessary ones?
                $lerneinheit_vocab = $this->vocab_raw ? $lerneinheit->vocab : $lerneinheit->vocab_raw;
                foreach ($lerneinheit_vocab as $wort) {
                    $this->list->push($wort);

                }
            });
        }
        else {

            $this->scope['lerneinheiten']->each(function ($item) {

                $LerneinheitResult = Lerneinheit::find($item['id']);
                if ($LerneinheitResult && $LerneinheitResult->name === $item['name']) {
                    // All vocab attributes? Or only necessary ones?
                    $lerneinheit_vocab = $this->vocab_raw ? $LerneinheitResult->vocab : $LerneinheitResult->vocab_raw;
                    $lerneinheit_vocab->each(function ($wort) {
                        $this->list->push($wort);
                    });
                }
            });
        }

        return $this;
    }



    /**
     * @param bool $buecher
     * @return $this
     */
    public function composeFromBuecher($buecher = false)
    {
        if ($buecher) {
            $buecher->each(function ($buch) {
                $this->composeFromLerneinheiten($buch->lerneinheiten);
            });
        }
        else {
            $this->scope['buecher']->each(function ($buch) {
                $buch = Buch::find($buch['id']);
                if ($buch) {
                    $this->composeFromLerneinheiten($buch->lerneinheiten);
                }
            });
        }

        return $this;
    }



    public function composeFromReihen()
    {
        $this->scope['reihen']->each(function ($reihe) {
            $buecher = Buch::where('reihe_id', $reihe['id'])->get();
            $this->composeFromBuecher($buecher);
        });

        return $this;
    }
}
<?php


namespace App\ServiceClasses\Grammatik;

use App\Grammatik;
use App\Repositories\VocabRepository;
use App\ServiceClasses\VocabAnalysis\FormAnalysator;
use Illuminate\Support\Arr;

/**
 * Class Morpheus
 * Creates
 * @package App\Repositories
 */
class Morpheus {

    public function fromGrammatikUndLerneinheit(Grammatik $Grammatik, $Lerneinheit)
    {
        $Vocab = $Grammatik->makeVocabCollection($Lerneinheit->id);
        $DotNotationsArrays = $Grammatik->getMorphSelectionScope();

        return Morpheus::filterByMorphSelect($Vocab, $DotNotationsArrays);
    }



    public static function filterByMorphSelect($VocabCollection, $MorphSelect_DotNotationsArray)
    {
        $FormToMorphAttribution = collect();
        $VocabCollection->each(function ($Wort) use ($MorphSelect_DotNotationsArray, $FormToMorphAttribution) {
            foreach ($MorphSelect_DotNotationsArray as $DotNotationString) {
                if (isset($Wort['morph_array'])) {
                    $AvailableFormen = Arr::get($Wort['morph_array'], $DotNotationString);
                    if (is_array($AvailableFormen) && count($AvailableFormen) > 0) {
                        foreach ($AvailableFormen as $Key => $Form) {
                            $FormToMorphAttribution->push((new MorphAttribution($Form, $Wort['lemma'], [$DotNotationString . '.' . $Key]))->get());
                        }
                    }
                    if (is_string($AvailableFormen)) {
                        $FormToMorphAttribution->push((new MorphAttribution($AvailableFormen, $Wort['lemma'], [$DotNotationString]))->get());

                    }
                }
            }
        });

        return $FormToMorphAttribution;
    }



    /**
     * Wenn die Grammatik nur Dativformen auslesen soll, sollen trotzdem Ablativformen als Lösung möglich sein, aber nicht Formen eines anderen Lemmas!
     * Also: servis => Abl. Pl. und Dat. Pl. von serva/servus
     * Aber nicht: servis => 2. Ps. Sg. Präs. Ind. von servire
     * @param $VocabCollection
     * @param Grammatik $Grammatik
     */
    public function forFormToMorphAttribution($VocabCollection, Grammatik $Grammatik)
    {
        $MorphAttributionContainer = collect();
        $DotNotationsArrays = $Grammatik->getMorphSelectionScope();
        $VocabCollection->each(function ($Wort) use ($MorphAttributionContainer, $DotNotationsArrays) {
            foreach ($DotNotationsArrays as $DotNotationString) {
                if (isset($Wort['morph_array'])) {
                    $AvailableFormenOrForm = Arr::get($Wort['morph_array'], $DotNotationString);

                    if (is_array($AvailableFormenOrForm) && count($AvailableFormenOrForm) > 0) {
                        foreach ($AvailableFormenOrForm as $Key => $Form) {
                            // ['form'] =  Form
                            // ['morph'] =  reverse-analyzed Formanalysis, but only $Wort as scope
                            $FormAnalysis = (new FormAnalysator([], (new VocabRepository())->find($Wort['route_name'], $Wort['id'])->get()))->analyze($Form)->get();
                            $MorphoStrings = ($FormAnalysis->first()['morpho'])->map(function ($MorphoArray) {
                                return implode(".", $MorphoArray);
                            })->toArray();
                            $MorphAttributionContainer->push((new MorphAttribution($Form, $Wort['lemma'], $MorphoStrings))->get());
                        }
                    }
                    if (is_string($AvailableFormenOrForm)) {
                        $FormAnalysis = (new FormAnalysator([], (new VocabRepository())->find($Wort['route_name'], $Wort['id'])->get()))->analyze($AvailableFormenOrForm)->get();
                        $MorphoStrings = ($FormAnalysis->first()['morpho'])->map(function ($MorphoArray) {
                            return implode(".", $MorphoArray);
                        })->toArray();
                        $MorphAttributionContainer->push((new MorphAttribution($AvailableFormenOrForm, $Wort['lemma'], $MorphoStrings))->get());

                    }
                }
            }
        });

        return $MorphAttributionContainer;
    }



    public function fromFormCollection($FormCollection)
    {
        return $FormCollection->map(function ($Form, $Key) {
            $FormAnalysisContainer = (new FormAnalysator())->analyze($Form)->get();
            $MorphoArrays = $FormAnalysisContainer->map(function ($FormAnalysis) {
                return ($FormAnalysis['morpho'])->flatten();
            });
            $MorphoArrayOfDotNotationStrings = $MorphoArrays->map(function ($MorphoArray) {
                return $MorphoArray->join('.');
            });

            return (new MorphAttribution($Form, $FormAnalysisContainer[0]['lemma'], $MorphoArrayOfDotNotationStrings->unique()->toArray()))->get();
        });
    }



}
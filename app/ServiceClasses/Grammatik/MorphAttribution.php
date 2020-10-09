<?php


namespace App\ServiceClasses\Grammatik;

/**/



/**
 * Class MorphAttribution
 * [
 *  "form" => "laudabat",
 *  "lemma" => "laudare",
 *  "morph" =>
 *      [
 *          "1_aktiv.2_imperfekt.1_indikativ.3_sg3",
 *      ]
 *  ];
 * @package App\ServiceClasses\Grammatik
 */
class MorphAttribution {

    private $MorphAttribution;



    public function __construct(string $Form, string $Lemma, array $MorphArrays)
    {
        $this->MorphAttribution = [
            'form' => $Form,
            'lemma' => $Lemma,
            'morph' => $MorphArrays,
        ];

    }



    public function get()
    {
        return $this->MorphAttribution;
    }
}
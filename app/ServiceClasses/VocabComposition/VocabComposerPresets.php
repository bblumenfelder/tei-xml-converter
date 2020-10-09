<?php


namespace App\ServiceClasses\VocabComposition;


/**
 * Reserved VocabCompositions
 *
 * Class VocabComposerPresets
 * @package App\ServiceClasses\VocabComposition
 */
class VocabComposerPresets {

    CONST PRESETS = [
        'vokabelauswahl_adeo_norm' => [
            'id' => 1001,
            'name' => 'vokabelauswahl_adeo_norm',
            'type' => 'preset',
            'public' => 1,
            'user_id' => 100,
            'scope' => [
                "vocab" => [],
                "lerneinheiten" => [
                    ["id" => 28,
                     "name" => "adeo"

                    ]
                ],
                "buecher" => [],
                "reihen" => [],
            ]
        ],
        'vokabelauswahl_adeamus_b' => [
            'id' => 2002,
            'name' => 'vokabelauswahl_adeamus_b',
            'type' => 'preset',
            'public' => 1,
            'user_id' => 100,
            'scope' => [
                "vocab" => [],
                "lerneinheiten" => [],
                "buecher" => [],
                "reihen" => [
                    [
                                 "id" => 2,
                                 "name" => "Adeamus B"]
                ],
            ]
        ],


    ];


}
<?php


namespace App\ServiceClasses\Grammatik;


class GrammatikBestimmungen {

    const DATA = [


        // ANDERES
        'normal' => ['short' => 'Norm.', 'long' => 'Normalform'],
        'alternativ' => ['short' => 'Alt.', 'long' => 'Alternativform'],
        // WORTARTEN
        '4_adv' => ['short' => 'Adv.', 'long' => 'Adverb'],
        // MODI
        '0_infinitiv' => ['short' => 'Inf.', 'long' => 'Infinitiv'],
        '1_indikativ' => ['short' => 'Ind.', 'long' => 'Indikativ'],
        '2_konjunktiv' => ['short' => 'Konj.', 'long' => 'Konjunktiv'],
        '3_imperativ' => ['short' => 'Imp.', 'long' => 'Imperativ'],
        '4_partizip' => ['short' => 'Part.', 'long' => 'Partizip'],
        // STEIGERUNG
        '1_pos' => ['short' => 'Pos.', 'long' => 'Positiv'],
        '2_komp' => ['short' => 'Komp.', 'long' => 'Komparativ'],
        '3_superl' => ['short' => 'Superl.', 'long' => 'Superlativ'],
        //CASES
        '1_nom' => ['short' => 'Nom.', 'long' => 'Nominativ'],
        '2_gen' => ['short' => 'Gen.', 'long' => 'Genitiv'],
        '3_dat' => ['short' => 'Dat.', 'long' => 'Dativ'],
        '4_akk' => ['short' => 'Akk.', 'long' => 'Akkusativ'],
        '5_vok' => ['short' => 'Vok.', 'long' => 'Vokativ'],
        '6_abl' => ['short' => 'Abl.', 'long' => 'Ablativ'],
        // GENERA
        '1_mask' => ['short' => 'm.', 'long' => 'maskulin'],
        '2_fem' => ['short' => 'f.', 'long' => 'feminin'],
        '3_neutr' => ['short' => 'n.', 'long' => 'neutral'],
        // NUMERI
        '1_sg' => ['short' => 'Sg.', 'long' => 'Singular'],
        '2_pl' => ['short' => 'Pl.', 'long' => 'Plural'],
        '1_sg1' => ['short' => '1.Ps.Sg.', 'long' => '1. Person Singular'],
        '2_sg2' => ['short' => '2.Ps.Sg.', 'long' => '2. Person Singular'],
        '3_sg3' => ['short' => '3.Ps.Sg.', 'long' => '3. Person Singular'],
        '4_pl1' => ['short' => '1.Ps.Pl.', 'long' => '1. Person Plural'],
        '5_pl2' => ['short' => '2.Ps.Pl.', 'long' => '2. Person Plural'],
        '6_pl3' => ['short' => '3.Ps.Pl.', 'long' => '3. Person Plural'],
        // TEMPORA
        '1_praesens' => ['short' => 'Präs.', 'long' => 'Präsens'],
        '2_imperfekt' => ['short' => 'Impf.', 'long' => 'Imperfekt'],
        '3_perfekt' => ['short' => 'Pf.', 'long' => 'Perfekt'],
        '4_plqpf' => ['short' => 'Plqpf.', 'long' => 'Plusquamperfekt'],
        '4_plusquamperfekt' => ['short' => 'Plqpf.', 'long' => 'Plusquamperfekt'],
        '5_futur' => ['short' => 'Fut.', 'long' => 'Futur'],
        '6_futur2' => ['short' => 'Fut. II', 'long' => 'Futur II'],
        // GENERA VERBI
        '1_aktiv' => ['short' => 'Akt.', 'long' => 'Aktiv'],
        '2_passiv' => ['short' => 'Pass.', 'long' => 'Passiv'],
        '3_gerundium' => ['short' => 'Gerund', 'long' => 'Gerund'],
        '4_gerundiv' => ['short' => 'Gerundiv', 'long' => 'Gerundiv'],
        '5_supin' => ['short' => 'Sup.', 'long' => 'Supin'],
        '1_supin1' => ['short' => 'Sup1.', 'long' => 'Supin 1'],
        '2_supin2' => ['short' => 'Sup2.', 'long' => 'Supin 2'],
        // NUMERALIA
        '4_multiplikativ' => ['short' => 'multipl.', 'long' => 'multiplikativ'],
        '3_distributiv' => ['short' => 'distribut.', 'long' => 'distributiv'],
        '2_ordinal' => ['short' => 'ord.', 'long' => 'ordinal'],
        '1_kardinal' => ['short' => 'kard.', 'long' => 'kardinal']
    ];


    public static function flattened() {
        return collect(self::DATA)->map(function ($Element, $ID) {
            return [
              'id' => $ID,
              'long' => $Element['long'],
              'short' => $Element['short'],
            ];
        })->values();
    }


}
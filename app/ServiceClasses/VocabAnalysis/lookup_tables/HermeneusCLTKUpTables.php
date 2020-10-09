<?php


namespace App\ServiceClasses\VocabAnalysis\lookup_tables;


trait HermeneusCLTKUpTables {

    public function get_0_POS_isPartikel($Value)
    {
        return [
                   'adv' => 'd',
                   'interj' => 'i',
                   'interrog' => 'd',
                   'konj' => 'c',
                   'neg' => 'd',
                   'praep' => 'r',
                   'subj' => 'c'
               ][ $Value ] ?? false;
    }



    public function get_0_POS($Value)
    {
        return [
                   "nomen" => "n", //	noun
                   "verb" => "v", //	verb
                   "adjektiv" => "a", //	adjective
                   "pronomen" => "p", //	pronoun
                   "numerale" => "m", //	numeral
               ][ $Value ] ?? false;
    }



    public function get_1_Person($Value)
    {
        return [
                   "1_sg1" => "1",
                   "2_sg2" => "2",
                   "3_sg3" => "3",
                   "4_pl1" => "1",
                   "5_pl2" => "2",
                   "6_pl3" => "3",
               ][ $Value ] ?? false;
    }



    public function get_2_Number($Value)
    {
        return [
                   "1_sg" => "s",
                   "1_sg1" => "s",
                   "2_sg2" => "s",
                   "3_sg3" => "s",
                   "2_pl" => "p",
                   "4_pl1" => "p",
                   "5_pl2" => "p",
                   "6_pl3" => "p",
               ][ $Value ] ?? false;
    }



    public function get_3_Tense($Value)
    {
        return [
                   "1_praesens" => "p",
                   "2_imperfekt" => "i",
                   "3_perfekt" => "r",
                   "4_plusquamperfekt" => "l",
                   "5_futur" => "f",
                   "6_futur2" => "t",
               ][ $Value ] ?? false;
    }



    public function get_4_Mood($Value)
    {
        return [
                   "0_infinitiv" => "n",            //infinitive
                   "1_indikativ" => "i",            //indicative
                   "2_konjunktiv" => "s",            //subjunctive
                   "3_imperativ" => "m",            //imperative
                   "4_partizip" => "p",            //participle
                   "3_gerundium" => "d",            //gerund
                   "4_gerundiv" => "g",            //gerundive
                   "5_supin" => "u",                //supine
               ][ $Value ] ?? false;
    }



    public function get_5_Voice($Value)
    {
        return [
                   '1_aktiv' => 'a',
                   '2_passiv' => 'p',
               ][ $Value ] ?? false;
    }



    public function get_6_Gender($Value)
    {
        return [
                   "1_mask" => "m",
                   "2_fem" => "f",
                   "3_neutr" => "n",
                   "m" => "m",
                   "mf" => "m",
                   "f" => "f",
                   "n" => "n",
               ][ $Value ] ?? false;
    }



    public function get_7_Case($Value)
    {
        return [
                   "1_nom" => "n", //  			nominative
                   "nom" => "n", //  			nominative
                   "2_gen" => "g", //  			genitive
                   "gen" => "g", //  			genitive
                   "3_dat" => "d", //  			dative
                   "dat" => "d", //  			dative
                   "4_akk" => "a", //  			accusative
                   "akk" => "a", //  			accusative
                   "5_vok" => "v", //  			ablative
                   "vok" => "v", //  			ablative
                   "6_abl" => "b", //  			vocative
                   "abl" => "b", //  			vocative
               ][ $Value ] ?? false;
    }



    public function get_8_Degree($Value)
    {
        return [
                   "1_pos" => "p",
                   "2_komp" => "c",
                   "3_superl" => "s",
               ][ $Value ] ?? false;
    }
}
<?php

namespace App\Helpers;

use App\Grammatik;
use App\User;

class Helper {

    /**
     * Wandelt Kurz-Strings wie 'inf' in die ausgeschriebene Variante 'Infinitiv' um
     * @param string $AbgekuerzterBegriff
     * @return string $AusgeschriebenerBegriff
     */
    public static function formatGrammatik($AbgekuerzterBegriff)
    {
        $LookUpTable = Grammatik::$GrammatikBegriffeLookupTable;

        if ( ! key_exists($AbgekuerzterBegriff, $LookUpTable)) {
            return $AbgekuerzterBegriff;
        }
        $AusgeschriebenerBegriff = $LookUpTable[ $AbgekuerzterBegriff ];

        return $AusgeschriebenerBegriff;
    }



    /**
     * Die Kategorien zu allen möglichen Bestimmungen ausgeben
     * @param $Bestimmungen
     * @return array $MoeglicheKategorienCollection
     */
    public static function getGrammatikKategorie($Bestimmungen)
    {

        $KategorienLookUpTable = Grammatik::$GrammatikKategorien;
        $MoeglicheKategorienCollection = collect();
        $KategorienCollection = collect();

        // Die Kategorien in einer Collection sammeln
        foreach ($Bestimmungen as $Key => $Bestimmung) {
            foreach ($Bestimmung as $Bestimmungskey => $Bestimmungsterm) {
                $Kategorie = $KategorienLookUpTable [ $Bestimmungsterm ];
                $KategorienCollection = $KategorienCollection->push($Kategorie);
            }
            // Diese Collection wiederum sammeln
            $MoeglicheKategorienCollection = $MoeglicheKategorienCollection->push($KategorienCollection);
            $KategorienCollection = collect();
        }

        // Nur einzigartige Elemente zurückgeben
        $MoeglicheKategorienCollection = $MoeglicheKategorienCollection->unique();

        return $MoeglicheKategorienCollection->toArray();

    }



    /**
     * 2018-08-18 will be formatted to 18.08.2018
     * @param string $Date
     * @return string
     */
    public static function formatToGermanDate(string $Date)
    {
        return (new \DateTime($Date))->format('d.m.Y');
    }



    /**
     * Gibt von einem multidimensionalen Array alle Werte als Array zurück
     * @param array $MultidimensionalArray
     * @param int $preserve_keys
     * @param array $newArray
     * @return array $newArray
     */
    public static function array_flatten($MultidimensionalArray, $preserve_keys = 0, &$newArray = Array())
    {
        foreach ($MultidimensionalArray as $key => $child) {
            if (is_array($child)) {
                $newArray = self::array_flatten($child, $preserve_keys, $newArray);
            }
            elseif ($preserve_keys + is_string($key) > 1) {
                $newArray[ $key ] = $child;
            }
            else {
                $newArray[] = $child;
            }
        }

        return $newArray;
    }



    /**
     * Sucht im vorgegebenen Array nach $Value und gibt die entsprechenden Keys als ARRAY zurück
     * @param string $Value
     * @param array $MultidimensionalArray
     * @param boolean $strict
     * @param array $Path
     * @return array|boolean
     */
    public static function get_array_keys($Value, $MultidimensionalArray, $strict = false, $Path = array())
    {
        if ( ! is_array($MultidimensionalArray)) {
            return false;
        }

        foreach ($MultidimensionalArray as $key => $val) {
            if (is_array($val) && $subPath = self::get_array_keys($Value, $val, $strict, $Path)) {
                $Path = array_merge($Path, array($key), $subPath);

                return $Path;
            }
            elseif (( ! $strict && $val == $Value) || ($strict && $val === $Value)) {
                $Path[] = $key;

                return $Path;
            }
        }

        return false;
    }



    /**
     * Sucht im vorgegebenen Array nach $Value und gibt die entsprechenden Keys als ARRAY zurück
     * @param string $Value
     * @param array $MultidimensionalArray
     * @param boolean $strict
     * @param array $Path
     * @return array|boolean
     */
    public static function get_all_array_keys($Value, $MultidimensionalArray, $Path = array(), $Results = array())
    {
        if ( ! is_array($MultidimensionalArray)) {
            return false;
        }

        foreach ($MultidimensionalArray as $key => $val) {
            if (is_array($val) && $subPath = self::get_array_keys($Value, $val, $Path, $Results)) {
                $Path = array_merge($Path, array($key), $subPath);

                $Results[] = $Path;
                //dump($Path);
                //return $Path;
            }
            elseif ($val == $Value) {
                $Path[] = $key;

                $Results[] = $Path;
            }
        }

        return $Results;
    }



    /**
     * Sucht im vorgegebenen Array nach $Value und gibt den dazugefundenen Key zurück
     * @param string $needle
     * @param array $haystack
     * @param array $Results
     * @param string $currentKey
     * @return array|boolean
     */
    public static function recursive_array_search($needle, $haystack, $Results = [], $currentKey = '')
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $nextKey = self::recursive_array_search($needle, $value, $Results, $currentKey . '.' . $key);
                if ($nextKey) {
                    return $nextKey;
                }
            }
            if ($value == $needle) {
                return $FoundKeys = $currentKey . '.' . $key;
                //return is_numeric($key) ? $currentKey . '/' . $key : $currentKey . '/' . $key;
            }

        }
    }



    /**
     * Sucht im vorgegebenen Array nach $Value (unabhängig von Groß- und Kleinschreibung) und gibt den dazugefundenen Key zurück
     * @param string $needle
     * @param array $haystack
     * @param array $Results
     * @param string $currentKey
     * @return array|boolean
     */
    public static function recursive_array_search_case($needle, $haystack, $Results = [], $currentKey = '')
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $nextKey = self::recursive_array_search_case($needle, $value, $Results, $currentKey . '.' . $key);
                if ($nextKey) {
                    return $nextKey;
                }
            }
            if (is_string($value)) {

                if (strcasecmp($value, $needle) == 0) {
                    return $FoundKeys = $currentKey . '.' . $key;
                }
            }

        }
    }



    public static function array_filter_recursive($Form, $Array, $InitialArray)
    {

        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $nextKey = self::array_filter_recursive($Form, $value, $InitialArray);
                if ($nextKey) {
                    return $nextKey;
                }
            }
            else if ($value == $Form) {

                $FilteredArray = array_filter($InitialArray, function ($k) {

                    foreach ($k as $val) {
                        if ( ! is_array($val)) {
                            continue;
                        }
                        else {
                            dump($val);
                        }
                    }

                });

            }
        }

    }



    /**
     * Sucht im vorgegebenen Array nach $Value und gibt die entsprechenden Keys als STRING zurück
     * @param string $Value
     * @param array $MultidimensionalArray
     * @param string $currentKey
     * @return string
     */
    public
    function get_array_keys_as_string($Value, $MultidimensionalArray, $currentKey = '')
    {
        foreach ($MultidimensionalArray as $key => $val) {
            if (is_array($val)) {
                $nextKey = $this->get_array_keys_as_string($Value, $val, $currentKey . '[' . $key . ']');
                if ($nextKey) {
                    return $nextKey;
                }
            }
            else if ($val == $Value) {
                return is_numeric($key) ? $currentKey . '[' . $key . ']' : $currentKey . '["' . $key . '"]';
            }
        }

        return false;
    }



    public
    static function searchMultidimensionalArray($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value OR (is_array($value) && self::searchMultidimensionalArray($needle, $value))) {
                return $current_key;
            }
        }

        return false;
    }



    /**
     * @param $Array_Of_Strings
     * @return array
     */
    public static function eliminateStringsContainedByOtherStringsInArray(array $Array_Of_Strings)
    {
        array_multisort(array_map('strlen', $Array_Of_Strings), $Array_Of_Strings);
        $Clean_Array = [];
        $GluedString = implode(' ', $Array_Of_Strings);
        foreach ($Array_Of_Strings as $String) {
            if (substr_count($GluedString, $String) <= 1) {
                array_push($Clean_Array, $String);
            }
        }

        return $Clean_Array;
    }



    /**
     * Verzeichnis öffnen
     * @param $DirectoryString
     * @return bool|resource
     */
    public static function getDirectory($DirectoryString)
    {
        chdir($DirectoryString);
        $Directory = opendir($DirectoryString);

        return $Directory;
    }



    /**
     * Liest das Verzeichnis aus und überträgt alle Dateinamen in eine Collection
     * @param $Directory
     * @return $this|Collection
     */
    public static function getDirectoryFiles($Directory)
    {
        $Files = collect();
        while ($Filename = readdir($Directory)) {

            if (is_file($Filename)) {

                $Files = $Files->push($Filename);
            }
        }

        return $Files;
    }



    /**
     * Check in authors_abbrev_lookup.json
     * @param string $AuthorString
     * @return string
     */
    public static function getAuthorAbbreviation(string $AuthorString)
    {
        foreach (collect(json_decode(file_get_contents(Path::file_corpus_author_abbrev_lookup()))[0])->toArray() as $AuthorSubstring => $AuthorAbbrev) {
            if (strstr($AuthorString, $AuthorSubstring)) {
                return $AuthorAbbrev;
            }
        }

        return '[null]';
    }



    /**
     * Returns common German author name for an abbreviation
     * @param string $Abbreviation
     * @return mixed
     */
    public static function getCommonAuthorName(string $Abbreviation)
    {
        if ($Abbreviation === '[null]') {
            return 'No Abbreviation!';
        }

        return (collect(json_decode(file_get_contents(Path::file_corpus_author_common_lookup()))[0])->toArray())[ $Abbreviation ];
    }



    /**
     * Returns highest_subsection name for PHI_ID
     * @param string $Phi_ID
     * @return mixed
     */
    public static function getHighestSubsection(string $Phi_ID)
    {
        return json_decode(file_get_contents(Path::file_corpus_highest_subsection_lookup()))[0]->phi_id_to_highest_subsection->$Phi_ID ?? null;
    }



    /**
     * Returns "Buch" for "book" etc...
     * @param string $highest_subsection
     * @return mixed
     */
    public static function getSubsectionDescription(string $highest_subsection)
    {
        return json_decode(file_get_contents(Path::file_corpus_highest_subsection_lookup()))[0]->highest_subsection_to_german_description->$highest_subsection ?? null;
    }



    /**
     * Check in title_abbrev_lookup.json
     * @param string $Title
     * @return string
     */
    public static function getTitleAbbreviation(string $Title)
    {
        foreach (collect(json_decode(file_get_contents(Path::file_corpus_title_abbrev_lookup()))[0])->toArray() as $TitleSubstring => $TitleAbbrev) {
            if (strstr(trim(mb_strtolower($Title)), trim(mb_strtolower($TitleSubstring)))) {
                return $TitleAbbrev;
            }
        }

        return '';
    }



    /**
     * 'phi2331.phi020.perseus-lat2.xml' will become 't23310202'
     * @param string $TEIXML_Filename
     * @return string
     */
    public static function getPHI_ID(string $TEIXML_Filename)
    {
        $Phi_ID = '';
        if (preg_match_all('[(\d)]', $TEIXML_Filename, $Digit_List)) {
            $Phi_ID = implode($Digit_List[0]);
        }
        else {
            $Phi_ID = 'invalid';
        }

        return 't' . $Phi_ID;
    }



    /**
     * Inverts boolean value
     * @param $Value
     * @return mixed
     */
    public static function invertBoolean($Value)
    {
        $Inverted = [
            0 => 1,
            1 => 0,
            false => true,
            true => false,
        ];

        return $Inverted[ $Value ];
    }



    /**
     * Update this function when auth is configured
     * @return User
     */
    public static function getCurrentUser()
    {
        return auth()->user();
        //return User::find(1);
        //return factory(User::class)->create(['name' => 'text-user-from-helper-function']);

    }



    /**
     * If user is logged in: Hello, User!
     * If user is not logged in: Hello!
     * @return string
     */
    public static function getUsernameInterjection() {
        return auth()->user() ? ", " . auth()->user()->username : "";
    }



    /**
     * Update this function when auth is configured
     * @return string
     */
    public static function getUsernameOrGuestString()
    {
        return auth()->user() ? auth()->user()->username : 'guest';

    }



    /**
     * Only Laravel log
     * @param bool $splitted
     * @return array|false|string
     */
    public static function getLaravelLog($splitted = false)
    {
        try {
            $logtext = file_get_contents(Path::path_logs() . 'laravel.log');

            return $splitted ? collect(explode('"}', $logtext)) : $logtext;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }



    /**
     * Read all logfiles (if existent)
     * @return array
     */
    public static function getLogfiles()
    {
        try {
            $texte = file_get_contents(Path::path_logs() . 'texte_errors.log');
        } catch (\Exception $exception) {
            $texte = '[Not found]';
        }

        try {

            $morph = file_get_contents(Path::path_logs() . 'morph_errors.log');
        } catch (\Exception $exception) {
            $morph = '[Not found]';
        }
        try {

            $general = file_get_contents(Path::path_logs() . 'general_errors.log');
        } catch (\Exception $exception) {
            $general = '[Not found]';
        }


        return [
            'texte' => $texte,
            'morph' => $morph,
            'general' => $general,
            'laravel' => Helper::getLaravelLog(true)
        ];

    }



    /**
     * @param string $String
     * @return false|int
     */
    public static function containsInteger(string $String)
    {
        return preg_match("/[0-9]/", $String);
    }



    /**
     * Returns array of punctuation marks
     * @return mixed
     */
    public static function getPunctuationMarks()
    {
        return json_decode(file_get_contents(Path::file_punctuation_marks()))->punctuation_marks;
    }



    /**
     * @param string $ErrorMsg
     * @param string $CustomMsg
     */
    public static function writeErrorLog(string $ErrorMsg, string $CustomMsg = '')
    {
        file_put_contents(Path::path_logs() . 'general_errors.log', 'User: [' . Helper::getUsernameOrGuestString() . ']:' .   $CustomMsg . ":\r\n" . $ErrorMsg . "\r\n \r\n", FILE_APPEND);
    }



    /**
     * @param string $ErrorMsg
     * @param string $CustomMsg
     */
    public static function writeTexteErrorLog(string $ErrorMsg, string $CustomMsg = '')
    {
        file_put_contents(Path::path_logs() . 'texte_errors.log', $CustomMsg . ":\r\n" . $ErrorMsg . "\r\n \r\n", FILE_APPEND);
    }



    /**
     * @param string $Filename
     * @param string $ErrorMsg
     */
    public static function writeCorpusErrorLog(string $Filename, string $ErrorMsg)
    {
        file_put_contents(Path::path_logs() . 'texte_errors.log', $Filename . ":\r\n" . $ErrorMsg . "\r\n \r\n", FILE_APPEND);
    }



    /**
     * @param string $Lemma
     * @param string $ErrorMsg
     */
    public static function writeMorphErrorLog(string $Lemma, string $ErrorMsg)
    {
        file_put_contents(Path::path_logs() . 'morph_errors.log', $Lemma . ":\r\n" . $ErrorMsg . "\r\n \r\n", FILE_APPEND);
    }
}

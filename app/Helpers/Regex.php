<?php


namespace App\Helpers;


class Regex {

    /**
     * Replaces ONLY Tags in given Array without their textContent:
     * Explanation: Match everything (all tag-names in brackets) after a < and an optional / (for end-tags)
     * until a > follows. In between \r\t\n\s of any length are allowed.
     * @param array $TagArray
     * @param string $Substitute
     * @param string $String
     * @return null|string|string[]
     */
    public static function replaceTags(array $TagArray, string $Substitute, string $String)
    {
        return preg_replace("/<\/?(" . implode('|', $TagArray) . ")(\r*?\t*?\n*?\s*?.*?)>/", $Substitute, $String);
    }



    /**
     * Replaces Tag AND textContent:
     * Explanation: Match <tag + anything> anything(except if it contains <tag>) including </tag>.
     * @param $TagArrayOrSingleTag
     * @param string $Substitute
     * @param string $String
     * @return null|string|string[]
     */
    public static function replaceTagsAndContent($TagArrayOrSingleTag, string $Substitute, string $String)
    {
        if (is_array($TagArrayOrSingleTag)) {
            $RegexPattern = [];
            foreach ($TagArrayOrSingleTag as $Tag) {
                array_push($RegexPattern, "/<" . $Tag . "(\r*?\t*?\n*?\s*?.*?)>(\r*?\t*?\n*?\s*?.*?)[^<" . $Tag . ">]*<\/" . $Tag . ">/");
            }
        }
        else {
            $RegexPattern = "/<" . $TagArrayOrSingleTag . "(\r*?\t*?\n*?\s*?.*?)>(\r*?\t*?\n*?\s*?.*?)[^<" . $TagArrayOrSingleTag . ">]*<\/" . $TagArrayOrSingleTag . ">/";
        }

        return preg_replace($RegexPattern, $Substitute, $String);
    }
}
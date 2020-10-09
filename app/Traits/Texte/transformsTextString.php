<?php

namespace App\Traits\Texte;


use App\Helpers\Regex;

/**
 * Trait transformsTextString
 * Prepares raw user input for XML-transformation
 * @package App\Traits\Texte
 */
trait transformsTextString {

    /**
     * Everything that will be wrapped in <pc>-Tags
     * @return array
     */
    public function getPunctuationMarks()
    {
        return ['!', '.', ':', '?', ';', ',', '\-', '\–', '"', '´', '`', '’', '„', '“', '»', '«', '«', '»', '‚', '‘', '(', ')'];
    }



    /**
     * Returns array of Words and Punctuation marks
     * @param string $Textstring
     * @return mixed
     */
    public function matchWordsAndPunctuation(string $Textstring)
    {
        $Matches = '';
        // Werden zusammenhängende Wörter oder Delimiter gefunden?
        preg_match_all('/[a-zA-Z]+|[' . join($this->getPunctuationMarks()) . ']/', $Textstring, $Matches);
        $ExtractedWords = $Matches[0];

        return $ExtractedWords;
    }



    /**
     * Returns array of punctuation marks
     * @param string $Textstring
     * @return mixed
     */
    public function matchPunctuationMarks(string $Textstring)
    {
        $Matches = '';
        // RegEx for all punctuation-marks
        //preg_match_all('/[' . join($this->getPunctuationMarks()) . ']/', $Textstring, $Matches);
        // RegEx for all punctuation-marks NOT between < and > (Like attributes etc...)
        preg_match_all('/[' . join($this->getPunctuationMarks()) . '](?![^<]*\>)/u', $Textstring, $Matches);
        $ExtractedPunctuationMarks = $Matches[0];


        return $ExtractedPunctuationMarks;
    }



    /**
     * Returns array of Words not between <>
     * @param string $Textstring
     * @return mixed
     */
    public function matchWordsWithoutTags(string $Textstring)
    {
        $Matches = '';
        // All words that are not contained by <>
        preg_match_all('/\w+(?![^<]*\>)/', $Textstring, $Matches);
        $ExtractedWords = $Matches[0];

        return $ExtractedWords;
    }



    /**
     * Returns Results from any Regex pattern search
     * @param string $RegexPattern
     * @param string $Textstring
     * @return mixed
     */
    public function matchPatternFrom(string $RegexPattern, string $Textstring)
    {
        $Matches = '';
        // All words that are not contained by <>
        preg_match_all($RegexPattern, $Textstring, $Matches);
        $ExtractedElements = $Matches[0];

        return $ExtractedElements;
    }



    /**
     * @param $ArrayOrString
     * @param $StartTag
     * @param $EndTag
     * @return array|string
     */
    public function wrap($ArrayOrString, $StartTag, $EndTag)
    {
        if (is_array($ArrayOrString)) {
            $WrappedArray = [];
            foreach ($ArrayOrString as $String) {
                array_push($WrappedArray, $StartTag . $String . $EndTag);
            }

            return $WrappedArray;
        }

        return $StartTag . $ArrayOrString . $EndTag;
    }



    /**
     * Replaces special characters of the extended character set by it's ASCII-pendants
     */
    public function substituteExtendedVowels()
    {
        $CharactersToReplace = ['Ā', 'ā', 'Ē', 'ē', 'Ī', 'ī', 'Ō', 'ō', 'Ū', 'ū'];
        $ReplacedCharacters = ['A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u'];
        $this->TextString = str_replace($CharactersToReplace, $ReplacedCharacters, $this->TextString);
    }


    /**
     * FIX for Firefox: Firefox converts <link>-Elements to self-closing-tags automatically.
     * In order to prevent that we will substitute them by linkY and then reconvert them.
     * Replaces special characters of the extended character set by it's ASCII-pendants
     * @param $TextString
     * @return mixed
     */
    public function substituteLinkYTagsbyLinkTags($TextString)
    {
        $TagsToReplace = ['link-fix', 'LINK-FIX', 'Link-Fix'];
        $ReplacedTags = ['link', 'link', 'link'];
        return str_replace($TagsToReplace, $ReplacedTags, $TextString);
    }



    /**
     * Wrap words inside <w>-Tags
     */
    public function wrapWords()
    {
        $WordArray = $this->matchWordsWithoutTags($this->TextString);
        $WordPatternArray = array_map(function ($Word) {
            // Word cannot be preceded and followed by <w></w>-Tags
            return '/(\b' . $Word . '\b)+(?![^<w>]*<\/w\>)/';
        }, $WordArray);
        $WrappedWordArray = $this->wrap($WordArray, '<w>', '</w>');
        $ReplacementCounter = 0;
        $this->TextString = preg_replace($WordPatternArray, $WrappedWordArray, $this->TextString, -1, $ReplacementCounter);

    }



    /**
     * Wraps punctuation marks inside <pc>-Tags
     */
    public function wrapPunctuationMarks()
    {
        $PunctuationMarkArray = $this->matchPunctuationMarks($this->TextString);
        $PunctuationMarkPatternArray = array_map(function ($PunctuationMark) {
            return '/(\\' . $PunctuationMark . ')+(?![^<pc>]*<\/pc\>)/';
        }, $PunctuationMarkArray);
        $WrappedPunctuationMarkArray = $this->wrap($PunctuationMarkArray, '<pc>', '</pc>');
        $this->TextString = preg_replace($PunctuationMarkPatternArray, $WrappedPunctuationMarkArray, $this->TextString, -1);
    }



    /**
     * Wraps unwrapped string elements inside <seg>-Tags
     */
    public function wrapUnknown()
    {
        // Deletes everything between w|pc|lb|milestone tags from string
        $IsolatedUnwrappedText = preg_replace('/<(w|pc|lb|milestone)>[^<]*<\/(w|pc|lb|milestone)>/', '', $this->TextString);
        // Match non-whitespaces
        $IsolatedUnwrappedTextMatches = $this->matchPatternFrom('/[^\s]+/', $IsolatedUnwrappedText);
        // Match everything not between w|pc|lb|milestone|seg tags
        $IsolatedUnwrappedTextMatchesPattern = array_map(function ($IsolatedUnwrappedTextMatch) {
            return '/(\\' . $IsolatedUnwrappedTextMatch . ')+(?![^<w>]*<\/w\>)+(?![^<pc>]*<\/pc\>)+(?![^<lb>]*<\/lb\>)+(?![^<milestone>]*<\/milestone\>)+(?![^<seg>]*<\/seg\>)/';
        }, $IsolatedUnwrappedTextMatches);
        // Wrap inside seg-tags
        $WrappedIsolatedUnwrappedTextMatches = $this->wrap($IsolatedUnwrappedTextMatches, '<seg>', '</seg>');
        $this->TextString = preg_replace($IsolatedUnwrappedTextMatchesPattern, $WrappedIsolatedUnwrappedTextMatches, $this->TextString);
    }



    /**
     * :FIXME: Should be done with DOMDocument
     */
    public function wrapWithDIV()
    {
        $this->TextString = '<text>' . $this->TextString . '</text>';

        return $this;
    }



    /**
     * :FIXME: Should be done with DOMDocument
     */
    public function wrapWithP()
    {
        $this->TextString = $this->TextString;

        return $this;
    }



    /**
     * Strips text of all elements
     */
    public function stripTags()
    {
        $this->TextString = Regex::replaceTags($this->StrippedTagsWithoutContent, '', $this->TextString);

    }



    /**
     *
     */
    public function insertWhitespaceSubstitutes()
    {

        $this->TextString = preg_replace('/(\n\n)+/', '<milestone> </milestone>', $this->TextString);
        // Instead of milestones two linebreaks
        /*$this->TextString = preg_replace('/(\n\n)+/', '<lb> </lb><lb> </lb>', $this->TextString);*/
        $this->TextString = preg_replace('/(\n)+/', '<lb> </lb>', $this->TextString);

    }


}

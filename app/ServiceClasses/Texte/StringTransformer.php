<?php

namespace App\ServiceClasses\Texte;

use App\APIs\DOMDocumentHandler;
use App\Helpers\Path;
use App\Traits\Texte\transformsTextString;

/**
 * Class StringTransformer
 * Handles user-input-string and prepares it for XSLT-transformation
 * @package App\TextTransformation
 */
class StringTransformer {

    use transformsTextString;

    public $TextString;
    public $DOMDocument;
    public $substituteExtendedVowels;
    public $wrapWords;
    public $wrapPunctuationMarks;
    public $insertWhitespace;
    public $wrapUnknown;
    public $stripTags;
    public $AllowedTags;
    public $StrippedTagsWithContent;
    public $StrippedTagsWithoutContent;
    public $wrapWithP;
    public $wrapWithDIV;



    /**
     * StringTransformer constructor.
     * @param string $Textstring
     * @param array $Config
     */
    public function __construct(string $Textstring, array $Config = null)
    {
        $this->TextString = $Textstring;
        $this->substituteExtendedVowels = $Config['substitute_extended_vowels'] ?? true;
        $this->wrapWords = $Config['wrap_words'] ?? true;
        $this->wrapPunctuationMarks = $Config['wrap_punctuation_marks'] ?? true;
        $this->insertWhitespace = $Config['insert_whitespace'] ?? true;
        $this->wrapUnknown = $Config['wrap_unknown'] ?? true;
        $this->stripTags = $Config['strip_tags'] ?? true;
        $this->wrapWithP = $Config['wrap_with_p'] ?? true;
        $this->wrapWithDIV = $Config['wrap_with_DIV'] ?? true;
        $this->AllowedTags = collect(json_decode(file_get_contents(Path::file_tei_elements()))[0])->toArray()['allowed_tags'];
        $this->StrippedTagsWithContent = collect(json_decode(file_get_contents(Path::file_tei_elements()))[0])->toArray()['remove_tagnames_textcontent'];
        $this->StrippedTagsWithoutContent = collect(json_decode(file_get_contents(Path::file_tei_elements()))[0])->toArray()['remove_tagnames_only'];
    }



    /**
     * Returns Textstring
     */
    public function get()
    {
        return html_entity_decode($this->TextString);
    }



    /**
     * @return \DOMElement
     */
    public function getDOMNode()
    {
        $this->DOMDocument = DOMDocumentHandler::load($this->TextString);

        return $this->DOMDocument->documentElement;
    }



    /**
     * @return mixed
     */
    public function getDOMDocument()
    {
        return DOMDocumentHandler::load($this->TextString);
    }

    /**
     * @return $this
     */
    public function transform()
    {
        if ($this->substituteExtendedVowels === true)
            $this->substituteExtendedVowels();
        if ($this->stripTags === true)
            $this->stripTags();
        if ($this->wrapWords === true)
            $this->wrapWords();
        if ($this->wrapPunctuationMarks === true)
            $this->wrapPunctuationMarks();
        if ($this->insertWhitespace === true)
            $this->insertWhitespaceSubstitutes();
        if ($this->wrapUnknown === true)
            $this->wrapUnknown();
        if ($this->wrapWithP === true)
            $this->wrapWithP();
        if ($this->wrapWithDIV === true)
            $this->wrapWithDIV();

        return $this;
    }


}

<?php


namespace App\ServiceClasses;


class TEIText {

    public $TeiTextSimpleXML;
    public $TeiTextDOMDocument;
    public $XPath_DOMDocument;
    public $XSLT;
    public $StyleSheet_Poetry;
    public $StyleSheet_Prose;
    public $XMLMethod;
    public $XMLInstance;
    public $Part;
    public $Title;
    public $Author;
    public $AuthorStripped;
    public $Language;
    public $Editor;
    public $PubPlace;
    public $Date;
    public $UpdatedAt;



    public function __construct(string $Filename, string $Type, string $XMLMethod = 'DOMDocument')
    {
        $File = storage_path('texts/tei/' . $Filename);

        // SimpleXML oder DOMDocument ?
        switch ($XMLMethod) {

            case 'SimpleXML':
                // SimpleXML
                $this->XMLInstance = simplexml_load_file($File);
                $this->XMLInstance->registerXPathNamespace('tei', "http://www.tei-c.org/ns/1.0");
                break;
            case 'DOMDocument':
                // DOMDocument
                $this->XMLInstance = new \DOMDocument();
                $this->XMLInstance->load($File);

                // DOMDocument-XPath
                $this->XPath_DOMDocument = new \DOMXPath($this->XMLInstance);
                $this->XPath_DOMDocument->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");

                // DOMDocument-XSLT
                $this->XSLT = new \DOMDocument();
                break;

        }

        $this->getInfo();
        $this->StyleSheet_Prose = null;


    }



    /**
     * XML-Instanz zurückgeben
     */
    public function getXMLInstance()
    {
        return $this->XMLInstance;
    }



    /**
     * Text-Informationen auslesen
     *
     *
     */
    public function getInfo()
    {
        if ($this->XMLMethod == 'SimpleXML') {

            $XPath_Author = $this->XMLInstance->xpath("//tei:author[text()]");
            $XPath_Title = $this->XMLInstance->xpath("//tei:title[text()]");
            $XPath_Language = $this->XMLInstance->xpath("//tei:language[text()]");
            $XPath_Editor = $this->XMLInstance->xpath("//tei:editor[text()]");
            $XPath_PubPlace = $this->XMLInstance->xpath("//tei:pubPlace[text()]");
            $XPath_Date = $this->XMLInstance->xpath("//tei:date[text()]");
            $XPath_UpdatedAt = $this->XMLInstance->xpath("//tei:change[1]/@when");
            $this->Title = $XPath_Title[0]->__toString();
            $this->Author = $XPath_Author[0]->__toString();
            $this->Language = $XPath_Language[0]->__toString();
            $this->Editor = $XPath_Editor[0]->__toString();
            $this->PubPlace = $XPath_PubPlace[0]->__toString();
            $this->Date = $XPath_Date[0]->__toString();
            $this->UpdatedAt = $XPath_UpdatedAt[0]->__toString();
        }
        else {

            $XPath_Author = $this->XPath_DOMDocument->evaluate("//tei:author[text()]");
            $XPath_Title = $this->XPath_DOMDocument->evaluate("//tei:title[text()]");
            $XPath_Language = $this->XPath_DOMDocument->evaluate("//tei:language[text()]");
            $XPath_Editor = $this->XPath_DOMDocument->evaluate("//tei:editor[text()]");
            $XPath_PubPlace = $this->XPath_DOMDocument->evaluate("//tei:pubPlace[text()]");
            $XPath_Date = $this->XPath_DOMDocument->evaluate("//tei:date[text()]");
            $XPath_UpdatedAt = $this->XPath_DOMDocument->evaluate("//tei:change[1]/@when");
            $this->Title = $XPath_Title->item(0)->textContent;
            $this->Author = $XPath_Author->item(0)->textContent;
            $this->AuthorStripped = str_replace(' ', '', $XPath_Author->item(0)->textContent);
            $this->Language = $XPath_Language->item(0)->textContent;
            $this->Editor = $XPath_Editor->item(0)->textContent;
            $this->PubPlace = $XPath_PubPlace->item(0)->textContent;
            $this->Date = $XPath_Date->item(0)->textContent;
            $this->UpdatedAt = $XPath_UpdatedAt->item(0)->textContent;
        }

    }



    /**
     * Eine Textstelle mit SimpleXML extrahieren
     * @param string $Element
     * @param string $Type
     * @param int $Part
     * @return mixed
     */
    public function extractTextPart(string $Element = 'div', string $Type, int $Part)
    {
        $XPathString = '//tei:' . $Element . '[@type="' . $Type . '"][@n="' . $Part . '"]';

        if ($this->XMLMethod == 'SimpleXML') {
            $ExtractedText = $this->XMLInstance->xpath($XPathString);
            $this->Part = $ExtractedText[0];
        }
        else {
            $ExtractedText = $this->XPath_DOMDocument->evaluate($XPathString);
            $this->Part = $ExtractedText->item(0);
        }

        return $this->Part;
    }



    /**
     * Nur die Zeilen/Verse der Textstelle mit DOMDocument extrahieren
     * @param string $Element
     * @param string $Type
     * @param int $Part
     * @return mixed
     */
    public function extractTextPartLines(string $Element = 'div', string $Type, int $Part)
    {
        //$XPathString = '//tei:' . $Element . '[@type="' . $Type . '"][@n="' . $Part . '"]/tei:l';
        // /*/ Ignores any interjacent nodes
        $XPathString = '//tei:' . $Element . '[@type="' . $Type . '"][@n="' . $Part . '"]/*/tei:l';

        if ($this->XMLMethod == 'SimpleXML') {
            $ExtractedText = $this->XMLInstance->xpath($XPathString);
        }
        else {
            $ExtractedText = $this->XPath_DOMDocument->evaluate($XPathString);
        }
        //dd($ExtractedText->item(0)->childNodes->item(11));
        return $ExtractedText;
    }



    /**
     * Nur die Zeilen/Verse der Textstelle mit DOMDocument extrahieren
     * @param string $Element
     * @param string $Type
     * @param int $Part
     * @return mixed
     */
    public function extractTextPartTextContent(string $Element = 'div', string $Type, int $Part)
    {

        $TextString = '';

        $XPathString = '//tei:' . $Element . '[@type="' . $Type . '"][@n="' . $Part . '"]/*[not(name() = "note")]//text()[normalize-space()]';

        if ($this->XMLMethod == 'SimpleXML') {
            $ExtractedText = $this->XMLInstance->xpath($XPathString);
        }
        else {
            $ExtractedText = $this->XPath_DOMDocument->evaluate($XPathString);
        }

        // Stringify
        foreach ($ExtractedText as $TextElement) {
            $TextString .= $TextElement->textContent . ' ';
        }

        return $TextString;
    }



    /**
     * Autor auslesen
     */
    public function getAuthor()
    {

    }



    /**
     * Werktitel auslesen
     */
    public function getTitle()
    {

    }



    public function isPoetry()
    {

    }



    public function isProse()
    {

    }

}
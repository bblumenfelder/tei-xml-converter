<?php

namespace App\TextTransformation;


use App\APIs\XPath;
use App\APIs\XSLT;
use App\Traits\Texte\transformsTextString;

class TextTransformer {

    use transformsTextString;

    public $TEISource;
    public $Textpart;
    public $Type;
    public $DOMDocument;
    public $OutputFileName;
    public $TextBody;
    public $WordLevel;
    public $Punctuation;
    public $StyleSheet_Poetry;



    /**
     * TransformedText constructor.
     * @param TEIText $TEISource
     * @param $Textpart
     * @param $Type
     */
    public function __construct(TEIText $TEISource, $Textpart, $Type)
    {
        $this->TEISource = $TEISource;
        $this->Textpart = $Textpart;
        $this->Type = $Type;
        // Determine the element that contains words as strings
        $this->WordLevel = $this->Type === 'poetry' ? 'l' : 'p';
        $this->StyleSheet_Poetry = resource_path('xslt\tei-transform-poetry.xslt');

        $this->DOMDocument = new \DOMDocument('1.0', 'utf-8');
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->formatOutput = true;
        $this->OutputFileName = date("Y_m_d_H_i", time()) . '_' . $TEISource->AuthorStripped . '_' . $TEISource->Title;
        $this->createTEIMarkup();
        $this->TextBody = $this->TextBody();
        $this->setTextBody($this->Textpart);


    }



    public function prepare()
    {
        $this->wrapAnalysisElements();
    }



    public function transform()
    {
        $this->DOMDocument = XSLT::transform($this->get(), $this->StyleSheet_Poetry);
    }



    /**
     * @param bool $Filename
     */
    public function save($Filename = false)
    {
        $OutputFilename = $Filename ? $Filename : $this->OutputFileName;
        $this->DOMDocument->save(storage_path('texts\transformed\\' . $OutputFilename . '.xml'));
    }



    /**
     * Returns Transformed text as string
     * @return string
     */
    public function get()
    {
        return $this->DOMDocument->saveXML();
    }



    private function transformLinebreaks()
    {
        $LineTags = XPath::queryTEI($this->DOMDocument, '//tei:l', true);
        $LineNumberIncremental = 1;
        foreach ($LineTags as $lineTag) {
            $this->createLinebreak($lineTag, $LineNumberIncremental);
            $LineNumberIncremental++;
        }
    }



    /**
     * This function will wrap every word inside <w>-tags and all punctuation-marks inside <pc>-tags
     * It will preserve all document-tags
     */
    private function wrapAnalysisElements()
    {
        // Get all nodes of the textbody that contain text
        $NodesContainingText = XPath::query($this->DOMDocument, '//body//*[text()[string-length(normalize-space(.))>0]]', true);

        foreach ($NodesContainingText as $NodeContainingText) {
            $MatchedWordsAndPunctuationMarks = $this->matchWordsAndPunctuation($NodeContainingText->textContent);
            // Empty node with text
            $NodeContainingText->textContent = '';
            // Fill node with new <w> elements that contain textContent of word
            foreach ($MatchedWordsAndPunctuationMarks as $matchedWordsAndPunctuationMark) {
                $NodeContainingText->appendChild($this->getTEIElement($matchedWordsAndPunctuationMark));
            }
        }
    }



    /**
     * Die Stelle, wo das ausgesuchte Segment angehängt werden soll
     */
    public function TextBody()
    {
        $TextBodyNode = XPath::queryTEI($this->DOMDocument, '//body');

        return $TextBodyNode;
    }



    /**
     * Appends TEXTPART-Element to <body>-Node: textpart
     * node will be stripped in transformation
     * @param $TextNode
     */
    public function setTextBody($TextNode)
    {
        $ImportedNode = $this->DOMDocument->importNode($TextNode, true);
        $ImportedNode->setAttribute('remove', 'true');
        $this->TextBody->appendChild($ImportedNode);
    }



    /**
     * Die Sätze (anhand einem Delimiter) zu einem Array auslesen
     * @param string $TextContent
     * @return array $ExtractedSentences
     */
    public function extractSentences(string $TextContent)
    {
        $Matches = '';
        $RegExPattern = '([a-zA-Z][^\.!?;:]*[\.!?;:])';
        preg_match_all($RegExPattern, $TextContent, $Matches);
        $ExtractedSentences = $Matches[0];

        return $ExtractedSentences;
    }






    /**
     * Überprüft, ob der übergebene String ein Interpunktionszeichen ist
     * @param string $String
     * @return boolean
     */
    private function isPunctuation(string $String)
    {
        if (in_array($String, $this->getPunctuationMarks())) {
            return true;
        }

        return false;
    }



    /**
     * Überprüft, ob der übergebene String ein Interpunktionszeichen ist
     * @param string $String
     * @return boolean
     */
    private function isComma(string $String)
    {
        if ($String == ',') {
            return true;
        }

        return false;
    }



    /**
     * @param $Line
     * @param $LineNumberIncremental
     */
    private function createLinebreak($Line, $LineNumberIncremental)
    {
        $LineNumberOriginal = $Line->getAttribute('n');
        $LineAttribute = json_encode([
            'original' => $LineNumberOriginal,
            'hermeneus' => $LineNumberIncremental
        ]);
        $Linebreak = $this->DOMDocument->createElement('lb', '');
        $Linebreak->setAttribute('n', $LineAttribute);

        $Line->parentNode->appendChild($Linebreak);
    }



    /**
     * @param string $WordOrPunctuation
     * @return \DOMElement
     */
    private function getTEIElement(string $WordOrPunctuation)
    {
        // Ist es eine Interpunktion?
        if ($this->isPunctuation($WordOrPunctuation)) {
            $WordTag = $this->DOMDocument->createElement('pc', $WordOrPunctuation);
            // Ist es ein Komma?
            if ($this->isComma($WordOrPunctuation)) {
                $WordTag->setAttribute('type', 'comma');
            }
        }
        // ... oder ein Wort?
        else {
            $WordTag = $this->DOMDocument->createElement('w', $WordOrPunctuation);
        }

        return $WordTag;
    }



    /**
     * Root-Tag erstellen
     */
    private function createTEIMarkup()
    {

        // Nützliche XPath-Abfragen
        //default:titleStmt/default:sponsor/text()
        //default:titleStmt/default:principal/text()
        //default:titleStmt/default:respStmt//text()
        //default:titleStmt/default:funder//text()
        //default:titleStmt/default:extent//text()
        //default:titleStmt/default:publicationStmt/text()
        //default:titleStmt/default:revisionDesc/text()

        $XMLModel = $this->DOMDocument->createProcessingInstruction('xml-model', 'href="http://www.tei-c.org/release/xml/tei/custom/schema/relaxng/tei_all.rng" schematypens="http://relaxng.org/ns/structure/1.0"');
        $XMLModel2 = $this->DOMDocument->createProcessingInstruction('xml-model', 'href="http://www.tei-c.org/release/xml/tei/custom/schema/relaxng/tei_all.rng" schematypens="http://purl.oclc.org/dsdl/schematron"');
        $TEINode = $this->DOMDocument->createElementNS("http://www.tei-c.org/ns/1.0", 'TEI');
        $TEIHeaderNodeList = $this->TEISource->XMLInstance->getElementsByTagNameNS("http://www.tei-c.org/ns/1.0", 'teiHeader');
        $ChangeNode = $this->DOMDocument->createElement('change', 'Parsing to Hermeneus-Format');
        $ChangeNode->setAttribute('when', date("Y-m-d"));
        $User = '';
        $ChangeNode->setAttribute('who', 'Benedikt Blumenfelder / ' . $User);

        $TEIHeaderNode = $this->DOMDocument->importNode($TEIHeaderNodeList->item(0), true);

        // Remove all attributes from teiHeader
        $TEIHeaderAttributes = $TEIHeaderNode->attributes;
        while ($TEIHeaderAttributes->length) {
            $TEIHeaderNode->removeAttribute($TEIHeaderAttributes->item(0)->name);
        }

        $RevisionDescNode = $TEIHeaderNode->getElementsByTagName('revisionDesc')->item(0);
        $RevisionDescNode->insertBefore($ChangeNode, $RevisionDescNode->firstChild);
        $TextNode = $this->DOMDocument->createElement('text');
        $BodyNode = $this->DOMDocument->createElement('body');
        //$PNode = $this->DOMDocument->createElement('p');
        $TEINode->appendChild($TEIHeaderNode);
        $TEINode->appendChild($TextNode);
        $TextNode->appendChild($BodyNode);
        //$BodyNode->appendChild($PNode);
        $this->DOMDocument->appendChild($XMLModel);
        $this->DOMDocument->appendChild($XMLModel2);
        $this->DOMDocument->appendChild($TEINode);


        // :TODO: change-Tag und Attribute mit Autor und Timestamp hinzufügen

    }

}
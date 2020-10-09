<?php

namespace App\TextTransformation;

use App\APIs\DOMDocumentHandler;
use App\APIs\XPath;
use App\CorpusText;
use App\Helpers\Helper;
use App\Helpers\Path;

/**
 * This class prepares a TEI-Text from the Textcorpus for extraction
 * Class CorpusTextLoader
 * @package App\TextTransformation
 */
class CorpusTextLoader {

    public $CorpusText;
    public $DOMDocument;
    public $StrippedElements;



    /**
     * CorpusTextLoader constructor.
     * @param CorpusText $CorpusText
     */
    public function __construct(CorpusText $CorpusText)
    {
        $this->CorpusText = $CorpusText;
        $this->StrippedElements = ['figure'];
        $this->DOMDocument = DOMDocumentHandler::loadFile(Path::file_corpus_text($this->CorpusText->filename));
    }



    /**
     * Getter
     * @return CorpusText
     */
    public function get()
    {
        return $this->CorpusText;
    }



    /**
     * Modifies $this->DOMDocument
     * @return $this
     */
    public function prepare()
    {
        $this->replaceHeadNodes();
        $this->transformHighestSubsections();
        $this->addTextpartDescriptors();
        $this->handleLineElements();
        $this->stripElements();
        $this->extractBody();

        return $this;
    }



    /**
     * Returns XML-String of body-element;
     * Transforms empty tags (<milestone /> => <milestone></milestone>)
     * @return string
     */
    public function extractBody()
    {
        $this->CorpusText->xml = $this->DOMDocument->saveXML($this->DOMDocument->getElementsByTagName('body')->item(0), LIBXML_NOEMPTYTAG);
    }



    /**
     * Add line-numbers
     */
    private function handleLineElements()
    {
        $NodesWithNAttribute = XPath::queryTEI($this->DOMDocument, "//tei:l[@n]", true);
        foreach ($NodesWithNAttribute as $NodeWithNAttribute) {
            $NumberNode = $this->DOMDocument->createElement('div');
            $NumberNode->setAttribute('class', 'textr-remove textr-line-number');
            $N_Attribute = $NodeWithNAttribute->getAttribute('n');
            $NumberNode->textContent = is_numeric($N_Attribute) ? $N_Attribute : '';
            $NodeWithNAttribute->insertBefore($NumberNode, $NodeWithNAttribute->firstChild);
        }
    }



    /**
     * For every highest subsection of a text (e.g. 'book' or 'poem') append
     * <div class="textr-remove textr-highest-subsection--headline">
     * as first element child.
     */
    private function transformHighestSubsections()
    {
        $HighestSubsectionName = $this->CorpusText->highest_subsection;
        // Get only <tags subtype='$HighestSubsectionName'> that don't have an ancestor which is itself a textpart; Also exclude <element type='edition'>
        $HighestSubsections = XPath::queryTEI($this->DOMDocument, "//*[@subtype='$HighestSubsectionName' and not (ancestor::*[@type='textpart'][@subtype='$HighestSubsectionName']) and not(self::*[@type='edition'])]", true);
        if ($HighestSubsections->length === 0) {
            $HighestSubsections = XPath::queryTEI($this->DOMDocument, "//*[@type='$HighestSubsectionName' and not (ancestor::*[@type='textpart'][@type='$HighestSubsectionName']) and not(self::*[@type='edition'])]", true);
        }
        foreach ($HighestSubsections as $HighestSubsection) {
            $HighestSubsection->setAttribute('class', 'textr-highest-subsection');
            $HighestSubsectionHeadline = $this->DOMDocument->createElement('div');
            $HighestSubsectionHeadline->setAttribute('class', 'textr-remove textr-highest-subsection--headline');
            $HighestSubsectionHeadline_AddButton = $this->DOMDocument->createElement('button');
            $HighestSubsectionHeadline_AddButton->setAttribute('class', 'textr-remove textr-highest-subsection--headline__add-button');
            $HighestSubsectionHeadline_Text = $this->DOMDocument->createElement('div');
            $HighestSubsectionHeadline_Text->setAttribute('class', 'textr-remove textr-highest-subsection--headline__text');
            // If n-Attribute is not an integer, just show the attribute (e.g. "proem")
            $HighestSubsectionHeadline_Text->textContent = $this->SectionDescriptionLogic($HighestSubsection);
            $HighestSubsectionHeadline->appendChild($HighestSubsectionHeadline_AddButton);
            $HighestSubsectionHeadline->appendChild($HighestSubsectionHeadline_Text);
            $HighestSubsection->insertBefore($HighestSubsectionHeadline, $HighestSubsection->firstChild);
        }

    }



    /**
     * Add descriptor-divs to every textpart
     * (except highest subsections)
     */
    private function addTextpartDescriptors()
    {
        if ($this->CorpusText->section_descriptors) {
            foreach ($this->CorpusText->section_descriptors as $section_descriptor) {
                $Textparts = XPath::queryTEI($this->DOMDocument, '//tei:*[@subtype="' . $this->CorpusText->highest_subsection . '"]//*[@type="textpart"][@subtype="' . $section_descriptor . '"]', true);
                foreach ($Textparts as $Textpart) {
                    $TextpartDescriptor = $this->DOMDocument->createElement('div');
                    $TextpartDescriptor->setAttribute('class', 'textr-remove textr-textpart-descriptor');
                    $TextpartDescriptor->textContent = $this->SectionDescriptionLogic($Textpart);
                    $Textpart->insertBefore($TextpartDescriptor, $Textpart->firstChild);
                }
            }
        }


    }



    /**
     * Will strip all elements in $this->StrippedElements-Array
     * :TODO: Store elements to strip in row of corpustext?
     */
    private function stripElements()
    {
        foreach ($this->StrippedElements as $StrippedElement) {
            $DOMDocument_StrippedElement_Array = [];
            foreach ($this->DOMDocument->getElementsByTagName($StrippedElement) as $DOMDocument_StrippedElement) {
                array_push($DOMDocument_StrippedElement_Array, $DOMDocument_StrippedElement);
            }
            foreach ($DOMDocument_StrippedElement_Array as $DOMDocument_StrippedElement) {
                $DOMDocument_StrippedElement->parentNode->removeChild($DOMDocument_StrippedElement);
            }
        }
    }



    /**
     * Add descriptor-divs to every textpart
     * (except highest subsections)
     */
    private function addTextpartDescriptors2()
    {
        $AllTextPartsExceptHighestSubsection = XPath::queryTEI($this->DOMDocument, '//tei:*[@subtype="' . $this->CorpusText->highest_subsection . '"]//*[@type="textpart"]', true);

        foreach ($AllTextPartsExceptHighestSubsection as $Textpart) {
            if ($Textpart->firstChild->getAttribute('type') === "textpart") {
                $TextpartDescriptor = $this->DOMDocument->createElement('div');
                $TextpartDescriptor->setAttribute('class', 'textr-remove textr-textpart-descriptor');
                /*                $TextpartDescriptor->textContent = strtoupper(Helper::getSubsectionDescription($Textpart->getAttribute('subtype'))) . ' ' . $Textpart->getAttribute('n');*/
                $TextpartDescriptor->textContent = $this->SectionDescriptionLogic($Textpart);
                $Textpart->insertBefore($TextpartDescriptor, $Textpart->firstChild);
            }
        }
    }



    /**
     * All <head>-Nodes are replaced by <div class="textr-remove textr-head">
     */
    private function replaceHeadNodes()
    {
        $AllHeadNodes = $this->DOMDocument->getElementsByTagName('head');
        $i = $AllHeadNodes->length - 1;
        while ($i > -1) {
            $element = $AllHeadNodes->item($i);
            $NewHeaderDiv = $this->DOMDocument->createElement('div');
            $NewHeaderDiv->setAttribute('class', 'textr-remove textr-head');
            $NewHeaderDiv->textContent = $element->textContent;
            $element->parentNode->replaceChild($NewHeaderDiv, $element);
            $i--;
        }
    }



    /**
     * This function will return a readable title for textparts
     * depending on their n-Attribute
     * @param \DOMElement $Textpart
     * @param string $SubsectionDescriptionAttribute
     * @return string
     */
    private function SectionDescriptionLogic(\DOMElement $Textpart, $SubsectionDescriptionAttribute = 'subtype')
    {
        $N_Attribute = $Textpart->getAttribute('n');
        $SubsectionDescription = $Textpart->getAttribute($SubsectionDescriptionAttribute);

        switch ($N_Attribute) {
            case is_numeric($N_Attribute) || Helper::containsInteger($N_Attribute):
                return ucfirst(Helper::getSubsectionDescription($SubsectionDescription)) . ' ' . $N_Attribute;
                break;
            case 'pr':
                return "Proömium";
                break;

            default:
                return $N_Attribute;
                break;
        }


    }
}
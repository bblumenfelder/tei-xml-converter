<?php

namespace App\ServiceClasses;

use App\APIs\DOMDocumentHandler;
use App\APIs\XPath;
use App\APIs\XSLT;
use App\Helpers\Path;
use App\HermeneusText;
use App\Traits\Texte\transformsTextString;

/**
 * Will create a valid xml-string from extracted text-sources
 * and handle information from Request-Data
 * Class TextCorpusTextExtractor
 * @package App\TextTransformation
 */
class CorpusExtractionTransformer {

    public $DOMDocument;
    public $DOMDocument_Transformation;
    public $Extractions;
    public $ExtractionsString;
    public $SeparateExtractionContainers;
    public $StylesheetPath;
    use transformsTextString;



    public function __construct(array $Extractions, HermeneusText $HermeneusText, $SeparateExtractionContainers = false)
    {
        // Should XSLT put a DIV between selected Textstellen?
        $this->SeparateExtractionContainers = $SeparateExtractionContainers;
        $this->DOMDocument = new \DOMDocument('1.0', 'UTF-8');
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->formatOutput = true;
        $this->DOMDocument_Transformation = new \DOMDocument('1.0', 'UTF-8');
        $this->StylesheetPath = Path::path_xslt() . $HermeneusText->source . '.xslt';
        $ParentNode = $this->DOMDocument->createElement('extractions');
        $this->DOMDocument->appendChild($ParentNode);
        $this->Extractions = array_map(function ($Extraction) {
            return [
                'xml' => DOMDocumentHandler::load($Extraction['xml']),
                'locus' => $Extraction['locus']
            ];


        }, $Extractions);
    }



    /**
     * Import Extractions into one DOMDocument;
     * If required, wrap them inside extraction-nodes
     * @return $this
     */
    public function merge()
    {
        foreach ($this->Extractions as $Extraction) {
            $ImportedExtraction = $this->DOMDocument->importNode($Extraction['xml']->documentElement, true);
            $ImportedExtractionNode = $this->DOMDocument->createElement('extraction');
            $ImportedExtractionNode->setAttribute('locus', $Extraction['locus']);
            $this->SeparateExtractionContainers ? $ImportedExtractionNode->setAttribute('keep', 'yes') : $ImportedExtractionNode->setAttribute('keep', 'no');
            $ImportedExtractionNode->appendChild($ImportedExtraction);
            $this->DOMDocument->firstChild->appendChild($ImportedExtractionNode);
        }

        return $this;
    }



    /**
     * @return string
     */
    public
    function getString()
    {
        return $this->DOMDocument_Transformation->saveXML($this->DOMDocument_Transformation->documentElement);
    }



    /**
     * @return \DOMNode
     */
    public
    function getDOMNodes()
    {
        return $this->DOMDocument_Transformation->documentElement;
    }



    public function transform()
    {
        $this->DOMDocument_Transformation = XSLT::transform($this->DOMDocument->saveXML(), $this->StylesheetPath);

        return $this;
    }



    /**
     * Transform on word-level
     * If $this->StripTextContainers is true, All other elements will be stripped, only words will be preserved
     * @return $this
     */
    public
    function transformString()
    {
        foreach (XPath::queryTEI($this->DOMDocument, '//text()[normalize-space()]', true) as $Textnode) {
            $Textnode_XMLContent = DOMDocumentHandler::innerXML($Textnode->parentNode);
            $TextStringTransformer = new TextStringTransformer($Textnode_XMLContent);


            if ($this->StripTextContainers) {
                $TextStringTransformer->wrapUnwrapped = false;
                $this->ExtractionsString .= $TextStringTransformer->transform()->get();
            }
            else {
                $Textnode->parentNode->textContent = $TextStringTransformer->transform()->get();
            }
        }

        return $this;
    }


}
<?php

namespace App\ServiceClasses\Texte;

use App\Helpers\Helper;
use App\Helpers\Path;
use App\Traits\Texte\transformsTEIXML;

/**
 * This class will create a basic TEI-XML-Document
 * with a header and a body and return it as HermeneusText
 * Class HermeneusTextDocumentCreator
 * @package App\TextTransformation\TextCreation
 */
class HermeneusTextDocumentCreator {


    public $DOMDocument;
    public $Textdata;
    public $TextString;
    public $TextNode;
    public $TEI_XML;
    public $TEIBody;
    public $DocumentRoot;
    public $UserName;
    public $importedText;
    public $FirstLineNumber;



    public function __construct(array $Textdata, \DOMNode $TextNode_DOMDocument = null)
    {
        $this->DOMDocument = new \DOMDocument('1.0', 'utf-8');
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->formatOutput = true;
        // Import Textnode
        $this->Textdata = $Textdata;
        // If no specific username was given, fall back to user_id
        $this->UserName = isset($this->Textdata['user_name']) ? $this->Textdata['user_name'] : (Helper::getCurrentUser())->name;
        $this->TEI_XML = $this->createDocumentRoot();
        $this->TEI_XML = $this->createTEIHeader();
        $this->TEI_XML = $this->createTEIBody($TextNode_DOMDocument);
        // If a first_line was given, set is as attribute of first lb-element
        $this->importedText;
    }



    /**
     * Returns XML-String
     * @return mixed
     */
    public function getXML()
    {
        return $this->TEI_XML->saveXML($this->TEI_XML, LIBXML_NOEMPTYTAG);
    }



    /**
     * Returns DOMDocument
     * @return mixed
     */
    public function getDOMDocument()
    {
        return $this->TEI_XML;
    }




    /**
     * Returns XML-String of Textbody only
     * @return mixed
     */
    public function getTextbodyXML()
    {
        return $this->TEI_XML->saveXML($this->TextNode, LIBXML_NOEMPTYTAG);

    }



    /**
     * Creates basic xml-document with schema and root element
     * @return \DOMDocument
     */
    public function createDocumentRoot()
    {

        $XMLSchema = $this->DOMDocument->createProcessingInstruction('xml-model', 'href="' . Path::path_public_tei_hermeneus_xml_schmema_rng() . '" schematypens="http://relaxng.org/ns/structure/1.0"');
        $TEINodeNS = $this->DOMDocument->createElementNS("http://www.tei-c.org/ns/1.0", 'TEI');
        $TEINode = $this->DOMDocument->createElement('TEI');
        $this->DOMDocument->appendChild($XMLSchema);
        $this->DOMDocument->appendChild($TEINodeNS);

        return $this->DOMDocument;
    }



    /**
     * Creates <teiHeader> with all relevant Textinfo
     */
    public function createTEIHeader()
    {
        $this->DocumentRoot = $this->DOMDocument->getElementsByTagName('TEI')->item(0);
        // <teiHeader>
        $teiHeader = $this->DOMDocument->createElement('teiHeader');
        // <teiHeader><fileDesc>
        $teiHeader_fileDesc = $this->DOMDocument->createElement('fileDesc');
        $teiHeader->appendChild($teiHeader_fileDesc);
        // <teiHeader><fileDesc><titleStmt>
        $teiHeader_titleStmt = $this->createTitleStmt();
        $teiHeader_fileDesc->appendChild($teiHeader_titleStmt);
        // <teiHeader><fileDesc><publicationStmt>
        $teiHeader_publicationStmt = $this->createPublicationStmt();
        $teiHeader_fileDesc->appendChild($teiHeader_publicationStmt);
        // <teiHeader><fileDesc><seriesStmt>
        $teiHeader_seriesStmt = $this->createSeriesStmt();
        $teiHeader_fileDesc->appendChild($teiHeader_seriesStmt);
        // <teiHeader><fileDesc><sourceDesc>
        $teiHeader_sourceDesc = $this->createSourceDesc();
        $teiHeader_fileDesc->appendChild($teiHeader_sourceDesc);

        $this->DocumentRoot->appendChild($teiHeader);

        return $this->DOMDocument;
    }



    /**
     * @return mixed
     */
    private function createTitleStmt()
    {
        /*:TODO: Eingabefeld beim Textcreator für übergeordnetes Werk*/
        $titleStmt = $this->DOMDocument->createElement('titleStmt');
        $titleStmt_title = $this->DOMDocument->createElement('title', ' ');
        $titleStmt_author = $this->DOMDocument->createElement('author', 'AUTOR AUTOR AUTOR');
        $titleStmt_respStmt = $this->DOMDocument->createElement('respStmt');
        $titleStmt_respStmt_resp = $this->DOMDocument->createElement('resp', 'compiled by');
        $titleStmt_respStmt_name = $this->DOMDocument->createElement('name', 'NAME NAME NAME');
        $titleStmt_respStmt->appendChild($titleStmt_respStmt_resp);
        $titleStmt_respStmt->appendChild($titleStmt_respStmt_name);
        $titleStmt->appendChild($titleStmt_title);
        $titleStmt->appendChild($titleStmt_author);
        $titleStmt->appendChild($titleStmt_respStmt);

        return $titleStmt;
    }



    /**
     * @return mixed
     */
    private function createPublicationStmt()
    {
        $publicationStmt = $this->DOMDocument->createElement('publicationStmt');
        $publicationStmt_p = $this->DOMDocument->createElement('p', 'published on hermeneus.eu by user ' . $this->UserName);
        $publicationStmt->appendChild($publicationStmt_p);

        return $publicationStmt;
    }



    /**
     * @return mixed
     */
    private function createSourceDesc()
    {
        $sourceDesc = $this->DOMDocument->createElement('sourceDesc');
        $sourceDesc_p = $this->DOMDocument->createElement('p', $this->Textdata['source']);
        $sourceDesc->appendChild($sourceDesc_p);

        return $sourceDesc;
    }



    /**
     * @return mixed
     */
    private function createSeriesStmt()
    {
        $seriesStmt = $this->DOMDocument->createElement('seriesStmt');
        $seriesStmt_title = $this->DOMDocument->createElement('title', 'TITEL TITEL TITEL');
        $seriesStmt_subtitle = $this->DOMDocument->createElement('title', 'UNTERTITEL UNTERTITEL UNTERTITEL' ?? '');
        $seriesStmt_subtitle_att = $this->DOMDocument->createAttribute('type');
        $seriesStmt_subtitle_att->value = 'sub';
        $seriesStmt_subtitle->appendChild($seriesStmt_subtitle_att);
        $seriesStmt_respStmt = $this->DOMDocument->createElement('respStmt');
        $seriesStmt_respStmt_resp = $this->DOMDocument->createElement('resp', 'text analyzed and edited by');
        $seriesStmt_respStmt_name = $this->DOMDocument->createElement('name', 'DEIN NAME DEIN NAME DEIN NAME ');
        $seriesStmt_respStmt->appendChild($seriesStmt_respStmt_resp);
        $seriesStmt_respStmt->appendChild($seriesStmt_respStmt_name);
        $seriesStmt_biblScope = $this->DOMDocument->createElement('biblScope', 'TEXTSTELLE TEXTSTELLE TEXTSTELLE ' ?? '');
        $seriesStmt->appendChild($seriesStmt_title);
        $seriesStmt->appendChild($seriesStmt_subtitle);
        $seriesStmt->appendChild($seriesStmt_respStmt);
        $seriesStmt->appendChild($seriesStmt_biblScope);

        return $seriesStmt;
    }



    /**
     * Creates body-node and if a textnode was given, appends it to body
     * @param \DOMNode|null $TextNode_DOMDocument
     * @return \DOMDocument
     */
    private function createTEIBody(\DOMNode $TextNode_DOMDocument = null)
    {
        $this->DocumentRoot = $this->DOMDocument->getElementsByTagName('TEI')->item(0);
        $TEIText = $this->DOMDocument->createElement('text');
        $this->TEIBody = $this->DOMDocument->createElement('body');
        $TEIText->appendChild($this->TEIBody);
        $this->DocumentRoot->appendChild($TEIText);
        if ($TextNode_DOMDocument) {
            $this->importText($TextNode_DOMDocument)->appendBody();
        }

        return $this->DOMDocument;
    }



    public function importText(\DOMNode $DOMDocument_importedText)
    {
        $this->importedText = $DOMDocument_importedText;
        $this->TextNode = $this->DOMDocument->importNode($this->importedText, true);

        return $this;
    }



    /**
     * @return $this
     */
    public function appendBody()
    {
        $this->TEIBody->appendChild($this->TextNode);

        return $this;
    }



    public function appendExtractions(\DOMNode $TextNode)
    {
        $DOMNode_Extraction_Imported = $this->DOMDocument->importNode($TextNode, true);
        $this->TEIBody->appendChild($DOMNode_Extraction_Imported);
        $this->DOMDocument->save(Path::path_corpus_index() . 'quaak.xml');
    }

}

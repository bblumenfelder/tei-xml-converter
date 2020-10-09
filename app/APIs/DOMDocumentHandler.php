<?php

namespace App\APIs;


use App\Exceptions\XMLException;

class DOMDocumentHandler {

    /**
     * @param $XMLSource
     * @param bool $encoding
     * @param bool $formatOutput
     * @return \DOMDocument
     */
    public static function load($XMLSource, $encoding = false, $formatOutput = false)
    {
        try {
            $DOMDocument = new \DOMDocument('1.0', 'UTF-8');
            $DOMDocument->preserveWhiteSpace = false;
            $DOMDocument->formatOutput = $formatOutput ? true : false;

            $DOMDocument->loadXML($XMLSource);
            if(!$DOMDocument->loadXML($XMLSource)) {
                throw new XMLException('Kein gültiges XML-Dokument!');
            }
            if ($encoding) {
                $DOMDocument->encoding = $encoding;
            };

            return $DOMDocument;
        } catch (XMLException $exception) {
            $exception->getMessage();
        }
    }



    /**
     * @param $XMLPath
     * @return \DOMDocument
     */
    public static function loadFile($XMLPath)
    {
        try {
            $DOMDocument = new \DOMDocument('1.0', 'UTF-8');
            $DOMDocument->preserveWhiteSpace = false;
            $DOMDocument->formatOutput = false;
            $DOMDocument->load($XMLPath);
        } catch (\Exception $exception) {
            $DOMDocument = DOMDocumentHandler::load(DOMDocumentHandler::convertAndLoadHTMLEntities($XMLPath));
        }

        return $DOMDocument;
    }



    /**
     * @param $HTMLPath
     * @return \DOMDocument
     */
    public static function loadHTMLFile($HTMLPath)
    {
        $DOMDocument = new \DOMDocument('1.0', 'UTF-8');
        $DOMDocument->preserveWhiteSpace = false;
        $DOMDocument->formatOutput = false;
        $DOMDocument->loadHTMLFile($HTMLPath);


        return $DOMDocument;
    }



    /**
     * Converts all HTML-Entities into utf-8
     * @param string $XMLPath
     * @return string
     */
    public static function convertAndLoadHTMLEntities(string $XMLPath)
    {
        return html_entity_decode(file_get_contents($XMLPath));

    }



    /**
     * Returns innerXML of a Node as string
     * @param \DOMNode $DOMNode
     * @return bool|string
     */
    public static function innerXML(\DOMNode $DOMNode)
    {
        // Determine Parentnode which will be trimmed
        $ParentElement = $DOMNode->nodeName;
        $Starttag = "<$ParentElement>";
        $Endtag = "</$ParentElement>";

        $SimpleXML_Node = simplexml_import_dom($DOMNode);
        $SimpleXML_String = $SimpleXML_Node->asXML();
        // Trim start-tag
        $TrimmedFromBeginning = substr($SimpleXML_String, strlen($Starttag));
        // Trim end-tag
        $TrimmedFromEnd = substr($TrimmedFromBeginning, 0, strlen($TrimmedFromBeginning) - strlen($Endtag));


        return $TrimmedFromEnd;
    }



    /**
     * Returns current DOMElement as XML-string for debugging
     * purposes
     * @param \DOMElement $DOMElement
     * @return string
     */
    public static function showXML(\DOMElement $DOMElement)
    {
        return $DOMElement->ownerDocument->saveXML($DOMElement);
    }

}
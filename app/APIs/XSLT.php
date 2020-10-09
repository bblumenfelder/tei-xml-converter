<?php


namespace App\APIs;
// Functions called within the XSLT-Stylesheet
include('xslt_functions/xslt_functions.php');

use XSLTProcessor;


class XSLT {
    /**
     * @param $XMLSource
     * @param $XSLFilename
     * @param string $transformTo
     * @return \DOMDocument|string
     */
    public static function transform($XMLSource, $XSLFilename, $transformTo = 'doc')
    {


        // XML-Quellen laden;
        $DOMDocument_XML = XSLT::loadXML($XMLSource);

        // XSL-Quellen laden
        $DOMDocument_XSL = new \DOMDocument();
        $DOMDocument_XSL->load($XSLFilename);

        // Prozessor instantiieren und konfigurieren
        $XSLTProcessor = new XSLTProcessor();
        $XSLTProcessor->registerPHPFunctions();

        libxml_use_internal_errors(true);

        $XSL_Result = $XSLTProcessor->importStyleSheet($DOMDocument_XSL);
        if ( ! $XSL_Result) {
            foreach (libxml_get_errors() as $error) {
                echo "Libxml error: {$error->message}\n";
            }
        }
        libxml_use_internal_errors(false);

        if ($XSL_Result) {
            return $transformTo === 'xml' ? $XSLTProcessor->transformToXML($DOMDocument_XML) : $XSLTProcessor->transformToDoc($DOMDocument_XML);
        }


    }



    /**
     * @param $XMLSource
     * @return \DOMDocument
     */
    public static function loadXML($XMLSource)
    {
        $DOMDocument_XML = new \DOMDocument();
        // Überprüfe, ob die Quelle ein string
        // oder ein DOMObject ist
        if (is_object($XMLSource)) {
            $DOMDocument_Node = $DOMDocument_XML->importNode($XMLSource, true);
            $DOMDocument_XML->appendChild($DOMDocument_Node);
        }
        else {
            $DOMDocument_XML->loadXML($XMLSource);
        }

        return $DOMDocument_XML;
    }



    /**
     * @param $XMLFilename
     * @param $XSLFilename
     * @param string $transformTo
     * @return \DOMDocument|string
     */
    public static function transformFile($XMLFilename, $XSLFilename, $transformTo = 'doc')
    {
        // XML-Quellen laden
        $DOMDocument_XML = new \DOMDocument();
        $DOMDocument_XML->load($XMLFilename);

        // XSL-Quellen laden
        $DOMDocument_XSL = new \DOMDocument();
        $DOMDocument_XSL->load($XSLFilename);

        // Prozessor instantiieren und konfigurieren
        $XSLTProcessor = new XSLTProcessor();
        $XSLTProcessor->importStyleSheet($DOMDocument_XSL);

        return $transformTo === 'xml' ? $XSLTProcessor->transformToXML($DOMDocument_XML) : $XSLTProcessor->transformToDoc($DOMDocument_XML);
    }
}
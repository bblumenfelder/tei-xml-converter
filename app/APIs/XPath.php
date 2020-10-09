<?php

namespace App\APIs;

class XPath {

    /**
     * @param $DOMDocument
     * @param $XPathString
     * @param bool $ReturnNodeList
     * @return \DOMElement|\DOMNodeList
     */
    public static function query($DOMDocument, $XPathString, $ReturnNodeList = false)
    {
        $XPath = new \DOMXPath($DOMDocument);
        $XPathResult = $XPath->query($XPathString);

        return $ReturnNodeList ? $XPathResult : $XPathResult->item(0);
    }



    /**
     * @param $DOMDocument
     * @param $ContextNode
     * @param $XPathString
     * @param bool $ReturnNodeList
     * @return \DOMElement|\DOMNodeList
     * :FIXME: Probably not working
     */
    public static function queryNode($DOMDocument, $ContextNode, $XPathString, $ReturnNodeList = false)
    {
        $XPath = new \DOMXPath($DOMDocument);
        $XPathResult = $XPath->query($XPathString, $ContextNode);

        return $ReturnNodeList ? $XPathResult : $XPathResult->item(0);
    }



    /**
     * @param $DOMDocument
     * @param $XPathString
     * @param bool $ReturnNodeList
     * @return \DOMElement|\DOMNodeList
     */
    public static function queryTEI($DOMDocument, $XPathString, $ReturnNodeList = false)
    {
        $XPath = new \DOMXPath($DOMDocument);
        $XPath->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");

        $XPathResult = $XPath->query($XPathString);

        return $ReturnNodeList ? $XPathResult : $XPathResult->item(0);
    }
}
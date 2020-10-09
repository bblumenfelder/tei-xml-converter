<?php

use App\Helpers\Helper;

function splitWords($ResultNodes)
{
    $DOMDocument = new DOMDocument();
    $returnValue = $ResultNodes[0]->ownerDocument->createDocumentFragment();
    $TextString = $ResultNodes[0]->textContent;

    preg_match_all('/\w+/', $TextString, $Words);

    foreach ($Words[0] as $Word) {
        $WordNode = $DOMDocument->createElement('w');
        $WordNode->appendChild($DOMDocument->createTextNode($Word));
        $NewNode = $ResultNodes[0]->ownerDocument->importNode($WordNode, true);
        $returnValue->appendChild($NewNode);
    }

    return $returnValue;
}

function splitAll($ResultNodes)
{
    $DOMDocument = new DOMDocument();
    $returnValue = $ResultNodes[0]->ownerDocument->createDocumentFragment();
    $TextString = $ResultNodes[0]->textContent;
    $FaultyPattern = '/(\W|\b)+/';

    // Divide text into segments of words and special chars
    $Splits = preg_split('/\b|[^\S]+/', $TextString, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    foreach ($Splits as $Textelement) {
        switch ($Textelement) {
            case preg_match('/\w+/', $Textelement) === 1:
                $TextElementNode = $DOMDocument->createElement('w');
                break;
            case preg_match('/[\p{Greek}]+/u', $Textelement) === 1:
                $TextElementNode = $DOMDocument->createElement('w');
                $TextElementNode->setAttribute('xml:lang', 'greek');
                break;
            // Load stringified array of all punctuation marks escaped by \
            // :FIXME: Double punctuation-marks are treated as one (e.g. <pc>.]</pc>
            case preg_match('/[\\' . implode('\\', Helper::getPunctuationMarks()) . ']/', $Textelement) === 1:
                $TextElementNode = $DOMDocument->createElement('pc');
                break;

            default:
                $TextElementNode = $DOMDocument->createElement('seg');
                break;
        }

        $TextElementNode->appendChild($DOMDocument->createTextNode($Textelement));
        $NewNode = $ResultNodes[0]->ownerDocument->importNode($TextElementNode, true);
        $returnValue->appendChild($NewNode);
    }

    return $returnValue;
}



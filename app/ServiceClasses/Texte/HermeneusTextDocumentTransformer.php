<?php


namespace App\ServiceClasses\Texte;


use App\Traits\Texte\transformsTEIXML;

class HermeneusTextDocumentTransformer {

    public $DOMDocument;



    public function __construct(\DOMDocument $DOMDocument)
    {
        $this->DOMDocument = $DOMDocument;
    }



    /**
     * Insert first lb-element into text-container
     * @return $this
     */
    public function insertFirstLB()
    {
        // Get first w-element and return it's parentNode
        $TextContainerNode = $this->DOMDocument->getElementsByTagName('w')->item(0)->parentNode;
        $FirstLineBreakElement = $this->DOMDocument->createElement('lb', ' ');
        $TextContainerNode->firstChild->parentNode->insertBefore($FirstLineBreakElement, $TextContainerNode->firstChild);

        return $this;
    }



    /**
     * <milestone>-Tag is replaced by <lb type="stanza"> </lb> which can be displayed differently with CSS
     */
    public function replaceMilestoneWithLB()
    {
        $MilestoneNodeList = $this->DOMDocument->getElementsByTagName('milestone');
        $MilestoneNodeListLength = $MilestoneNodeList->length;

        // Use for-loop since it getElementsByTagName returns a dynamic list
        for ($i = 0; $i < $MilestoneNodeListLength; $i++) {
            $MilestoneElement = $MilestoneNodeList->item(0);
            $NewLineBreakElement = $this->DOMDocument->createElement('lb', ' ');
            $NewLineBreakElement->setAttribute('type', 'stanza');
            $MilestoneElement->parentNode->insertBefore($NewLineBreakElement, $MilestoneElement);
            $MilestoneElement->parentNode->removeChild($MilestoneElement);
        }

        return $this;
    }



    public function removeAllSEG()
    {
        $SEGNodeList = $this->DOMDocument->getElementsByTagName('seg');
        foreach ($SEGNodeList as $SEGNode) {
            $SEGNode->parentNode->removeChild($SEGNode);
        }

        return $this;
    }



    /**
     * Set a pre="true"-Attribute for pc-elements that are
     * apostrophes or similar
     * @return $this
     */
    public function handleWhitespaceInPCElements()
    {
        $PrePC = ['„', '"'];
        $NoPrePC = ['-', '–', '‐'];
        $PCNodelist = $this->DOMDocument->getElementsByTagName('pc');
        foreach ($PCNodelist as $PCElement) {
            if (in_array($PCElement->textContent, $PrePC))
                $PCElement->setAttribute('pre', 'true');
            if (in_array($PCElement->textContent, $NoPrePC))
                $PCElement->setAttribute('join', 'no');
        }

        return $this;
    }



    /**
     * Set n-attribute to all lb-elements
     * @param $FirstLinebreakNumber
     * @return $this
     */
    public function attributeLBs($FirstLinebreakNumber)
    {
        $LinebreaksNodelist = $this->DOMDocument->getElementsByTagName('lb');
        foreach ($LinebreaksNodelist as $LinebreakElement) {
            $LinebreakElement->setAttribute('n', json_encode($FirstLinebreakNumber));
            $FirstLinebreakNumber['original']++;
            $FirstLinebreakNumber['hermeneus']++;
        }

        return $this;
    }


    public function attributeWords()
    {
        $WordsNodelist = $this->DOMDocument->getElementsByTagName('w');
        foreach ($WordsNodelist as $WordElement) {
            $WordElement->setAttribute('pos', ' ');
            $WordElement->setAttribute('notation', ' ' );
        }

        return $this;
    }



    /**
     * Returns XML-String
     * @return mixed
     */
    public function getXML()
    {
        return $this->DOMDocument->saveXML($this->DOMDocument, LIBXML_NOEMPTYTAG);
    }



    /**
     * Returns DOMDocument
     * @return mixed
     */
    public function getDOMDocument()
    {
        return $this->DOMDocument;
    }

}

<?php


namespace App\Traits\Texte;


use App\APIs\XPath;

trait transformsHermeneusXML {


    public function extractTextparts($HermeneusXML)
    {
        // saveHTML prevents self-closing tags
        return [
            'HermeneusTextInfo' => $HermeneusXML->saveXML($this->XPATHHermeneusTextInfo($HermeneusXML)),
            'HermeneusText' => $HermeneusXML->saveXML($this->XPATHHermeneusText($HermeneusXML)),
            'HermeneusAnnotations' => $HermeneusXML->saveHTML($this->XPATHHermeneusAnnotations($HermeneusXML)),
            'HermeneusFarben' => $HermeneusXML->saveHTML($this->XPATHHermeneusFarben($HermeneusXML)),
        ];

    }



    public function XPATHHermeneusTextInfo($HermeneusXML)
    {
        return XPath::query($HermeneusXML, "//*[@id='hermeneus-textinfo']");
    }



    public function XPATHHermeneusText($HermeneusXML)
    {
        return XPath::query($HermeneusXML, "//*[@id='hermeneus-text']");
    }



    public function XPATHHermeneusAnnotations($HermeneusXML)
    {
        return XPath::query($HermeneusXML, "//*[@id='hermeneus-annotations']");
    }



    public function XPATHHermeneusFarben($HermeneusXML)
    {
        $FarbenNeu = XPath::query($HermeneusXML, "//*[@id='hermeneus-farben']");
        if ($FarbenNeu) {
            return $FarbenNeu;
        }
        // Fallback 0075
        else {
            $FarbenAlt = XPath::query($HermeneusXML, "//*[@id='hermeneus-links']");
            $FarbenAlt->setAttribute('id', 'hermeneus-farben');
            if ($FarbenAlt) {
                foreach ($FarbenAlt->childNodes as $FarbeAlt) {
                    $FarbeAlt->setAttribute('id', 'hermeneus-farbe');
                    $ToolName = $FarbeAlt->getAttribute('tool-id');
                    $FarbeAlt->setAttribute('tool-id', str_replace('colorize', 'farbe', $ToolName));
                }

            }

            return $FarbenAlt;
        }


    }

}
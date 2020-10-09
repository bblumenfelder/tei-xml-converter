<?php


namespace App\TextTransformation;

use App\APIs\XPath;
use App\Repositories\BestimmungRepository;

class TextParser {

    public $DOMDocument;
    public $TEISource;
    public $BestimmungRepository;
    public $OutputFileName;
    public $OutputPath;



    public function __construct(TEIText $TEISource, $Filename)
    {
        $this->TEISource = $TEISource;
        $this->DOMDocument = new \DOMDocument('1.0', 'utf-8');
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->formatOutput = true;
        $this->DOMDocument->load(storage_path('texts\transformed\\' . $Filename . '.xml'));
        $this->OutputFileName = date("Y_m_d_H_i", time()) . '_' . $TEISource->AuthorStripped . '_' . $TEISource->Title;
        $this->OutputPath = storage_path('texts\parsed\\' . $this->OutputFileName . '.xml');
        $this->BestimmungRepository = new BestimmungRepository();
    }



    public function loadWords()
    {
        /*        $XPathWords = "//tei:w";
                // Führe Query durch
                $XPathResultNodeList = $this->XPath_DOMDocument->evaluate($XPathWords);*/
        // XPath-Query-String: Selektiere alle Wörter
        $XPathResultNodeList = XPath::queryTEI($this->DOMDocument, '//tei:w', true);

        return $XPathResultNodeList;
    }



    public function analyze()
    {

        $Words = $this->loadWords();

        foreach ($Words as $Word) {
            $Form = $this->sanitizeForm($Word);
            $Bestimmung = $this->BestimmungRepository->bestimmeInArray($Form);
            $String = json_encode($Bestimmung->toArray());
            $Word->setAttribute('lemma', str_replace('"', "'", $String));
        }
    }



    /**
     * @param bool $Filename
     */
    public function export($Filename = false): void
    {
        $OutputFilename = $Filename ? $Filename : $this->OutputFileName;
        $this->DOMDocument->save(storage_path('texts\parsed\\' . $OutputFilename . '.xml'));
    }



    /**
     * @param $Word
     * @return string
     */
    private function sanitizeForm($Word): string
    {
        $Form = lcfirst($Word->textContent);

        return $Form;
    }

}
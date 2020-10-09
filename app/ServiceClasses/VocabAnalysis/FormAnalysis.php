<?php


namespace App\ServiceClasses\VocabAnalysis;


class FormAnalysis {

    public $form;
    public $lemmata;
    protected $show_pos;
    protected $show_morpho;
    protected $show_bedeutungen;
    protected $show_relevant_lemma;



    /**
     * 'form' => [
     *              [
     *                  'id': id,
     *                  'lemma': lemma,
     *                  'pos': pos,
     *                  'morpho': morpho,
     *                  'bedeutung': bedeutung,
     *                  'relevant': false/true,
     *              ], ...
     *            ]
     * VocabAnalysis constructor.
     * @param string $form
     * @param array $config
     */
    public function __construct(string $form, array $config = [])
    {
        $this->show_pos = $config['show_pos'] ?? true;
        $this->show_morpho = $config['show_morpho'] ?? true;
        $this->show_bedeutungen = $config['show_bedeutungen'] ?? true;
        $this->show_relevant_lemma = $config['show_relevant_lemma'] ?? true;

        $this->form = $form;
        $this->lemmata = collect();
    }



    public function appendLemmaAnalysis($LemmaAnalysis = null)
    {
        $this->lemmata->push($LemmaAnalysis ?? $this->getEmptyLemmaAnalysis());
    }



    public function getEmptyLemmaAnalysis()
    {
        return [
            'id' => '',
            'lemma' => '',
            'info' => '',
            'pos' => '',
            'morpho' => json_encode([]),
            'bedeutung' => '',
            'is_accepted_reading' => false
        ];
    }



    public function get($analysis_format = false)
    {
        switch ($analysis_format) {
            case 'with_keyword':
                return [$this->form => $this->lemmata];
                break;
            default:
                return $this->lemmata;
                break;
        }
    }
}
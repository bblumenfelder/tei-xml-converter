<?php


namespace App\ServiceClasses\VocabAnalysis;



use App\Helpers\Helper;
use App\Repositories\VocabRepository;
use App\ServiceClasses\VocabAnalysis\lookup_tables\HermeneusCLTKUpTables;
use App\VocabComposition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FormAnalysator {

    use HermeneusCLTKUpTables;

    protected $scope;
    protected $scope_apply;
    protected $scope_operator;
    protected $scope_vocab_composition_id;
    protected $scope_vocab_composition;
    protected $show_pos;
    protected $show_morpho;
    protected $show_bedeutungen;
    protected $analysis_format;
    protected $lerneinheit_bedeutung;
    /**
     * @type FormAnalysis
     */
    public $Analysis;
    public $Lemmata;
    public $Form;
    /**
     * @var string "hermeneus", "conllu"
     */
    private $morpho_format;
    /**
     * @var false|mixed
     */
    private $scope_vocab_collection;



    public function __construct(array $config = [], $ScopeVocabCollection = false)
    {
        $this->scope_operator = $config['analysis_operator'] ?? null;
        $this->scope_vocab_composition_id = $config['analysis_vocab_composition_id'] ?? null;
        $this->scope_vocab_composition = $this->scope_vocab_composition_id ? (VocabComposition::find($this->scope_vocab_composition_id))->scope : null;
        $this->scope_apply = $config['analysis_vocab_compose'] ?? false;
        /**
         * Wenn eine manuelle $ScopeVocabCollection angegeben wird, suche darin,
         * andernfalls lese scope aus der Konfiguration bzw. nehme alle Wörter
         */
        $this->scope =  $ScopeVocabCollection ? $ScopeVocabCollection : $this->getScope();
        $this->show_pos = $config['analysis_show_pos'] ?? true;
        $this->show_morpho = $config['analysis_show_morpho'] ?? true;
        $this->show_bedeutungen = $config['analysis_show_bedeutungen'] ?? true;
        $this->analysis_format = $config['analysis_format'] ?? 'hermeneus_v2';
        $this->morpho_format = $config['morpho_format'] ?? 'hermeneus';
        $this->lerneinheit_bedeutung = $config['lerneinheit_bedeutung'] ?? true;
        $this->Lemmata = collect();
    }



    /**
     * @param string $form
     * @return $this
     */
    public function lemmatize(string $form)
    {
        $this->Lemmata = $this->getVocabOfForm($form);

        return $this;
    }



    /**
     * @param string $form
     * @return $this
     */
    public function analyze(string $form)
    {
        $this->Form = $form;
        $this->lemmatize($form)->analyseLemmata();

        return $this;

    }



    /**
     * @param bool $ToJson
     * @return array|false|Collection|string
     */
    public function get($ToJson = false)
    {
        return $ToJson ? json_encode($this->Analysis->get($this->analysis_format)) : $this->Analysis->get($this->analysis_format);
    }



    /**
     * Für jedes Lemma bzw. jedes Wort-Model,
     * das zur Form gefunden wurde...
     * @return $this
     */
    private function analyseLemmata()
    {
        $this->Analysis = new FormAnalysis($this->Form);
        foreach ($this->Lemmata as $Lemma) {
            $LemmaAnalysis = $this->getSingleLemmaAnalysis($Lemma);
            $this->Analysis->appendLemmaAnalysis($LemmaAnalysis);
        }

        return $this;
    }



    /**
     * Retrieve single analysis for word with given keys
     * @param $Wort
     * @return array
     */
    private function getSingleLemmaAnalysis($Wort)
    {
        switch ($this->analysis_format) {
            case 'hermeneus_v1':
                return [
                    'id' => $Wort['id'],
                    'lemma' => $Wort['lemma'],
                    'info' => $Wort->getAdditionalLemmaInfo() ?? '',
                    'wortart' => $Wort['wortart'],
                    'bestimmungen' => $this->show_morpho ? $this->getMorphoOf($Wort) : json_encode([]),
                    'bedeutung' => $this->show_bedeutungen ? $Wort['lerneinheit_bedeutung'] ?? $Wort['bedeutung'] : '',
                    'isActualLemma' => false
                ];
                break;

            default:
                return [
                    'id' => $Wort['id'],
                    'lemma' => $Wort['lemma'],
                    'info' => $Wort->getAdditionalLemmaInfo() ?? '',
                    'pos' => $Wort['wortart'],
                    'morpho' => $this->show_morpho ? $this->getMorphoOf($Wort) : json_encode([]),
                    'bedeutung' => $this->show_bedeutungen ? $Wort['lerneinheit_bedeutung'] ?? $Wort['bedeutung'] : '',
                    'is_accepted_reading' => false
                ];
                break;
        }

    }



    public function getMorphoOf($Wort, $PrependPOS = false)
    {
        switch ($this->morpho_format) {
            case 'cltk':
                return $this->getCLTKMorphoOf($Wort);
                break;
            default:
                return $this->getHermeneusMorphoOf($Wort, $PrependPOS);
                break;
        }
    }



    /**
     * Die Bestimmungen zur Form extrahieren
     * GGf. Wortart vorher angeben
     * @param $Wort
     * @return Collection|string
     */
    public function getCLTKMorphoOf($Wort)
    {
        $POSStringArray = ["-", "-", "-", "-", "-", "-", "-", "-", "-",];
        $AlleFormenArray = $Wort['morph_array'];

        $EinzelnesLemmaBestimmungen = collect();

        // Wort ist nicht morphologisierbar
        if ( ! $AlleFormenArray) {
            // Wort ist ein Partikel und hat vielleicht Informationen in einer bestimmten Spalte
            if (get_class($Wort) === "App\Partikel") {
                if ($Wort['fb_art']) {
                    $POSStringArray[0] = $this->get_0_POS_isPartikel($Wort['fb_art']);
                    $EinzelnesLemmaBestimmungen = $EinzelnesLemmaBestimmungen->push(join("", $POSStringArray));
                }
            }
        }
        else {
            // Solange die Form in der Morphologietabelle auffindbar ist ...
            while ( ! empty(Helper::recursive_array_search($this->Form, $AlleFormenArray))) {

                // Wandle die Bestimmung in dot-Notation um (aktiv.praesens.indikativ.sg1)
                $GefundeneForm_String = ltrim(Helper::recursive_array_search($this->Form, $AlleFormenArray), '.');
                $GefundeneForm_Array = explode('.', $GefundeneForm_String);


                array_unshift($GefundeneForm_Array, lcfirst($Wort->wortart));
                // 0_POS
                $POSStringArray[0] = $this->get_0_POS($Wort->wortart);
                // Iterate over all morph-tags and look them up in a substitute-table. Continue loop if there is a match
                foreach ($GefundeneForm_Array as $HermeneusMorphTag) {
                    $Field1 = $this->get_1_Person($HermeneusMorphTag);
                    if ($Field1) {
                        $POSStringArray[1] = $Field1;
                        $POSStringArray[2] = $this->get_2_Number($HermeneusMorphTag);
                        continue;
                    }
                    $Field2 = $this->get_2_Number($HermeneusMorphTag);
                    if ($Field2) {
                        $POSStringArray[2] = $Field2;
                        continue;
                    }
                    $Field3 = $this->get_3_Tense($HermeneusMorphTag);
                    if ($Field3) {

                        $POSStringArray[3] = $Field3;
                        continue;
                    }
                    $Field4 = $this->get_4_Mood($HermeneusMorphTag);
                    if ($Field4) {

                        $POSStringArray[4] = $Field4;
                        continue;
                    }
                    $Field5 = $this->get_5_Voice($HermeneusMorphTag);
                    if ($Field5) {

                        $POSStringArray[5] = $Field5;
                        continue;
                    }
                    $Field6 = $this->get_6_Gender($HermeneusMorphTag);
                    if ($Field6) {

                        $POSStringArray[6] = $Field6;
                        continue;
                    }
                    else {
                        // $Wort might be a NOMEN
                        $POSStringArray[6] = $Wort["wortart"] === "nomen" ? $this->get_6_Gender($Wort["fb_genus"]) : "-";
                    }
                    $Field7 = $this->get_7_Case($HermeneusMorphTag);
                    if ($Field7) {

                        $POSStringArray[7] = $Field7;
                        continue;
                    }
                    $Field8 = $this->get_8_Degree($HermeneusMorphTag);
                    if ($Field8) {

                        $POSStringArray[8] = $Field8;
                        continue;
                    }
                }

                $EinzelnesLemmaBestimmungen = $EinzelnesLemmaBestimmungen->push(join("", $POSStringArray));

                // LÖSCHE die Form aus der Morphologietabelle, sodass nach weiteren Vorkommen
                // gesucht werden kann!
                array_forget($AlleFormenArray, $GefundeneForm_String);
            }
        }

        return $EinzelnesLemmaBestimmungen;

    }



    /**
     * Die Bestimmungen zur Form extrahieren
     * GGf. Wortart vorher angeben
     * @param $Wort
     * @param bool $PrependPOS
     * @return Collection|string
     */
    public function getHermeneusMorphoOf($Wort, $PrependPOS = false)
    {
        $AlleFormenArray = $Wort['morph_array'];

        $EinzelnesLemmaBestimmungen = collect();

        // Wort ist nicht morphologisierbar
        if ( ! $AlleFormenArray) {
            // Wort ist ein Partikel und hat vielleicht Informationen in einer bestimmten Spalte
            if (get_class($Wort) === "App\Partikel") {
                if ($Wort['fb_art']) {
                    $EinzelnesLemmaBestimmungen->push([$Wort['fb_art']]);
                }
            }
        }
        else {
            // Solange die Form in der Morphologietabelle auffindbar ist ...
            while ( ! empty(Helper::recursive_array_search_case($this->Form, $AlleFormenArray))) {

                // Wandle die Bestimmung in dot-Notation um (aktiv.praesens.indikativ.sg1)
                $GefundeneForm_String = ltrim(Helper::recursive_array_search_case($this->Form, $AlleFormenArray), '.');
                $GefundeneForm_Array = explode('.', $GefundeneForm_String);

                // Füge ggf. die Wortart am Anfang hinzu
                if ($PrependPOS === true) {
                    array_unshift($GefundeneForm_Array, lcfirst($Wort->wortart));
                }
                $EinzelnesLemmaBestimmungen = $EinzelnesLemmaBestimmungen->push($GefundeneForm_Array);

                // LÖSCHE die Form aus der Morphologietabelle, sodass nach weiteren Vorkommen
                // gesucht werden kann!
                array_forget($AlleFormenArray, $GefundeneForm_String);
            }
        }

        return $EinzelnesLemmaBestimmungen;
    }



    /**
     * Returns VocabCollection of WHERE TO SEARCH
     * Evaluates all scope-parameters
     * @return mixed
     */
    private function getScope()
    {
        $Vocab_All = (new VocabRepository)->all()->get();
        if ($this->scope_apply === true) {
            if ($this->scope_vocab_composition) {
                /*WITH CACHING*/
                /*$Vocab_InScope = Cache::remember('vocab_composition_vocab_' . $this->scope_vocab_composition_id, 600, function () {
                    return (new VocabRepository())->composition($this->scope_vocab_composition_id)->get();
                });  */
                /*WITHOUT CACHING*/
                $Vocab_InScope =  (new VocabRepository())->composition($this->scope_vocab_composition_id)->get();
                if ($this->scope_operator === 'in') {
                    return $Vocab_InScope;
                }
                else if ($this->scope_operator === 'not') {
                    /**
                     * Subtracts $Vocab_InScope from $Vocab_All
                     */
                    return $Vocab_All->filter(function ($wort) use ($Vocab_InScope) {
                        return ! ($Vocab_InScope->contains('lemma', $wort['lemma']) && $Vocab_InScope->contains('wortart', $wort['wortart']));
                    });
                }
            }
        }

        return $Vocab_All;
    }



    /**
     * Checks for native lemma; If there is none
     * then splits form (e.g. amicisque)
     * and searches again
     * @param $Form
     * @return Collection
     */
    private function getVocabOfForm($Form)
    {
        // Mögliche Vokabeln zu der Form herausfiltern
        $VocabsForFormNative = $this->getVocabOfForm_Native($Form);
        // Wurden keine "natürlichen" Vokabeln zu dieser Form gefunden?
        if ($VocabsForFormNative->count() === 0) {
            // Suche nach den gesplitteten Vokabeln
            $VocabsForForm = $this->splitFormsBySuffix($Form)->map(function ($SplittedForm) {
                return $this->getVocabOfForm_Native($SplittedForm);
            })->filter(function ($FoundVocab) {
                return $FoundVocab->isNotEmpty();
            })->flatten(1);
        }
        else {
            $VocabsForForm = $VocabsForFormNative;
        }

        return $VocabsForForm;
    }



    /**
     * Gibt diejenigen Vokabeln zurück, in denen die Form vorkommt
     * @param string $Form
     * @param bool $CaseSensitive
     * @return Collection
     */
    public function getVocabOfForm_Native(string $Form, bool $CaseSensitive = false)
    {
        if (strlen($Form) === 1) {
            return $this->scope->filter(function ($value, $key) use ($Form, $CaseSensitive) {
                return $value->lemma === $Form;
            });
        }

        return $this->scope->filter(function ($value, $key) use ($Form, $CaseSensitive) {
            $AlleFormen = collect($value['alle_formen']);

            return $CaseSensitive ? $AlleFormen->contains($Form) : $AlleFormen->contains($Form) || $AlleFormen->contains(strtolower($Form));
        });
    }



    /**
     * Zerlegt den String anhand der Enklitika
     * @param $Form
     * @return array
     */
    public function splitFormsBySuffix($Form)
    {
        if (substr($Form, -3) == 'que') {
            return collect([substr($Form, 0, strlen($Form) - 3), '-que']);
        }
        elseif (substr($Form, -2) == 'ne') {
            return collect([substr($Form, 0, strlen($Form) - 2), '-ne']);
        }
        elseif (substr($Form, -3) == 'cum') {
            return collect([substr($Form, 0, strlen($Form) - 3), '-cum']);
        }
        elseif (substr($Form, -2) == 've') {
            return collect([substr($Form, 0, strlen($Form) - 2), '-ve']);
        }
        elseif (substr($Form, -2) == 'ce') {
            return collect([substr($Form, 0, strlen($Form) - 2), '-ce']);
        }
        else {
            return collect([]);
        }
    }
}
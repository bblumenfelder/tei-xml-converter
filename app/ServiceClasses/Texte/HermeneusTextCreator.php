<?php
/**
 * Accepts configuration to return HermeneusText-instance
 */

namespace App\ServiceClasses\Texte;


use App\Helpers\Helper;
use App\HermeneusText;

class HermeneusTextCreator {

    protected $HermeneusText;
    public    $Textdata;
    public    $Textstring;
    protected $Config;
    protected $FirstLineNumber;
    protected $HermeneusTextDocument;
    protected $Config_Transform_Default = ['substitute_extended_vowels' => true,
                                           'wrap_with_p' => true,
                                           'wrap_with_div' => true,
                                           'wrap_unknown' => true,
                                           'insert_first_lb' => true,
                                           'check_db_for_vocab' => true,
                                           'remove_all_seg_' => true,
                                           'pre_attributes' => true,];

    protected $Config_Default_CorpusText = [];



    public function __construct($Textdata, array $Config = null)
    {
        // Custom config or standard config?
        $this->Config = $this->getConfig($Config);
        $this->Textdata = $this->getTextdata($Textdata);
        $this->Textstring = $this->Textdata['text_content'];
        $this->FirstLineNumber = isset($this->Textdata['first_line']) ? ['original' => $this->Textdata['first_line'], 'hermeneus' => 1] : ['original' => 1, 'hermeneus' => 1];
    }



    /**
     * Convert format and correct aberrations
     * @param $Textdata
     * @return array
     */
    private function getTextdata($Textdata)
    {
        $Textdata = ! is_array($Textdata) ? $Textdata->toArray() : $Textdata;
        $Textdata['text_content'] = $Textdata['text_content'] ?? $Textdata['text_submitted'];
        $Textdata['user_name'] = $Textdata['user_name'] ?? 'NAME NAME NAME';
        $Textdata['source'] = $Textdata['source'] ?? isset($Textdata['locus']) ?? '';

        return $Textdata;
    }



    /**
     * How should the text be processed?
     * Depends if text is corpus-text or user-text and
     * if config-array was set
     * @param null $Config
     * @return array|null
     */
    private function getConfig($Config = null)
    {
        /* switch ($this->Textdata['type']) {

             case 'corpus':
                 return $Config ? $Config : $this->Config_Default_CorpusText;
                 break;
             default:*/
        return $Config ? $Config : $this->Config_Transform_Default;
        /*      break;
      }*/
    }



    /**
     * Creates instance
     * @param string $type
     * @return HermeneusText
     */
    public function create($type = 'user')
    {
        $this->HermeneusText = /*new HermeneusText();
        $this->HermeneusText->type = $type;
        $this->HermeneusText->author = $this->Textdata['author'];
        $this->HermeneusText->title = $this->Textdata['title'];
        $this->HermeneusText->subtitle = $this->Textdata['subtitle'] ?? '';
        $this->HermeneusText->user_name = $this->Textdata['user_name'] ?? '';
        $this->HermeneusText->text_content = $this->Textdata['text_content'] ?? '';
        $this->HermeneusText->user_id = (Helper::getCurrentUser())->id;
        $this->HermeneusText->locus = $this->Textdata['locus'] ?? '';
        $this->HermeneusText->source = $this->Textdata['source'] ?? '';
        $this->HermeneusText->public = 0;
        $this->HermeneusText->xml_init =*/ $this->makeXML();

        return $this->HermeneusText;
    }



    /**
     * For preview or debug purposes
     * @return mixed
     */
    public function makePreviewXML()
    {
        $Text_DOMDocument = (new StringTransformer($this->Textstring))
            ->transform()
            ->getDOMDocument();

        $HermeneusTextDocument_Transformed = (new HermeneusTextDocumentTransformer($Text_DOMDocument))
            ->attributeLBs($this->FirstLineNumber)
            ->attributeWords()
            ->handleWhitespaceInPCElements();

        return $HermeneusTextDocument_Transformed->getXML();
    }



    /**
     * @return mixed
     */
    private function makeXML()
    {
        $Text_DOMNode = (new StringTransformer($this->Textstring))
            ->transform()
            ->getDOMNode();
        $HermeneusTextDocument = (new HermeneusTextDocumentCreator($this->Textdata, $Text_DOMNode))
            ->getDOMDocument();
        $HermeneusTextDocument_Transformed = (new HermeneusTextDocumentTransformer($HermeneusTextDocument))
            /*->insertFirstLB()*/
            ->removeAllSEG()
            ->replaceMilestoneWithLB()
            /*->attributeLBs($this->FirstLineNumber)*/
            ->handleWhitespaceInPCElements();

        return $HermeneusTextDocument_Transformed->getXML();
    }
}

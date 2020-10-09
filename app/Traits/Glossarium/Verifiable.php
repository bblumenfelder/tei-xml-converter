<?php


namespace App\Traits\Glossarium;


use App\Vocab;
use Illuminate\Http\Request;

/**
 * Trait Verifiable
 * Dynamically lookup Vocab Model class and update morph_status
 * @package App\Traits\Glossarium
 */
trait Verifiable {

    /**
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request)
    {
        $VocabClass = Vocab::getModel($request);
        $Vocab = $VocabClass::find($request->id);
        $this->authorize('verify', $Vocab);
        $Vocab->status = 2;
        $Vocab->update();
        return $Vocab->makeVisible('morph')->toArray();;
    }



    /**
     * @param Request $request
     * @return mixed
     */
    public function unverify(Request $request)
    {
        $VocabClass = Vocab::getModel($request);
        $Vocab = $VocabClass::find($request->id);
        $this->authorize('unverify', $Vocab);
        $Vocab->status = 1;
        $Vocab->update();
        return $Vocab->makeVisible('morph')->toArray();;
    }
}
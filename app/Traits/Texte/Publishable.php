<?php


namespace App\Traits\Texte;


use App\HermeneusText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait Publishable {

    public function publishRequest($id, Request $request)
    {

        $HermeneusText = HermeneusText::find($id);
        if ($HermeneusText->isEditableByUser()) {
            $HermeneusText->update(['public' => 1]);
        }

    }



    public function publish($id, Request $request)
    {

        $HermeneusText = HermeneusText::find($id);
        if (Gate::allows('manage-texts')) {
            $HermeneusText->update(['public' => 2]);
            $HermeneusText->user->awardGravitas(10);
        }
    }



    public function unpublish($id, Request $request)
    {
        $HermeneusText = HermeneusText::find($id);
        if ($HermeneusText->isEditableByUser()) {
            $HermeneusText->update(['public' => 0]);
        }
    }
}
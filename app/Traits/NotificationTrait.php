<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

use App\Adjektiv;
use App\Nomen;
use App\Numerale;
use App\Partikel;
use App\Pronomen;
use App\Verb;


use App\Buch;
use App\Grammatik;
use App\Lerneinheit;
use App\Sachfeld;



/**
 * Erledigt sämtliche Meldungen an den User; Methoden geben einen View zurück, der i.d.R. durch AJAX empfangen/ausgegeben wird
 */
trait NotificationTrait {

    /**
     * Bestätigung vom User anfordern, ein Objekt zu löschen.
     * $significant_column ist ein Accessor aller Modelle.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDelete($id) {
        $model = $this->model;
        $modelObject = $model::find($id);

        $data['id'] = $modelObject->id;
        $data['class_name'] = $model::CLASS_NAME;
        $data['significant_column'] = $modelObject->significant_column;
        $data['controller_action'] = $model::CLASS_NAME . 'Controller@destroy';

        return view('notification.confirm_delete', ['data' => $data]);
    }

}
<?php

namespace App\Traits\Glossarium;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait Morphable {



    /**
     * Morphologize Vocab
     */
    /**
     * @param int $id
     * @return string
     */
    public function morph(int $id) {
        $modelObject = $this->model;
        $VocabMorpher = $modelObject::MORPHER;

        $vocab = $modelObject::find($id);
        $MorphingVocab = new $VocabMorpher($vocab);
        if ($vocab->IsValid()) {

            try {
                $MorphingVocab->autoMorph();
                $MorphingVocab->writeXML();
                $MorphingVocab->writeJSON();
            } catch (\Exception $e) {
                return json_encode(['error' => $vocab->lemma . ': Kein Zugriff auf die XML-Datei!']);
            }
        }
        else {
            return json_encode(['error' => $vocab->lemma . ': Es fehlen Angaben zu der Vokabel!']);
        }

        $this->updateMorphStatusToMorphed($vocab);

        return json_encode(['success' => $vocab->lemma]);
    }






    /**
     * Informationen zur Morphologisierung ausgeben
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getMorphInfo($id)
    {
        $modelObject = $this->model;
        $VocabMorpher = $modelObject::MORPHER;
        $VocabMorphInfoHandler = $modelObject::MORPH_INFO_HANDLER;

        // Vocab-Model suchen
        $vocab = $modelObject::find($id);
        // Morpher instantiieren
        $MorphingVocab = new $VocabMorpher($vocab);
        // Zugehörigen Info-Handler instantiieren
        $MorphInfo = new $VocabMorphInfoHandler($MorphingVocab);
        $MorphInfo = $MorphInfo->getMorphInfo();

        return view('glossarium.__morph_info', ['morph_info' => $MorphInfo]);
    }



    /**
     * Informationen zur Morphologisierung ausgeben
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function createMorphArray($id)
    {
        $modelObject = $this->model;
        $VocabMorpher = $modelObject::MORPHER;

        $vocab = $modelObject::find($id);
        $MorphingVocab = new $VocabMorpher($vocab);
        $MorphingVocab->autoMorph();
        $morph_array = $MorphingVocab->getArray();

        return $morph_array;
    }



    /**
     * Formular für normale Formen laden
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showMorph($id)
    {
        $modelObject = $this->model;
        $route_name = $modelObject::ROUTE_NAME;

        $vocab = $modelObject::find($id);
        $xml_path = $vocab->xml_path;
        $xml_sim = simplexml_load_file($xml_path);
        $morph = $xml_sim->xpath("//" . $modelObject::MODEL_NAME . "[@id=" . $vocab->id . "]");

        // Strikte Nomenklatur!, z.B. __nomina_morph_sonderformen.blade.php
        if ($vocab->morph_mode === 1) {
            return view('glossarium.__' . $route_name . '_morph_sonderformen', ['morph' => $morph]);
        }
        else {
            return view('glossarium.__' . $route_name . '_morph', ['morph' => $morph]);
        }
    }



    /**
     * @param
     */
    public function updateMorphStatusToMorphed($vocab)
    {
        $vocab->update(['status' => 1]);
    }



    /**
     * @param Request $request
     */
    public function updateMorphStatusToValidated(Request $request)
    {
        $id = $request->input('vocab_id');
        $modelObject = $this->model;
        $vocab = $modelObject::find($id);

        $vocab->update(['status' => 2]);
    }



    /**
     * Formular für normale Formen laden
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showMorphNormal($id)
    {
        $modelObject = $this->model;
        $route_name = $modelObject::ROUTE_NAME;

        $vocab = $modelObject::find($id);
        $xml_path = $vocab->xml_path;
        $xml_sim = simplexml_load_file($xml_path);
        $morph = $xml_sim->xpath("//" . $modelObject::MODEL_NAME . "[@id=" . $vocab->id . "]");

        return view('glossarium.__' . $route_name . '_morph', ['morph' => $morph]);
    }



    /**
     * Formular für Sonderformen laden
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showMorphSonderform($id)
    {
        $modelObject = $this->model;
        $route_name = $modelObject::ROUTE_NAME;

        $vocab = $modelObject::find($id);
        $xml_path = $vocab->xml_path;
        $xml_sim = simplexml_load_file($xml_path);
        $morph = $xml_sim->xpath("//" . $modelObject::MODEL_NAME . "[@id=" . $vocab->id . "]");

        return view('glossarium.__' . $route_name . '_morph_sonderformen', ['morph' => $morph]);
    }



    /**
     * Vokabel morphologisieren
     * @param int $id
     * @return array
     */
    public function createMorph($id)
    {
        $modelObject = $this->model;
        $VocabMorpher = $modelObject::MORPHER;

        $vocab = $modelObject::find($id);
        $MorphingVocab = new $VocabMorpher($vocab);
        if ($vocab->IsValid()) {

            try {
                $MorphingVocab->autoMorph();
                $MorphingVocab->writeXML();
                $MorphingVocab->writeJSON();
            } catch (\Exception $e) {
                return json_encode(['error' => $vocab->lemma . ': Kein Zugriff auf die XML-Datei!']);
            }
        }
        else {
            return json_encode(['error' => $vocab->lemma . ': Es fehlen Angaben zu der Vokabel!']);
        }

        $this->updateMorphStatusToMorphed($vocab);

        return json_encode(['success' => $vocab->lemma]);
    }



    /**
     * Formular für Sonderformen laden
     * @param int $id
     * @param array $sonderformen
     */
    public function updateMorph($id, array $sonderformen)
    {
        $modelObject = $this->model;
        $VocabMorpher = $modelObject::MORPHER;

        $vocab = $modelObject::find($id);
        $MorphingVocab = new $VocabMorpher($vocab);
        $MorphingVocab->customMorph($sonderformen);

        $this->updateMorphStatusToMorphed($vocab);
    }




    /**
     * Array von IDs auf morph_mode prüfen und dann morphologisieren
     * @param Request $request
     * @return Response
     */
    //public function massMorph($SelectedIDsJSON)
    public function massMorph(Request $request)
    {
        $SelectedIDsJSON = $request->input('idarray');


        // Init
        $ArrayOfMorphableIDs = array();
        $ArrayOfMorphableLemmata = array();
        $ArrayOfNonMorphableLemmata = array();

        // Die Klasse der Vokabel
        $modelObject = $this->model;

        // Ausgewählte IDs aus #hidden_selected_rows
        $SelectedIDsArray = json_decode($SelectedIDsJSON);

        // Spalte $SelectedIDsArray in zwei separate Arrays
        foreach ($SelectedIDsArray as $id) {
            $vocab = $modelObject::find($id);
            $lemma = $vocab->lemma;

            // Wenn Morphologisierung auf manuell gestellt wurde ODER Felder nicht ausgefüllt wurden
            if ($vocab->morph_mode == 1 || ! $vocab->IsValid()) {
                // Füge das Lemma in dieses Array
                $ArrayOfNonMorphableLemmata[] .= $lemma;
            }

            else {
                // Andernfalls füge es in das Array der morphologisierbaren Formen
                $ArrayOfMorphableIDs[] .= $id;
                $ArrayOfMorphableLemmata[] .= $lemma;
            }

        }

        // Anzahl der zu morphologisierenden Wörter
        $count = count($ArrayOfMorphableIDs);

        return view('glossarium.__morph_mass', [
            'ArrayOfNonMorphableLemmata' => $ArrayOfNonMorphableLemmata,
            'ArrayOfMorphableLemmata' => $ArrayOfMorphableLemmata,
            'ArrayOfMorphableIDs' => $ArrayOfMorphableIDs,
            'count' => $count]);

    }

}



?>
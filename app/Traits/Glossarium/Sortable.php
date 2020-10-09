<?php
/**
 * Created by PhpStorm.
 * User: Bene
 * Date: 27.07.2017
 * Time: 21:22
 */

namespace App\Traits\Glossarium;


trait Sortable {



    /**
     * Sortiere
     * @param  $EntriesPerPage
     * @param  string $sortBy
     * @param  string $sortDir
     * @return \Illuminate\Http\Response
     */
    public function sort($EntriesPerPage, $sortBy, $sortDir)
    {
        $modelObject = $this->model;
        // Erzeuge Querybuilder-Instanz
        $vocab = $modelObject::query();
        // Sortiere
        $vocab->orderBy($sortBy, $sortDir);
        // Anzahl aller Einträge des entsprechenden Models
        $NumberOfAllEntries = count($modelObject::all());
        // ... wenn "alle" (Einträge) ausgewählt wurde...
        if ($EntriesPerPage == 'alle') {
            $EntriesPerPage = $NumberOfAllEntries;
        }
        return view('glossarium.vocab', [
            'EntriesPerPage' => $EntriesPerPage,
            'sortBy' => $sortBy,
            'vocab' => $vocab->paginate($EntriesPerPage)]);
    }


}
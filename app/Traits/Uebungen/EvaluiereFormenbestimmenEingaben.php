<?php


namespace App\Traits\Uebungen;


use App\Http\Requests\Uebungen\EvaluiereFormenbestimmenRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Boolean;

trait EvaluiereFormenbestimmenEingaben {

    /**
     * Formen auswerten: Richtige Eingaben, falsche Eingaben, nicht erreichte Eingaben
     * @param EvaluiereFormenbestimmenRequest $request
     * @return Collection $Evaluierung
     */
    public function auswerten(EvaluiereFormenbestimmenRequest $request)
    {
        $Loesungen_Request = $request->input('loesungen');
        $Eingaben_Request = $request->input('eingaben');

        $Evaluierung = collect();
        $Eingabe_User = collect();

        foreach ($Loesungen_Request as $Form => $Loesungen_Moegliche) {

            $Evaluierung_Data = collect();
            $Evaluierung_Eingaben_Alle = collect();

            foreach ($Loesungen_Moegliche as $Key => $Loesung_Moegliche_Aktuelle) {

                $Evaluierung_Eingaben_FuerForm = collect();

                $Eingabe_User = $Eingabe_User->put($Form, $Eingaben_Request[ $Form ]);
                $Loesung_Eingegebene = $Eingabe_User[ $Form ];


                $Eingaben_AktuelleLoesung_Richtig = $Loesung_Moegliche_Aktuelle->filter(function ($value, $key) use ($Loesung_Eingegebene) {
                    return $Loesung_Eingegebene->contains($value);
                });

                $Eingaben_AktuelleLoesung_NichtErreicht = $Loesung_Moegliche_Aktuelle->filter(function ($value, $key) use ($Loesung_Eingegebene) {
                    return ! $Loesung_Eingegebene->contains($value);
                });

                $Eingaben_AktuelleLoesung_Falsch = $Loesung_Eingegebene->filter(function ($value, $key) use ($Loesung_Moegliche_Aktuelle) {
                    if ($value != null) {
                        return ! $Loesung_Moegliche_Aktuelle->contains($value);
                    }
                });

                $Evaluierung_Eingaben_FuerForm->put('EingabeRichtig', $Eingaben_AktuelleLoesung_Richtig);
                $Evaluierung_Eingaben_FuerForm->put('EingabeNichtErreicht', $Eingaben_AktuelleLoesung_NichtErreicht);
                $Evaluierung_Eingaben_FuerForm->put('EingabeFalsch', $Eingaben_AktuelleLoesung_Falsch);
                $Evaluierung_Eingaben_Alle = $Evaluierung_Eingaben_Alle->push($Evaluierung_Eingaben_FuerForm);
            }

            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Loesungen_Moegliche', $Loesungen_Moegliche);
            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Eingaben_Alle', $Evaluierung_Eingaben_Alle);
            $Evaluierung = $Evaluierung->put($Form, $Evaluierung_Data);
        }

        return $Evaluierung;

    }



    /**
     * Formen auswerten: Ist das Ergebnis richtig oder falsch?
     * @param Collection $Auswertung
     * @param int $TolerierteFehlerzahl
     * @return Collection $Evaluierung
     */
    public function bewerten($Auswertung, $TolerierteFehlerzahl = 1)
    {
        $Bewertung = $Auswertung->map(function ($EvaluierungProMoeglicheLoesung, $Form) use ($TolerierteFehlerzahl) {

            $EingabenAuswertung = $EvaluierungProMoeglicheLoesung['Evaluierung_Eingaben_Alle'];

            if ($this->HasRichtigeEingabe($EingabenAuswertung)) {
                $Ergebnis = 'richtig';
                $Feedback = 'Optime! Du hast die Form vollständig bestimmt!';
            }
            else if ($this->HasTolerierteFehlerzahl($EingabenAuswertung, $TolerierteFehlerzahl)) {
                $Ergebnis = 'ausreichend';
                $Feedback = 'Das kann man nocheinmal gelten lassen...';
            }
            else {
                $Ergebnis = 'falsch';
                $Feedback = 'Das war leider nicht korrekt.';
            }


            $EvaluierungProMoeglicheLoesung->put('Ergebnis', $Ergebnis);
            $EvaluierungProMoeglicheLoesung->put('Feedback', $Feedback);

            return $EvaluierungProMoeglicheLoesung;
        });

        return $Bewertung;

    }



    /**
     * Es wurde für die elle möglichen Lösungen keine falsche Form eingegeben
     * und es wurde keine Bestimmung vergessen
     * @param $EingabenAuswertung
     * @return Boolean
     */
    public function HasRichtigeEingabe($EingabenAuswertung)
    {
        foreach ($EingabenAuswertung as $MoeglicheLoesung) {
            $EingabeNichtErreicht = $MoeglicheLoesung['EingabeNichtErreicht'];
            $EingabeFalsch = $MoeglicheLoesung['EingabeFalsch'];

            // Es wurde für die aktuelle mögliche Lösung keine falsche Form eingegeben
            // und es wurde keine Bestimmung vergessen
            if ($EingabeNichtErreicht->isEmpty() && $EingabeFalsch->isEmpty()) {
                return true;
            }

            return false;
        }
    }



    /**
     * Überprüft, ob die erlaubte Fehleranzahl nicht überschritten wurde
     * @param $EingabenAuswertung
     * @param int $TolerierteFehlerzahl
     * @return Boolean
     */
    public function HasTolerierteFehlerzahl($EingabenAuswertung, $TolerierteFehlerzahl)
    {
        foreach ($EingabenAuswertung as $MoeglicheLoesung) {
            $EingabeNichtErreicht = $MoeglicheLoesung['EingabeNichtErreicht'];
            $EingabeFalsch = $MoeglicheLoesung['EingabeFalsch'];

            // Die Form ist nicht komplett richtig UND
            // Es wurde nicht mehr als X Bestimmungen vergessen UND
            // Es gibt keine falsche Eingabe
            if ($EingabeNichtErreicht->isNotEmpty() && count($EingabeNichtErreicht) <= $TolerierteFehlerzahl && $EingabeFalsch->isEmpty()) {
                return true;
            }

            return false;
        }
    }


}
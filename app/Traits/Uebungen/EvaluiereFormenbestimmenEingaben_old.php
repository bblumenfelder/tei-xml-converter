<?php


namespace App\Traits\Uebungenhgg;


use App\Http\Requests\Uebungen\EvaluiereFormenbestimmenRequest;
use Illuminate\Http\Request;

trait EvaluiereFormenbestimmenEingaben {


    public function evaluiereEingaben(EvaluiereFormenbestimmenRequest $request)
    {


        $Loesungen_Request = $request->input('loesungen');
        $Eingaben_Request = $request->input('eingaben');

        $Evaluierung_FuerAlle = collect();
        $Evaluierung_Loesungen_Moegliche = collect();
        $Evaluierung_Loesungen_Uebrige = collect();
        $Evaluierung_Data = collect();
        $Loesungen_Moegliche = collect();
        $Eingabe_User = collect();
        $Evaluierung_Eingaben = collect();
        $Evaluierung_Eingaben_Richtig = collect();
        $Evaluierung_Eingaben_NichtErreicht = collect();
        $Evaluierung_Eingaben_Falsch = collect();
        $Evaluierung_Eingaben_Alle = collect();

        foreach ($Loesungen_Request as $Form => $MoeglicheBestimmungen) {

            $Loesungen_Moegliche = $Loesungen_Moegliche->put($Form, $Loesungen_Request[ $Form ]);
            $Evaluierung_Feedback = null;

            // Für alle Formen, die der User auch bestimmt hat ...
            if ($Eingaben_Request->has($Form)) {



                $Evaluierung_Ergebnis = null;

                foreach ($Loesungen_Moegliche[ $Form ] as $Key => $Loesung_Moegliche_Aktuelle) {

                    $Loesungen_Auch_Moegliche = collect();
                    $Eingabe_User = $Eingabe_User->put($Form, $Eingaben_Request[ $Form ]);
                    $MoeglicheLoesungSchonVorhanden = false;
                    // Solange es noch kein richtiges Ergebnis gibt, iteriere
                    if ($Evaluierung_Ergebnis != 'richtig') {


                        $Loesung_Eingegebene = $Eingabe_User[ $Form ];


                        // Vergleiche Eingaben und mache eine neue Collection der nicht eingegebenen Grammatikbegriffe
                        $Loesung_UebrigeBestimmungen = $Loesung_Moegliche_Aktuelle->diff($Loesung_Eingegebene);
                        $Eingabe_UebrigeBestimmungen = $Eingabe_User[ $Form ]->diff($Loesung_Moegliche_Aktuelle);


                        $Eingaben_AktuelleLoesung_Richtig = $Loesung_Moegliche_Aktuelle->filter(function ($value, $key) use ($Loesung_Eingegebene) {
                            return $Loesung_Eingegebene->contains($value);
                        });
                        $Eingaben_AktuelleLoesung_NichtErreicht = $Loesung_Moegliche_Aktuelle->filter(function ($value, $key) use ($Loesung_Eingegebene) {
                            return ! $Loesung_Eingegebene->contains($value);
                        });
                        $Eingaben_AktuelleLoesung_Falsch = $Loesung_Eingegebene->filter(function ($value, $key) use ($Loesung_Moegliche_Aktuelle) {
                            return ! $Loesung_Moegliche_Aktuelle->contains($value);
                        });


                        $Evaluierung_Eingaben->put('EingabeRichtig', $Eingaben_AktuelleLoesung_Richtig);
                        $Evaluierung_Eingaben->put('EingabeNichtErreicht', $Eingaben_AktuelleLoesung_NichtErreicht);
                        $Evaluierung_Eingaben->put('EingabeFalsch', $Eingaben_AktuelleLoesung_Falsch);
                        $Eingaben_AktuelleLoesung_Richtig = collect();
                        $Eingaben_AktuelleLoesung_NichtErreicht = collect();
                        $Eingaben_AktuelleLoesung_Falsch = collect();

                        // User hat eine mögliche Lösung komplett bestimmt und hat bestanden
                        if ($Loesung_UebrigeBestimmungen->isEmpty() && $Eingabe_UebrigeBestimmungen->isEmpty()) {

                            // Neue Collection von Lösungen die auch möglich wären
                            $Loesungen_Auch_Moegliche = collect($Loesungen_Moegliche[ $Form ]);
                            // Aktuelle mögliche Lösung wird nicht mehr übernommen
                            $Loesungen_Auch_Moegliche->forget($Key);
                            $Evaluierung_Loesungen_Moegliche = $Loesungen_Auch_Moegliche;
                            $Evaluierung_Ergebnis = 'richtig';
                            $Evaluierung_Feedback = 'Die Form wurde komplett richtig bestimmt!';
                        }



                        // User hat nur eine Bestimmung vergessen
                        else if (count($Loesung_UebrigeBestimmungen) == 1) {

                            // Aktuelle mögliche Lösung kommt in die MöglichCollection
                            $Evaluierung_Loesungen_Moegliche = $Loesungen_Moegliche[ $Form ];
                            $Evaluierung_Loesungen_Uebrige = $Loesung_UebrigeBestimmungen;
                            $Evaluierung_Ergebnis = 'ausreichend';
                            $Evaluierung_Feedback = 'Es ist eine Bestimmung zu wenig eingegeben worden';

                        }



                        // User hat mehr als eine Bestimmung vergessen
                        else if (count($Loesung_UebrigeBestimmungen) > 1) {

                            // Aktuelle mögliche Lösung kommt in die MöglichCollection
                            if ($MoeglicheLoesungSchonVorhanden != true) {
                                $Evaluierung_Loesungen_Moegliche = $Loesungen_Moegliche[ $Form ];
                                $MoeglicheLoesungSchonVorhanden = true;
                            }
                            $Evaluierung_Ergebnis = 'falsch';
                            $Evaluierung_Feedback = 'Es wurden zu viele Bestimmungen vergessen!';
                        }

                        // User hat eine Bestimmung eingegeben, die nicht in der aktuellen Lösung vorkommt.
                        // Oder die Eingabe passt nicht zur aktuellen Form
                        else if ($Eingabe_UebrigeBestimmungen->isNotEmpty()) {

                            // Aktuelle mögliche Lösung kommt in die MöglichCollection
                            if ($MoeglicheLoesungSchonVorhanden != true) {
                                $Evaluierung_Loesungen_Moegliche = $Loesungen_Moegliche[ $Form ];
                                $MoeglicheLoesungSchonVorhanden = true;
                            }
                            $Evaluierung_Ergebnis = 'falsch';
                            $Evaluierung_Feedback = 'Es wurden falsche Eingaben gemacht';

                        }

                    }
                    $Evaluierung_Eingaben_Alle = $Evaluierung_Eingaben_Alle->push($Evaluierung_Eingaben);
                    $Evaluierung_Eingaben = collect();

                    $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Loesungen_Moegliche', $Evaluierung_Loesungen_Moegliche);
                    $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Ergebnis', $Evaluierung_Ergebnis);
                }


            }
            // Der Rest kommt in eine Extra-Collection
            else {
                $Evaluierung_Loesungen_Moegliche = $Loesungen_Moegliche[ $Form ];
                $Evaluierung_Ergebnis = 'falsch';
                $Evaluierung_Feedback = 'Es wurde keine Form eingegeben!';
            }

            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Loesungen_Moegliche', $Evaluierung_Loesungen_Moegliche);
            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Loesungen_Uebrige', $Evaluierung_Loesungen_Uebrige);
            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Eingaben_Alle', $Evaluierung_Eingaben_Alle);
            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Ergebnis', $Evaluierung_Ergebnis);
            $Evaluierung_Data = $Evaluierung_Data->put('Evaluierung_Feedback', $Evaluierung_Feedback);

            $Evaluierung_FuerAlle = $Evaluierung_FuerAlle->put($Form, $Evaluierung_Data);
            $Evaluierung_Loesungen_Moegliche = collect();
            $Evaluierung_Eingaben_Alle = collect();
            $Evaluierung_Ergebnis = collect();
            $Evaluierung_Data = collect();

        }

        return $Evaluierung_FuerAlle;

    }

}
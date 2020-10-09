<?php

namespace App\Traits\Glossarium;
use App\Helpers\Helper;


/**
 * Model-Trait, der für alle instantiierten Objekte die Eigenschaft $alle_formen erlaubt
 */
trait HasAlleFormenAttribute
{


    /**
     * Gibt eine Collection ALLER möglichen Formen + Lemma zurück, um darin zu suchen
     * @return Collection
     */
    public function getAlleFormenAttribute()
    {
        // Wenn die Form morphologisiert ist, sammle die Formen + Lemma
        if ( ! empty($this->morph)) {
            $FormenArray = json_decode($this->morph, true);
            $FormenArrayFlattened = Helper::array_flatten($FormenArray);
            $FormenArrayFlattened[] .= $this->lemma;
            $FormenCollectionFlattened = collect($FormenArrayFlattened);
            // Beginnt das Lemma mit einem Großbuchstaben, dann alle Formen in Kleinbuchstaben ausgeben
            // => Abgleich
            if (ctype_upper($this->lemma[0])) {
                return $FormenCollectionFlattened->map(function ($MixedForm, $key) {
                    return strtolower($MixedForm);
                });
            }
            return $FormenCollectionFlattened;

        }
        // andernfalls gib nur das Lemma zurück
        return collect($this->lemma);
    }

}
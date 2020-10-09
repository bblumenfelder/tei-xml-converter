<?php


namespace App\Traits\Services;


trait comparesMorphoStrings {
 public static function hasTwoConcordances(string $FoundPOSString, array $HermeneusPOS) {
     foreach ($HermeneusPOS as $HermeneusPOSString) {
         // dump("her: " . $HermeneusPOSString . " \napi: " . $FoundPOSString);
         similar_text($HermeneusPOSString, $FoundPOSString, $result);
         // dump("= " . $result);
         return $result > 75;
     }
 }
}
<?php


namespace App\ServiceClasses\Glossarium\VocabSanitizer;


use App\Repositories\VocabRepository;

class VocabSanitizer {

    /**
     * @var \Illuminate\Support\Collection
     */
    private static $list;



    public static function findDeficientVerben()
    {
        static::$list = collect();

        $DeficientVerben = (new VocabRepository())->all('verben')->get()->filter(function ($Vocab) {

            return (strpos($Vocab->morph, "\"urum\"") !== false) || (strpos($Vocab->morph, "\"1_mask\": \"us essem\"") !== false) || (strpos($Vocab->morph, "\"1_sg1\": \"i\"") !== false);
        });
        static::$list['verben'] = $DeficientVerben;

        return new static;
    }



    public static function findDeficientAdjektive()
    {
        static::$list = collect();

        $DeficientVerben = (new VocabRepository())->all('adjektive')->get()->filter(function ($Vocab) {
            return $Vocab->fb_komparativ == '--' ||  $Vocab->fb_komparativ == '-' || $Vocab->fb_superlativ == '--' || $Vocab->fb_superlativ == '-';
        });
        static::$list['adjektive'] = $DeficientVerben;

        return new static;
    }



    public static function sanitize(string $wortart = null)
    {
        if ($wortart) {
            call_user_func('App\ServiceClasses\Glossarium\VocabSanitizer\VocabSanitizer::sanitize' . ucfirst($wortart));
        }
        else {
            self::sanitizeAdjektive();
            self::sanitizeEigennamen();
            self::sanitizeNomina();
            self::sanitizeNumeralia();
            self::sanitizePartikel();
            self::sanitizePronomina();
            self::sanitizeVerben();
            self::sanitizeWendungen();
        }

        return new static;
    }



    public static function sanitizeAdjektive()
    {
        static::$list['adjektive']->each(function ($Vocab) {
            if ($Vocab->fb_komparativ == '--' ||  $Vocab->fb_komparativ == '-' || $Vocab->fb_superlativ == '--' || $Vocab->fb_superlativ == '-') {
                $Vocab->update(['fb_hat_komparativ_superlativ' => 0]);
                $Vocab->update(['fb_komparativ' => null]);
                $Vocab->update(['fb_superlativ' => null]);
                dump('Morphologisiere ' . $Vocab->lemma . ' ...');
                $Vocab->morph();
            }
        });
    }



    public static function sanitizeEigennamen()
    {
    }



    public static function sanitizeNomina()
    {
    }



    public static function sanitizeNumeralia()
    {
    }



    public static function sanitizePartikel()
    {
    }



    public static function sanitizePronomina()
    {
    }



    public static function sanitizeVerben()
    {
        static::$list['verben']->each(function ($Vocab) {
            dump('Morphologisiere ' . $Vocab->lemma . ' ...');
            $Vocab->morph();
        });
    }



    public static function sanitizeWendungen()
    {
    }



    public static function list(string $wortart = null)
    {
        return isset($wortart) ? static::$list[ $wortart ] : static::$list;
    }
}
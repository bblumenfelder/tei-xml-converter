<?php


namespace App\ServiceClasses\Uebungen;


use ReflectionClass;

class UebungCreator {

    private static $UebungModel;
    /**
     * @var array
     */
    private static $UebungContent;



    public static function make($UebungModel)
    {
        static::$UebungModel = $UebungModel;
        return new static;
    }



    public static function fromVocab(array $LerneinheitVocab)
    {
        static::$UebungContent = array_map(function ($LerneinheitWort) {
            return [
                'value_given' => call_user_func(static::$UebungModel . "::givenValue", $LerneinheitWort),
                'value_input' => '',
                'value_expected' => call_user_func(static::$UebungModel . "::expectedValue", $LerneinheitWort),
                'hint' => '',
                'quantifier' => 1,
            ];
        }, $LerneinheitVocab);
        return new static;
    }



    public static function get()
    {
        return static::$UebungContent;
    }

}
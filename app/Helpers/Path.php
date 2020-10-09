<?php

namespace App\Helpers;


use App\User;

class Path {

    public static function file_local_tei_hermeneus_xml_schmema_rng()
    {
        return public_path('tei\schema\tei_hermeneus.rng');
    }



    public static function path_public_tei_hermeneus_xml_schmema_rng()
    {
        return 'http://www.hermeneus.eu/tei/schema/tei_hermeneus.rng';
    }



    public static function file_corpus_index()
    {
        return storage_path('app\\texte\\corpus\\index.xml');
    }



    public static function file_corpus_author_abbrev_lookup()
    {
        return storage_path('app\\texte\\corpus\\author_abbrev_lookup.json');
    }



    public static function file_corpus_author_common_lookup()
    {
        return storage_path('app\\texte\\corpus\\author_common_lookup.json');
    }



    public static function file_corpus_title_abbrev_lookup()
    {
        return storage_path('app\\texte\\corpus\\title_abbrev_lookup.json');
    }



    public static function file_corpus_highest_subsection_lookup()
    {
        return storage_path('app\\texte\\corpus\\highest_subsection_lookup.json');
    }



    /*    public static function file_tei_elements()
        {
            return storage_path('app\\texte\\corpus\\tei_elements.json');
        }*/


    /**
     * UNIX
     * @return string
     */
    public static function file_tei_elements()
    {
        return storage_path('app/texte/corpus/tei_elements.json');
    }



    public static function file_punctuation_marks()
    {
        return storage_path('app\\json\\punctuation_marks.json');
    }



    public static function path_corpus_index()
    {
        return storage_path('app/texte/corpus/');
    }



    public static function path_logs()
    {
        return storage_path('logs/');
    }



    public static function file_corpus_text(string $Filename)
    {
        return storage_path('app\\texte\\corpus\\tei\\' . $Filename);
    }



    public static function path_corpus_texts()
    {
        return storage_path('app\\texte\\corpus\\tei\\');
    }



    /**
     * @param User $user
     * @return bool|string
     */
    public static function path_user_avatar(User $user)
    {
        $avatar_path = Path::path_user_storage($user) . "avatar.png";

        return is_file($avatar_path) ? $avatar_path : false;
    }



    /**
     * @param User $user
     * @return string
     */
    public static function path_user_storage(User $user)
    {
        $user_dir = storage_path('app\\users\\' . $user->username . '\\');
        if ( ! is_dir($user_dir)) {
            mkdir($user_dir);
        }

        return $user_dir;
    }



    public static function path_xslt()
    {
        return storage_path('app\\xslt\\');
    }



    public static function path_tei_hermeneus_xslt()
    {
        return storage_path('app/xslt/tei-hermeneus.xslt');
        //return app_path('../../hermeneus_assets/xslt_hermeneus.editor/xslt/tei-hermeneus.xslt');
    }



    public static function hermeneus_assets_path()
    {
        return '../hermeneus_assets';
    }
}
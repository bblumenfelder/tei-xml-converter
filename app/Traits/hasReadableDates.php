<?php


namespace App\Traits;


use Illuminate\Support\Carbon;

trait hasReadableDates {
    /**
     * Last updated at ...
     * @return string
     * @throws \Exception
     */
    public function getUpdatedAtReadableAttribute()
    {
        return (new Carbon($this['updated_at']))->locale('de')->isoFormat('DD.MM.Y H:mm U\hr');
    }
    /**
     * Last created at ...
     * @return string
     * @throws \Exception
     */
    public function getCreatedAtReadableAttribute()
    {
        return (new Carbon($this['created_at']))->locale('de')->isoFormat('DD.MM.Y H:mm U\hr');
    }

    /**
     * Last created at ...
     * @return string
     * @throws \Exception
     */
    public function getDeletedAtReadableAttribute()
    {
        return (new Carbon($this['deleted_at']))->locale('de')->isoFormat('DD.MM.Y H:mm U\hr');
    }

}
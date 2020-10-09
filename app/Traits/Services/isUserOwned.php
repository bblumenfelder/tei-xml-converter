<?php


namespace App\Traits\Services;

use App\User;

/**
 * Model Accessor
 * Trait isUserOwned
 * @package App\Traits\Services
 */
trait isUserOwned {
    public function getUserOwnedAttribute()
    {
        $LoggedUser = auth()->user();
        return isset($LoggedUser) ? $this->user_id === auth()->user()->id : false;
    }


}
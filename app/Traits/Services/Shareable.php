<?php

namespace App\Traits\Services;


use App\Helpers\Helper;
use App\Tessera;

trait Shareable  {


    public function share(string $ModelMode = '')
    {
        $ExistingShortlink = $this->existingShortlink($ModelMode);
        if (count($ExistingShortlink) > 0) {
            return $ExistingShortlink[0];
        }
        else {
            return Tessera::create([
                'hash' => Tessera::make_hash(),
                'model' => $this->getShareableData($ModelMode)['model'],
                'model_id' => $this->getShareableData($ModelMode)['model_id'],
                'model_mode' => $ModelMode,
                'url' => $this->getShareableData($ModelMode)['url'],
                'user_id' => (Helper::getCurrentUser())->id,
            ]);
        }
    }



    /**
     * Is there already a shortlink for the current instance in the database?
     * @param string $ModelMode
     * @return mixed
     */
    public function existingShortlink(string $ModelMode = '')
    {
        return Tessera::where([
            'model' => $this->getShareableData($ModelMode)['model'],
            'model_id' => $this->getShareableData($ModelMode)['model_id'],
            'model_mode' => $ModelMode,
            'url' => $this->getShareableData($ModelMode)['url'],
            'user_id' => (Helper::getCurrentUser())->id,
        ])->get();
    }

}
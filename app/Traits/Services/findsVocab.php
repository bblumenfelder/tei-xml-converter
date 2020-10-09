<?php


namespace App\Traits\Services;


use App\Repositories\VocabRepository;

trait findsVocab {

    /**
     * Get vocab by given id-array or all of wortart
     * @param string $wortart
     * @param array|null $ids
     * @return \Illuminate\Support\Collection
     */
    public static function findVocabStatic(string $wortart, array $ids = null)
    {
        $VocabRepository = new VocabRepository();

        return $ids !== null ? $VocabRepository->find($wortart, $ids)->get() : $VocabRepository->all($wortart)->get();
    }



    /**
     * Get vocab by given id-array or all of wortart
     * @param string $wortart
     * @param array|null $ids
     * @return \Illuminate\Support\Collection
     */
    public function findVocab(string $wortart, array $ids = null)
    {
        $VocabRepository = new VocabRepository();

        return $ids !== null ? $VocabRepository->find($wortart, $ids)->get() : $VocabRepository->all($wortart)->get();
    }
}
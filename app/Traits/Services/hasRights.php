<?php

namespace App\Traits\Services;


trait hasRights {

    public $role;

    /**
     * 1 = ADMIN
     * 2 = EDITOR
     * 3 = TEACHER
     * 4 = USER
     */

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this['role'] === $role;
    }



    public function getHierarchyAttribute()
    {
        return self::HIERARCHY[ $this['role'] ];
    }



    public function lowerHierarchyThan(int $int)
    {
        return self::HIERARCHY[ $this['role'] ] < $int;
    }



    public function higherHierarchyThan(int $int)
    {
        return self::HIERARCHY[ $this['role'] ] > $int;
    }



    public function hasHierarchy(int $int)
    {
        return self::HIERARCHY[ $this['role'] ] === $int;
    }



    public function hasAtLeastHierarchy(int $int)
    {
        return self::HIERARCHY[ $this['role'] ] <= $int;
    }



    public function hasAtMaxHierarchy(int $int)
    {
        return self::HIERARCHY[ $this['role'] ] >= $int;
    }



    /**
     * User has subscription?
     * @return bool
     */
    public function hasSubscription()
    {
        return false;
    }



    /**
     * Central rights management
     * @return array
     */
    public function getRightsAttribute()
    {

        return [
            'deleteLerninhalt' => $this->canDeleteLerninhalt(),
            'createLerninhalt' => $this->canCreateLerninhalt(),
            'verifyVocab' => $this->canVerifyVocab(),
            'unverifyVocab' => $this->canUnverifyVocab(),
            'createVocab' => $this->canCreateVocab(),
            'deleteVocab' => $this->canDeleteVocab(),
            'editVocab' => $this->canEditVocab(),
            'morphVocab' => $this->canMorphVocab(),
            'editVerweise' => $this->canEditVerweise(),
            'editAnyText' => $this->canEditAnyText(),
            'viewAdminpanel' => $this->canViewAdminpanel(),
            'manageServerlogs' => $this->canManageServerlogs(),
            'manageUsers' => $this->canManageUsers(),
            'manageTexts' => $this->canManageTexts(),
            'accessAdvancedFeatures' => $this->canAccessAdvancedFeatures(),
            'accessSubscriptionFeatures' => $this->canAccessSubscriptionFeatures(),
        ];
    }



    public static function guest()
    {

        return [
            'deleteLerninhalt' => false,
            'createLerninhalt' => false,
            'verifyVocab' => false,
            'unverifyVocab' => false,
            'createVocab' => false,
            'accessAdvancedFeatures' => false,
            'accessSubscriptionFeatures' => false,
            'deleteVocab' => false,
            'editVocab' => false,
            'editVerweise' => false,
            'morphVocab' => false,
            'viewAdminpanel' => false,
            'manageServerlogs' => false,

        ];
    }



    private function canViewAdminpanel()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canManageServerlogs()
    {
        return $this->hasAtLeastHierarchy(1);
    }



    private function canManageUsers()
    {
        return $this->hasAtLeastHierarchy(1);
    }



    private function canManageTexts()
    {
        return $this->hasAtLeastHierarchy(1);
    }



    private function canCreateLerninhalt()
    {
        return $this->hasAtLeastHierarchy(4);
    }



    private function canDeleteLerninhalt()
    {
        return $this->hasAtLeastHierarchy(4);
    }



    private function canVerifyVocab()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canUnverifyVocab()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canEditAnyText()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canDeleteVocab()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canEditVocab()
    {
        return $this->hasAtLeastHierarchy(3);
    }



    private function canEditVerweise()
    {
        return $this->hasAtLeastHierarchy(3);
    }



    private function canAccessAdvancedFeatures()
    {
        return $this->hasAtLeastHierarchy(2);
    }



    private function canAccessSubscriptionFeatures()
    {
        return $this->hasSubscription() || $this->hasAtLeastHierarchy(2);
    }



    private function canCreateVocab()
    {
        return $this->hasAtLeastHierarchy(3);
    }



    private function canMorphVocab()
    {
        return $this->hasAtLeastHierarchy(3);
    }
}
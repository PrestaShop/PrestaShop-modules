<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for SetCategory queries.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class SetCategoryParam extends RequestParamBase
{

    protected $category = "";
    protected $profileMapId = 0;


    /**
     * Gets the category name.
     * @return string the category name
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Sets the category name.
     * @param string $category the category name
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * Gets the profile identifier.
     * @return integer the profile identifier
     */
    public function getProfileMapId() {
        return $this->profileMapId;
    }

    /**
     * Sets the profile identifier, possible values are fixed with the help of prediggo.
     * @param integer $profileMapId the profile identifier.
     */
    public function setProfileMapId($profileMapId) {
        $this->profileMapId = $profileMapId;
    }


 
}


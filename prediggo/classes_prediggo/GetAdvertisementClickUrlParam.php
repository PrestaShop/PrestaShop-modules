<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for getAdvertisementClickUrl queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetAdvertisementClickUrlParam extends RequestParamBase
{

    protected $clickId = "";

    /**
     * Sets the ID of the clicked Ad
     * @param string $clickId
     */
    public function setClickId($clickId) {
        $this->clickId = $clickId;
    }

    /**
     * Gets the ID of the clicked Ad
     * @return string
     */
    public function getClickId() {
        return $this->clickId;
    }


}


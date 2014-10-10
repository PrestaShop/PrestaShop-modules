<?php

require_once 'RequestResultBase.php';

/**
 * This class represents the result of a getAdvertisementClickUrl query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetAdvertisementClickUrlResult extends RequestResultBase {

    private $clickUrl = "";

    /**
     * @param string $clickUrl
     */
    public function setClickUrl($clickUrl) {
        $this->clickUrl = $clickUrl;
    }

    /**
     * Returns the URL corresponding to the Ad
     * @return string
     */
    public function getClickUrl() {
        return $this->clickUrl;
    }

}

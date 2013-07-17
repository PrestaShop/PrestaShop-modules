<?php

/**
 * Common base class for all servlet request parameter classes
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class RequestParamBase
{
   
    protected  $serverUrl = "";
    protected  $timeout = 4000;

    protected  $shopId = "";
    protected  $sessionId = "";
    protected  $variantId = "";


    /**
     * Gets the server url
     * @return string the server Url
     */
    public function getServerUrl() {
        return $this->serverUrl;
    }

    /**
     * Sets the server Url.
     * @param string $serverUrl the server Url
     */
    public function setServerUrl($serverUrl) {
        $this->serverUrl = $serverUrl;
    }

    /**
     * Gets the timeout in milliseconds for this request.
     * @return integer the timeout value
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Sets the timeout in milliseconds.
     * @param integer $timeout the timeout value
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * Gets the shop identifier.
     * @return string the shop identifier
     */
    public function getShopId() {
        return $this->shopId;
    }

    /**
     * Sets the shop identifier (this value is assigned to you by preddiggo).
     * @param string $shopId the shop identifier
     */
    public function setShopId($shopId) {
        $this->shopId = $shopId;
    }

    /**
     * Gets the session identifier.
     * @return string the session identifier
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Sets the session identifier. (usually session_id() )
     * @param string $sessionId the session identifier
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }
    
    /**
     * Gets the variant id, some requests may use this parameter to tell prediggo to
     * behave differently.
     * 
     * @return string the variant identifier
     */
    public function getVariantId() {
        return $this->variantId;
    }

    /**
     * Sets the variant id, some requests may use this parameter to tell prediggo to
     * behave differently.
     * @param string $variantId the variant identifier
     */
    public function setVariantId($variantId) {
        $this->variantId = $variantId;
    }


}


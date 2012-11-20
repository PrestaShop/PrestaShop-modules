<?php


/**
 * A product suggestion from an autocomplete request.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ProductSuggestion
{

    private $productId = "";
    private $productName = "";
    private $additionalAttributes = array();

    protected $notificationId = "";
    
     /**
     * Gets the identifier to use for reporting a click to prediggo
     * @return string the recommendation identifier
     */
    public function getNotificationId() {
        return $this->notificationId;
    }

    /**
     * Sets the identifier to use for reporting a click to prediggo.
     * @param string $notificationId the recommendation identifier
     */
    public function setNotificationId($notificationId) {
        $this->notificationId = $notificationId;
    }
    
    /**
     * Get the product identifier on customer side (=your side)
     * @return string The product identifier
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set the product identifier on customer side (=your side)
     * @param string $productId The product identifier
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * Get the product name, used mostly for debugging purpose.
     * @return string the product name
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set the product name, used mostly for debugging purpose.
     * @param string $productName The product name
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

        /**
     * Gets the additional attributes returned by the server (on customer's demand)
     * @return array an array in which keys are attribute names.
     */
    public function getAdditionalAttributes() {
        return $this->additionalAttributes;
    }

    /**
     * Adds an addtional attribute in the collection
     * @param $key string the attribute name
     * @param $value string the attribute value
     */
    public function setAdditionalAttribute($key, $value) {
        $this->additionalAttributes[$key] = $value;
    }
}

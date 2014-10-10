<?php

require_once 'RecDimensionConstants.php';

/**
 * Class representing a recommended product.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ItemRecommendation
{
    protected $dimension = "" ;
    protected $itemId = "";
    protected $notificationId = "";
    protected $itemName = "";
    protected $inferredFrom = "";
    protected $ruleId = 0;
    protected $additionalAttributes = array();


    /**
     * Gets the dimension used to compute this recommendation
     * @return string the dimension
     * @see RecDimensionConstants
     */
    public function getDimension() {
        return $this->dimension;
    }

    /**
     * Sets the dimension used to compute this recommendation
     * @param string $dimension the dimension
     * @see RecDimensionConstants
     */
    public function setDimension($dimension) {
        $this->dimension = $dimension;
    }

    /**
     * Gets the item identifier, as submitted in items.xml.
     * @return string the item identifier
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * Sets the item identifier.
     * @param string $itemId the item identifier
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;
    }


    /**
     * Gets the Rule identifier in the backoffice, for debugging purpose only.
     * @return string the Rule identifier
     */
    public function getRuleId() {
        return $this->ruleId;
    }

    /**
     * Gets the Rule identifier in the backoffice.
     * @param string $ruleId the Rule identifier
     */
    public function setRuleId($ruleId) {
        $this->ruleId = $ruleId;
    }


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
     * Gets the item name, as submitted in items.xml.
     * @return string the item name
     */
    public function getItemName() {
        return $this->itemName;
    }

    /**
     * Sets the item name.
     * @param string $itemName the item name
     */
    public function setItemName($itemName) {
        $this->itemName = $itemName;
    }

    /**
     * Gets the base of this recommendation, usually an another item.
     * @return string the base of this recommendation
     */
    public function getInferredFrom() {
        return $this->inferredFrom;
    }

    /**
     * Sets the base of this recommendation.
     * @param string $inferredFrom the base of this recommendation
     */
    public function setInferredFrom($inferredFrom) {
        $this->inferredFrom = $inferredFrom;
    }

    /**
     * Gets the additional attributes returned by the server (on customer's demand)
     * @return array an array in which keys are attribute names.
     */
    public function getAdditionalAttributes() {
        return $this->additionalAttributes;
    }

    /**
     * Adds an additional attribute in the collection
     * @param $key string the attribute name
     * @param $value string the attribute value
     */
    public function setAdditionalAttribute($key, $value) {
        $this->additionalAttributes[$key] = $value;
    }

}


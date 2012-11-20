<?php

require_once "Utils.php";
require_once "GetFilteredRecommendationParam.php";

/**
 * Parameter class for getBasketRecommendation queries
 *
 * @package prediggo4php
 * @subpackage types
 * 
 * @author Stef
 */
class GetBasketRecommendationParam extends GetFilteredRecommendationParam
{

    protected $basketItems = array();

    /**
     * Gets the list of items. Items are made of Key/Value pairs representing the profile identifier followed by the item ID.
     * @return array An array of pairs representing items (int and string)
     */
    public function getBasketItems()
    {
        return $this->basketItems;
    }

    /**
     * Adds an item to the list.
     * @param integer $profileMapId The profile identifier of this item
     * @param string $itemId The item identifier
     */
    public function addBasketItem( $profileMapId, $itemId)
    {
        Utils::addPairToUniqueArray( $this->basketItems, $profileMapId, $itemId);
    }



}


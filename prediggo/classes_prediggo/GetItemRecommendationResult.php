<?php

require_once 'BundleRecommendation.php';
require_once 'GetRecommendationResultBase.php';

/**
 * This class represents the result of a GetItemRecommendation query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetItemRecommendationResult extends GetRecommendationResultBase
{
    
    private $itemId = "";
    private $recommendedBundles = array();

    /**
     * Gets the item ID that was passed to the request.
     * @return string the item id
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * Sets the item ID that was passed to the request.
     * @param string $itemId the item id to set
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;
    }

    /**
     * Gets a list of recommended bundles.
     * @return array an array of BundleRecommendation objects.
     */
    public function getRecommendedBundles() {
        return $this->recommendedBundles;
    }

    /**
     * Adds a new recommended bundle to this collection. Should not be called by customer code.
     * @param BundleRecommendation $bundle The bundled item to add.
     */
    public function addRecommendedBundles(BundleRecommendation $bundle) {
        $this->recommendedBundles[] = $bundle;
    }

}

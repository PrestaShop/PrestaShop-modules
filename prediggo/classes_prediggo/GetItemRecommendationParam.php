<?php
require_once "ItemRequestInfo.php";
require_once 'GetFilteredRecommendationParam.php';


/**
 * Parameter class for getItemRecommendation queries.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetItemRecommendationParam extends GetFilteredRecommendationParam
{

   protected $itemInfo ;

    /**
     * default constructor
     */
    function __construct ()
    {
        $this->itemInfo = new ItemRequestInfo();
    }


    /**
     * Gets the item definition.
     * @return ItemRequestInfo The item definition
     */
    public function getItemInfo() {
        return $this->itemInfo;
    }

    /**
     * Replaces the current item definition by a given one.
     * @param ItemRequestInfo $itemInfo the item definition to set
     */
    public function setItemInfo(ItemRequestInfo $itemInfo) {
        $this->itemInfo = $itemInfo;
    }
}

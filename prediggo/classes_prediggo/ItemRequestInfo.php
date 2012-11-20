<?php


require_once('Utils.php' );

/**
 * Data class representing a minimal product description.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ItemRequestInfo
{
   
    protected  $itemId = "";
    protected  $itemName = "";
    protected  $attributes = array();


     /**
     * Gets the item identifier.
     * @return string the item identifier
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * Sets the item identifier.
     * @param string $itemId The Item identifier.
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;
    }

    /**
     * Gets the item name.
     * @return string the item name
     */
    public function getItemName() {
        return $this->itemName;
    }

    /**
     * Sets the item name. Useful if itemid is not known on prediggo side.
     * @param string $itemName the item name.
     */
    public function setItemName($itemName) {
        $this->itemName = $itemName;
    }


    /**
     * Gets the list of attributes. Should be considered read only.
     * Restrictions are made of Key/Value pairs representing the name of the restricted attribute and the desired matching value.
     * @return array A set of attributes (Pair of attribute name and value)
     */
    public function getAttributes()
    {
        return $this->attributes;
    }


    /**
     * Adds a new attribute to this item description.
     * @param string $attributeName The name of the attribute you want to specify.
     * @param string $value The attribute value.
     */
    public function addAttribute( $attributeName, $value )
    {
        Utils::addPairToUniqueArray($this->attributes, $attributeName, $value);
    }

}

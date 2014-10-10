<?php

require_once "RequestResultBase.php";
require_once "AdBlock.php";

/**
 * This class represents the result of a GetAdvertisement query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetAdvertisementResult extends RequestResultBase {


    protected $pageId;
    protected $pageName;
    protected $blocks = array();


    /**
     * Returns the page ID submitted in the request
     */
    public function getPageId() {
        return $this->pageId;
    }

    public function setPageId( $pageId) {
        $this->pageId = $pageId;
    }

    /**
     * The page name as configured in the backoffice
     * @return
     */
    public function getPageName() {
        return $this->pageName;
    }

    public function setPageName($pageName) {
        $this->pageName = $pageName;
    }


    /**
     * Returns the list of blocks in the page
     * @return array[AdBlock]
     */
    public function getBlocks() {
        return $this->blocks;
    }

    /**
     * @param $block
     */
    public function addBlock($block) {
        $this->blocks[] = $block;
    }

    /**
     * Retrieves a block object from the result based on its ID
     *
     * @param int $blockId the ID of the block
     * @return AdBlock The block with specified ID, null if not found
     */
    public function blockById($blockId) {

        foreach ( $this->blocks as $block) {
            if( $block->getBlockId() == $blockId ) {
                return $block;
            }
        }

        return null;
    }

    /**
     * Returns a list of Ads for the given blockId, this is a null safe alternative to blockById(blockID).getAds()
     * @param $blockId The block id
     * @return array[AdResult] the list of ads, or an empty list if block ID is not found
     */
    public function adsByBlock($blockId) {

        $requestedBlock = $this->blockById($blockId);

        if($requestedBlock === null) {
            return array();
        }

        return $requestedBlock->getAds();
    }


}

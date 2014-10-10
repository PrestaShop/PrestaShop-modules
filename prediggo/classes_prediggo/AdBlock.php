<?php

require_once("AdResult.php");

/**
 * A block description containing Ads, a block usually correspond to an Ad slot on a web page
 */
class AdBlock {

    protected $blockId;
    protected $blockName;
    protected $ads = array();


    /**
     * Gets the identifier of the block, defined by prediggo
     * @return
     */
    public function getBlockId() {
        return $this->blockId;
    }

    public function setBlockId($blockId) {
        $this->blockId = $blockId;
    }

    /**
     * Get the name of the block, mostly for debugging purpose
     * @return
     */
    public function getBlockName() {
        return $this->blockName;
    }

    public function setBlockName($blockName) {
        $this->blockName = $blockName;
    }

    /**
     * Returns the list of ads for this block, can be empty, never null
     * @return array[AdResult] a list of ads
     */
    public function getAds() {
        return $this->ads;
    }

    public function setAds($ads) {
        $this->ads = $ads;
    }

    public function addAd(AdResult $ad) {
        $this->ads[] = $ad;
    }


} 
<?php
/**
 * An Ad description
 */
class AdResult {

    protected $adName = "";
    protected $pictureUrl = "";
    protected $clickUrl = "";
    protected $clickId = "";

    protected $properties = array();

    /**
     * Returns the ad name, primarily intended for debugging purpose
     * @return string
     */
    public function getAdName() {
        return $this->adName;
    }

    public function setAdName($adName) {
        $this->adName = $adName;
    }

    /**
     * The URL of the picture
     * @return string
     */
    public function getPictureUrl() {
        return $this->pictureUrl;
    }

    public function setPictureUrl($pictureUrl) {
        $this->pictureUrl = $pictureUrl;
    }

    /**
     * The url linked by this ad
     * @return string
     */
    public function getClickUrl() {
        return $this->clickUrl;
    }

    public function setClickUrl($clickUrl) {
        $this->clickUrl = $clickUrl;
    }


    public function setClickId($clickId) {
        $this->clickId = $clickId;
    }

    /**
     * This ad identifier
     * @return string
     */
    public function getClickId() {
        return $this->clickId;
    }




    /**
     * Key value pairs of user defined properties
     * @return array an associative array
     */
    public function getProperties() {
        return $this->properties;
    }

    public function addProperty($key, $value) {
        return $this->properties[$key] = $value;
    }



} 
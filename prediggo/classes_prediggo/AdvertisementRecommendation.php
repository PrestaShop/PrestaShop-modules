<?php



require_once 'AdFormatConstants.php';

/**
 * Class representing a recommended ad.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class AdvertisementRecommendation
{
        protected $format = AdFormatConstants::LEADERBOARD;
        protected $imageUrl = "";
        protected $posterUrl = "";
        protected $trailerUrl = "";
        protected $id = "";
        protected $websiteUrl = "";
        protected $adName = "";
        protected $adTypeText = "";


        /**
         * Gets this ad format.
         * @return string the ad format.
         * @see AdFormatConstants
         */
        public function getFormat() {
            return $this->format;
        }

        /**
         * Sets this ad format
         * @param string $format the format to set
         * @see AdFormatConstants
         */
        public function setFormat($format) {
            $this->format = $format;
        }


        /**
         * Gets the URL pointing to the picture. Dimensions should correspond to the "Format" specification.
         * @return string the picture url
         */
        public function getImageUrl() {
            return $this->imageUrl;
        }

        /**
         * Sets the URL pointing to the picture. Dimensions should correspond to the "Format" specification.
         * @param string $imageUrl the picture url to set
         */
        public function setImageUrl($imageUrl) {
            $this->imageUrl = $imageUrl;
        }


        /**
         * Gets the URL pointing to a larger sized picture.
         * @return string the poster url.
         */
        public function getPosterUrl() {
            return $this->posterUrl;
        }

        /**
         * Sets the URL pointing to a larger sized picture.
         * @param string $posterUrl the poster url to set.
         */
        public function setPosterUrl($posterUrl) {
            $this->posterUrl = $posterUrl;
        }


        /**
         * Gets the URL pointing to the trailer.
         * @return string the trailer url
         */
        public function getTrailerUrl() {
            return $this->trailerUrl;
        }


        /**
         * Sets the URL pointing to the trailer.
         * @param string $trailerUrl the trailer url to set
         */
        public function setTrailerUrl($trailerUrl) {
            $this->trailerUrl = $trailerUrl;
        }

        /**
         * Gets the identifier used by prediggo.
         * @return string the ad identifier
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Sets the identifier used by prediggo.
         * @param string $id the ad identifier
         */
        public function setId($id) {
            $this->id = $id;
        }

        /**
         * Gets the website URL.
         * @return string the website url
         */
        public function getWebsiteUrl() {
            return $this->websiteUrl;
        }

        /**
         * Sets the website URL.
         * @param string $websiteUrl the website url
         */
        public function setWebsiteUrl($websiteUrl) {
            $this->websiteUrl = $websiteUrl;
        }

        /**
         * Gets the advertised product name / title.
         * @return string the product name.
         */
        public function getAdName() {
            return $this->adName;
        }


        /**
         * Sets the advertised product name / title.
         * @param string $adName the name to set
         */
        public function setAdName($adName) {
            $this->adName = $adName;
        }

        /**
         * Gets a short text describing the ad type ("in cinema", "available on dvd"...).
         * @return string a short text description
         */
        public function getAdTypeText() {
            return $this->adTypeText;
        }

        /**
         * Sets a short text describing the ad type.
         * @param string $adTypeText the type text to set
         */
        public function setAdTypeText($adTypeText) {
            $this->adTypeText = $adTypeText;
        }
        
        


}


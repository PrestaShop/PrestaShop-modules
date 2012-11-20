<?php

/**
 * Description of a custom redirection. Custom redirections are defined by marketers in the prediggo control panel and may appear or not depending on
 * the occurence of particular keywords in the search query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class RedirectionObject
{

    private $targetUrl;
    private $pictureUrl;
    private $label;

    /**
     * Gets the redirection label, usually used as the text displayed as hyperlink.
     * @return string the redirection label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the redirection label
     * @param string $label the redirection label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get the url of a picture if any, can act as clickable image for an offer.
     * @return string The url of the picture if defined, can be blank.
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * Set the url of a picture if any, can act as clickable image for an offer.
     * @param string $pictureUrl The url of the picture if defined, can be blank.
     */
    public function setPictureUrl($pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;
    }

    /**
     * Get the destination url, usually the link "href" target.
     * @return string The destination url
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Set the destination url, usually the link "href" target.
     * @param string $targetUrl The link target
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

}

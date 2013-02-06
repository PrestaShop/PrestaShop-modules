<?php

/**
 * class for the tag <control>
 * 
 */
class CertissimControl extends CertissimXMLElement
{
  /**
   * initializes a stream with the root <control> without child
   * 
   */
  public function __construct()
  {
    parent::__construct("<control fianetmodule='api_prestashop_certissim' version='2.2.5'></control>");
  }

  /**
   * adds the child <crypt> to the tag <wallet>
   *
   * @param string $crypt crypt value
   * @param string $version crypt version
   */
  public function addCrypt($crypt, $version)
  {
    //find an existing tag <crypt>
    $elements = $this->getChildrenByName('crypt');
    //if <crypt> already exists
    if (count($elements) > 0)
    {
      //adds value and version
      $cryptelement = array_pop($elements);
      $cryptelement->setAttribute('version', $version);
      $cryptelement->setValue($crypt);
    }
    else //<crypt> does not exist yet
    {
      //initializa <crypt> tag
      $cryptelement = new CertissimXMLElement('<crypt version="'.$version.'">'.$crypt.'</crypt>');
      //gets the tag <wallet>
      $wallet = array_pop($this->getChildrenByName('wallet'));
      //add the tag <crypt> into the tag <wallet>
      $wallet->childCrypt($cryptelement);
    }
  }

  /**
   * adds the child <datelivr> into <wallet>
   *
   * @param date $date
   */
  public function addDatelivr($date)
  {
    //finds the tag <datelivr>
    $elements = $this->getChildrenByName('datelivr');
    //if tags already exists
    if (count($elements) > 0)
    {
      //adds value
      $datelivrelement = array_pop($elements);
      $datelivrelement->setValue($date);
    }
    else //if tag does not exist yet
    {
      //initialize the tag <datelivr>
      $datelivrelement = new CertissimXMLElement('<datelivr>'.$date.'</datelivr>');
      //gets the tag <wallet>
      $wallet = array_pop($this->getChildrenByName('wallet'));
      //adds the <datelivr> into the tag <wallet>
      $wallet->childDatelivr($datelivrelement);
    }
  }

}
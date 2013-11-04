<?php

class CertissimControl extends CertissimXMLElement
{

	public function __construct()
	{
		parent::__construct("<control fianetmodule='api_prestashop_certissim' version='3.0' certissimmodule='3.7'></control>");
	}

	/**
	 * add the child <crypt> to <wallet>
	 *
	 * @param string $crypt crypt value
	 * @param string $version crypt version
	 */
	public function addCrypt($crypt, $version)
	{
		$elements = $this->getChildrenByName('crypt');
		if (count($elements) > 0)
		{
			$cryptelement = array_pop($elements);
			$cryptelement->setAttribute('version', $version);
			$cryptelement->setValue($crypt);
		}
		else
		{
			$cryptelement = new CertissimXMLElement('<crypt version="'.$version.'">'.$crypt.'</crypt>');
			$wallet = array_pop($this->getChildrenByName('wallet'));
			$wallet->childCrypt($cryptelement);
		}
	}

	/**
	 * adds the child <datelivr> to <wallet>
	 *
	 * @param date $date
	 */
	public function addDatelivr($date)
	{
		$elements = $this->getChildrenByName('datelivr');
		if (count($elements) > 0)
		{
			$datelivrelement = array_pop($elements);
			$datelivrelement->setValue($date);
		}
		else
		{
			$datelivrelement = new CertissimXMLElement('<datelivr>'.$date.'</datelivr>');
			$wallet = array_pop($this->getChildrenByName('wallet'));
			$wallet->childDatelivr($datelivrelement);
		}
	}

}
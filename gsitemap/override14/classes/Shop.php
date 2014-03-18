
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Shop
 *
 * @author youness
 */
class Shop extends ShopCore
{

	public $domain;
	public $virtual_uri;
	public $base_uri;
	
	public function __construct()
	{
		parent::__construct();
		$this->domain = Tools::getShopDomain(true);		
		$this->virtual_uri = '';		
		$this->base_uri = '';		
	}		
	public function getBaseURL()
	{
		if (!isset($this->domain) || !$this->domain)
			return false;
		return 'http://'.$this->domain.$this->base_uri;
	}

}


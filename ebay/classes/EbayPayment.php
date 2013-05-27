<?php

class EbayPayment extends PaymentModule
{

	public function __construct() 
	{
		$this->name = 'ebay';
		parent::__construct();
	}

}


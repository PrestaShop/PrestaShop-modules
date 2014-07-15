<?php


include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');)
include(dirname(__FILE__) . '/ebay.php');

class ebaySynchronizeOrdersTask extends Ebay {

	public function __construct() {
		parent::__construct();
		$this->cronOrdersSync();
	}
}

new ebaySynchronizeOrdersTask();
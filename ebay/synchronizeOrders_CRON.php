<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__) . '/../../config/config.inc.php');

include(dirname(__FILE__) . '/cultureindoorsage.php');

class ebaySynchronizeOrdersTask extends Ebay {

	public function __construct() {
		parent::__construct();
		$this->cronOrdersSync();
	}
}

new ebaySynchronizeOrdersTask();
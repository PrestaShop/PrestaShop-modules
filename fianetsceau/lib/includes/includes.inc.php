<?php

define('SCEAU_USE_CDATA', true);
define('SCEAU_ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));
require_once SCEAU_ROOT_DIR . '/lib/includes/functions.inc.php';
require_once SCEAU_ROOT_DIR.'/lib/kernel/includes.inc.php';
require_once SCEAU_ROOT_DIR.'/lib/common/includes.inc.php';
require_once SCEAU_ROOT_DIR.'/lib/sceau/includes.inc.php';
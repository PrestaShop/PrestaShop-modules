<?php

require('../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/fidbag/class/fidbagWebService.php");

foreach ($_POST as $key => $value)
{
	if ($keyk == "MerchantCode")
		$arg[$key] = Configuration::get('FIDBAG_MERCHANT_CODE');
	else
		$arg[$key] = Tools::safeOutput($value);
}

$webService = new FidbagWebService();
$return = $webService->action('LostPassword', $arg);
$result = 'LostPasswordResult';

$json_return = Tools::jsonDecode($return->$result);

echo $json_return;

?>
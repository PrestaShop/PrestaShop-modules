<?php 

include_once(dirname(__FILE__).'../../../../../config/config.inc.php');


if (Tools::getValue('token') == sha1(_COOKIE_KEY_.'fianet'))
{
	header("content-type: application/xml");
	echo file_get_contents(sha1(_COOKIE_KEY_.'fianet_log').'.xml');
}
else
	die('INVALID TOKEN');

?>
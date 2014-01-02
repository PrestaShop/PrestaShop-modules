<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.1 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

if (!isset($_GET['function']) || !isset($_GET['token']) || Tools::getValue('token') != Configuration::get('ZINGAYA_TOKEN'))
	die;

$zingaya = Module::getInstanceByName('zingaya');
if ($zingaya->active)
{
	if ($_GET['function'] == 'regenerateButton')
		echo $zingaya->generateButton();

	if ($_GET['function'] == 'delete_widget')
		echo $zingaya->deleteWidget((int)$_GET['widget']);
}
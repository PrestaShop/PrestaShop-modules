<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');
require_once(dirname(__FILE__).'/../prediggo.php');


// Launch a Prediggo Notification
if (Tools::getValue('nId'))
{

	global $cookie;
	$oPrediggo = new Prediggo();

	$params = array(
		'cookie' => $cookie,
		'notificationId' => (int)Tools::getValue('nId')
	);
	die(Tools::jsonEncode($oPrediggo->setProductNotification($params)));
}

// Launch a Prediggo Autocomplete action
if (isset($_GET['q']))
{
	require_once(dirname(__FILE__).'/../controllers/PrediggoSearchController.php');

	global $cookie;

	ob_start();
	$oPrediggoSearchController = new PrediggoSearchController();
	$oPrediggoSearchController->setQuery(pSQL(Tools::getValue('q')));
	$aResult = $oPrediggoSearchController->getAutocomplete();
	ob_end_clean();
	die(Tools::jsonEncode($aResult));
}
// Launch a Prediggo Recommendations for the blocklayered module
if(Tools::getValue('id_category_layered'))
{
	global $cookie;
	$oPrediggo = new Prediggo();

	$params = array(
		'cookie' => $cookie,
		'id_category_layered' => (int)Tools::getValue('id_category_layered')
	);
	die(Tools::jsonEncode($oPrediggo->getBlockLayeredRecommendations($params)));

}
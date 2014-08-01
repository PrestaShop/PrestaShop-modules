<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/mondialrelay.php');
include_once(dirname(__FILE__).'/errorCode.php');
try{
	if (!Tools::getValue('token'))
		die('{"error":"Security error"}');
	if (sha1('mr'._COOKIE_KEY_.'Back') != Tools::getValue('token'))
		die('{"error":"Security error"}');
		
	$webservice = MondialRelay::MR_URL.'webservice/Web_Services.asmx?WSDL';
	$client = new SoapClient($webservice);
	$params = array();
	$params['Enseigne'] = Tools::getValue('enseigne');
	$params['Poids'] = '';
	$params['Taille'] = '';
	$params['CP'] = (Configuration::get('PS_SHOP_CODE')) ? Configuration::get('PS_SHOP_CODE') : '75000';
	$params['Ville'] = '';
	$id_country = (Configuration::get('PS_SHOP_COUNTRY_ID')) ? Configuration::get('PS_SHOP_COUNTRY_ID') : Configuration::get('PS_COUNTRY_DEFAULT');	
	$params['Pays'] = Country::getIsoById($id_country);
	$params['Action'] = ''; 	
	$concat = $params['Enseigne'].$params['Pays'].$params['Ville'].$params['CP'].$params['Poids'].Tools::getValue('key');
	$params['Security'] = Tools::strtoupper(md5($concat));	 
	$result_mr = $client->WSI2_RecherchePointRelais($params); 
	if (($errorNumber = $result_mr->WSI2_RecherchePointRelaisResult->STAT) != 0)
	{		
		echo '{"error":"'.str_replace('"', '', $statCode[$errorNumber]).'"}';
		die();
	} 
	echo '{"success":1}';
}
catch(Exception $e) {
	echo '{"error":"'.str_replace('"', '', $statCode[99]).'"}';
	die();
}
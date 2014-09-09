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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
	require_once(_PS_MODULE_DIR_.'seur/backward_compatibility/backward.php');

if (class_exists('SeurLib') == false)
	include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$context = Context::getContext();

if (version_compare(_PS_VERSION_, '1.5', '>='))
{
	if (!$context->customer->isLogged())
		exit;
}
else
{
	$cookie = $context->cookie;

	if (!$cookie->isLogged())
		exit;
}

ini_set('default_charset', 'UTF-8');

if (Tools::getValue('id_address_delivery'))
{
	if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('id_address_delivery')) != Tools::getValue('token'))
		exit;

	try
	{
		$address_delivery = new Address((int)Tools::getValue('id_address_delivery'), (int)$cookie->id_lang);

		$sc_options = array(
			'connection_timeout' => 30
		);

		$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_SP'), $sc_options);
		$xml = '
			<CAMPOS>
				<CODIGO_POSTAL>'.(int)$address_delivery->postcode.'</CODIGO_POSTAL>
				<NOM_CORTO></NOM_CORTO>
				<LATITUD></LATITUD>
				<LONGITUD></LONGITUD>
				<NOM_POBLACION></NOM_POBLACION>
				<COD_SERVICIO></COD_SERVICIO>
				<COD_PRODUCTO></COD_PRODUCTO>
				<USUARIO>'.Configuration::get('SEUR_WS_USERNAME').'</USUARIO>
				<PASSWORD>'.Configuration::get('SEUR_WS_PASSWORD').'</PASSWORD>
			</CAMPOS>';

		$data = array('in0' => Tools::strtoupper($xml));
		$response = $soap_client->puntosDeVentaStr( $data );
		$xml = simplexml_load_string( utf8_decode($response->out));
		$centro = array();
		$num = (int)$xml->attributes()->NUM[0];

		$module_instance = Module::getInstanceByName(SEUR_MODULE_NAME);
		$filename = 'getPickupPointsAjax';

		for ($i = 1; $i <= $num; $i++)
		{
			$name = 'REG'.$i;
			$centro[] = array(
				'company' => (string)$xml->$name->NOM_CENTRO_SEUR,
				'address' => (string)$xml->$name->COD_TIPO_VIA.'/ '.(string)$xml->$name->NOM_CORTO.', '.(string)$xml->$name->NUM_VIA,
				'address2' => sprintf($module_instance->l('Nº Centro: %1$s - Nº Vía: %2$s', $filename),
					(string)$xml->$name->COD_CENTRO_SEUR, (string)$xml->$name->COD_VIA),
				'codCentro' => (string)$xml->$name->COD_CENTRO_SEUR,
				'city' => (string)$xml->$name->NOM_POBLACION,
				'post_code' => (string)$xml->$name->CODIGO_POSTAL,
				'phone' => (string)$xml->$name->TELEFONO_1,
				'gMapDir' => (string)$xml->$name->COD_TIPO_VIA.'/ '.$xml->$name->NOM_CORTO.', '.$xml->$name->NUM_VIA.', '.$xml->$name->NOM_POBLACION,
				'position' => array('lat' => (float)$xml->$name->LATITUD, 'lng' => (float)$xml->$name->LONGITUD),
				'timetable' => (string)$xml->$name->HORARIO
			);
		}
		echo Tools::jsonEncode($centro);
	}
	catch (PrestaShopException $e)
	{
		$e->displayMessage();
	}
}

if (Tools::getValue('usr_id_address'))
{
	if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('usr_id_address')) != Tools::getValue('token'))
		exit;

	$usrAddress = new Address((int)Tools::getValue('usr_id_address'), (int)$cookie->id_lang );
	$gMapUsrDir = $usrAddress->address1.' '.$usrAddress->postcode.','.$usrAddress->city.','.$usrAddress->country;
	echo $gMapUsrDir;
}

if (Tools::getValue('savepos') && Tools::getValue('id_seur_pos'))
{
	if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('chosen_address_delivery')) != Tools::getValue('token'))
		exit;

	$id_cart = (int)$context->cart->id;
	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_cart`
		FROM `'._DB_PREFIX_.'seur_order_pos` 
		WHERE `id_cart` = "'.(int)$id_cart.'"
	');
	
	if ($result !== false)
	{
		echo '{"result":"'.Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
			UPDATE `'._DB_PREFIX_.'seur_order_pos` 
			SET 
				`id_seur_pos` = "'.(int)Tools::getValue('id_seur_pos').'", 
				`company` = "'.pSQL(urldecode(Tools::getValue('company'))).'", 
				`address` = "'.pSQL(urldecode(Tools::getValue('address'))).'", 
				`city` = "'.pSQL(urldecode(Tools::getValue('city'))).'", 
				`postal_code` = "'.pSQL(urldecode(Tools::getValue('post_code'))).'", 
				`timetable` = "'.pSQL(urldecode(Tools::getValue('timetable'))).'", 
				`phone` = "'.pSQL(urldecode(Tools::getValue('phone'))).'"
			WHERE `id_cart` = "'.(int)$id_cart.'"
		').'"}';
	}
	else
	{
		echo '{"result":"'.Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
			INSERT INTO `'._DB_PREFIX_.'seur_order_pos`
				(`id_cart`, `id_seur_pos`, `company`, `address`, `city`, `postal_code`, `timetable`, `phone`) 
			VALUES
				(
					"'.(int)$id_cart.'",
					"'.(int)Tools::getValue('id_seur_pos').'",
					"'.pSQL(urldecode(Tools::getValue('company'))).'",
					"'.pSQL(urldecode(Tools::getValue('address'))).'",
					"'.pSQL(urldecode(Tools::getValue('city'))).'",
					"'.pSQL(urldecode(Tools::getValue('post_code'))).'",
					"'.pSQL(urldecode(Tools::getValue('timetable'))).'",
					"'.pSQL(urldecode(Tools::getValue('phone'))).'"
				)
		').'"}';
	}
}
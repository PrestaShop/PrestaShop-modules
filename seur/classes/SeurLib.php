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

if (!defined('_PS_VERSION_'))
	exit;

if (!defined('SEUR_MODULE_NAME'))
	define('SEUR_MODULE_NAME', 'seur');

class SeurLib
{
	public static $baleares_states = array(
		'ES-IB' => 'Baleares'
	);

	public static $canarias_states = array(
		'ES-TF' => 'Santa Cruz de Tenerife', 'ES-GC' => 'Las Palmas'
	);

	public static $ceuta_melilla_states = array(
		'ES-CE' => 'Ceuta', 'ES-ML' => 'Melilla'
	);

	public static $spain_states = array(
		'ES-VI' => 'Alava',     'ES-AB' => 'Albacete',   'ES-A' => 'Alicante',       'ES-AL' => 'Almeria',          'ES-O' => 'Asturias',
		'ES-AV' => 'Avila',     'ES-BA' => 'Badajoz',    'ES-B' => 'Barcelona',      'ES-BU' => 'Burgos',           'ES-CC' => 'Caceres',
		'ES-CA' => 'Cadiz',     'ES-S' => 'Cantabria',   'ES-CS'  => 'Castellon',    'ES-CR' => 'Ciudad Real',      'ES-CO' => 'Cordoba',
		'ES-CU' => 'Cuenca',    'ES-GI' => 'Gerona',     'ES-GR' => 'Granada',       'ES-GU' => 'Guadalajara',      'ES-SS' => 'Guipuzcua',
		'ES-H' => 'Huelva',     'ES-HU' => 'Huesca',     'ES-J' => 'Jaen',           'ES-C'  => 'La Coruña',		'ES-L' => 'Lerida',
		'ES-LO' => 'La Rioja',  'ES-LE' => 'Leon',       'ES-LU' => 'Lugo',          'ES-MA' => 'Malaga',           'ES-M' => 'Madrid',
		'ES-MU' => 'Murcia',    'ES-NA' => 'Navarra',    'ES-OU' => 'Orense',        'ES-P' => 'Palencia',          'ES-PO' => 'Pontevedra',
		'ES-SA' => 'Salamanca', 'ES-SG' => 'Segovia',    'ES-SE' => 'Sevilla',       'ES-SO' => 'Soria',            'ES-T' => 'Tarragona',
		'ES-TE' => 'Teruel',    'ES-TO' => 'Toledo',     'ES-V' => 'Valencia',       'ES-VA' => 'Valladolid',       'ES-BI' => 'Vizcaya',
		'ES-ZA' => 'Zamora',    'ES-Z' => 'Zaragoza'
	);

	public static $street_types = array(
		'AUT' => 'AUTOVIA',          'AVD' => 'AVENIDA',  'CL' => 'CALLE',     'CRT' => 'CARRETERA',
		'CTO' => 'CENTRO COMERCIAL', 'EDF' => 'EDIFICIO', 'ENS' => 'ENSANCHE', 'GTA' => 'GLORIETA',
		'GRV' => 'GRAN VIA',         'PSO' => 'PASEO',    'PZA' => 'PLAZA',    'POL' => 'POLIGONO INDUSTRIAL',
		'RAM' => 'RAMBLA',           'RDA' => 'RONDA',    'ROT' => 'ROTONDA',  'TRV' => 'TRAVESIA',
		'URB' => 'URBANIZACION'
	);

	public static $seur_countries = array(
		'ES' => 'ESPAÑA', 'PT' => 'PORTUGAL'
	);

	public static $seur_zones = array(
		0 => 'Provincia',    1 => 'Peninsula',    2 => 'Portugal',
		3 => 'Baleares',     4 => 'Canarias',     5 => 'Ceuta/Melilla'
	);

	public static function displayErrors($error = null)
	{
		if (!empty($error))
		{
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				echo '<div class="error"><p>'.$error.'</p></div>';
			else
				echo '<div class="error"><p><img src="../img/admin/warning.gif" border="0" alt="Error Icon" /> '.$error.'</p></div>'; // @TODO echo ??
		}
	}

	public static function getConfiguration()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur_configuration`
			WHERE `id_seur_configuration` = 1'
		);
	}

	public static function getConfigurationField($campo)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT '.pSQL($campo).'
			FROM `'._DB_PREFIX_.'seur_configuration`
			WHERE `id_seur_configuration` = 1'
		);
	}

	public static function setConfigurationField($campo, $valor)
	{
		return Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur_configuration`
			SET `'.pSQL($campo).'`="'.(int)$valor.'"
			WHERE `id_seur_configuration` = 1'
		);
	}

	public static function getOrderPos($id_cart)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur_order_pos`
			WHERE `id_cart` = '.(int)$id_cart
		);
	}

	public static function getMerchantData()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur_merchant`
			WHERE `id_seur_datos` = 1'
		);
	}

	public static function getMerchantField($campo)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT '.pSQL($campo).'
			FROM `'._DB_PREFIX_.'seur_merchant`
			WHERE `id_seur_datos` = 1'
		);
	}

	public static function setMerchantField($campo, $valor)
	{
		return Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur_merchant`
			SET `'.pSQL($campo).'`="'.pSQL($valor).'"
			WHERE `id_seur_datos` = 1'
		);
	}

	public static function isPricesConfigured()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT( * )
			FROM  `'._DB_PREFIX_.'delivery`
			WHERE  `price` != 0
			AND  `id_carrier` IN (
				SELECT  `id_seur_carrier` AS `id`
				FROM  `'._DB_PREFIX_.'seur_history`
				WHERE  `active` =1
			)'
		);
	}

	public static function getSeurCarrier($type)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `id_seur_carrier` AS `id`, `type`, `active`
			FROM `'._DB_PREFIX_.'seur_history`
			WHERE `active` = 1 AND `type` = "'.pSQL($type).'"'
		);
	}

	public static function getSeurCarriers($active = true)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT `id_seur_carrier` AS `id`, `type`, `active`
			FROM `'._DB_PREFIX_.'seur_history`
			'.($active ? 'WHERE `active` = 1' : '')
		);
	}

	public static function getLastSeurCarriers()
	{
		Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('UPDATE `'._DB_PREFIX_.'seur_history` SET `active`= 0');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT `id_seur_carrier` AS `id`, `type`
			FROM `'._DB_PREFIX_.'seur_history`
			GROUP BY `type`'
		);
	}

	public static function updateSeurCarriers($new_carriers_seur)
	{
		$olds_carriers_seur = self::getSeurCarriers(false);

		$sql_new_carriers = 'INSERT IGNORE INTO `'._DB_PREFIX_.'seur_history` VALUES ';
		$sql_disable = 'UPDATE `'._DB_PREFIX_.'seur_history` SET `active` = 0 WHERE ';
		$sql_enable = 'UPDATE `'._DB_PREFIX_.'seur_history` SET `active` = 1 WHERE ';

		$enable_olds_carriers = false;

		foreach ($olds_carriers_seur as $old_carrier)
		{
			foreach ($new_carriers_seur as $key_new => $new_carrier_seur)
			{
				if (($new_carrier_seur['id'] == $old_carrier['id']) && ($new_carrier_seur['type'] == $old_carrier['type']))
				{
					if ($old_carrier['active'] == 0)
					{
						$sql_enable .= ' (`id_seur_carrier` = '.(int)$old_carrier['id'].' AND `type` = "'.pSQL($old_carrier['type']).'") OR ';
						$sql_disable .= '`type` ="'.pSQL($old_carrier['type']).'" OR ';
						$enable_olds_carriers = true;
					}
					unset($new_carriers_seur[$key_new]);
				}
			}
		}

		foreach ($new_carriers_seur as $new_carrier_seur)
		{
			$sql_new_carriers .= '('.(int)$new_carrier_seur['id'].',"'.pSQL($new_carrier_seur['type']).'",1),';
			$sql_disable .= '`type` ="'.pSQL($new_carrier_seur['type']).'" OR ';
		}

		$sql_disable = trim($sql_disable, 'OR ');
		$sql_disable .= ';';

		$sql_enable = trim($sql_enable, 'OR ');
		$sql_enable .= ';';

		if (!empty($new_carriers_seur))
		{
			Db::getInstance()->Execute($sql_disable);

			$sql_new_carriers = trim($sql_new_carriers, ',');
			$sql_new_carriers .= ';';
			Db::getInstance()->Execute($sql_new_carriers);
		}

		if ($enable_olds_carriers)
		{
			Db::getInstance()->Execute($sql_disable);
			Db::getInstance()->Execute($sql_enable);
		}
	}


	public static function getSeurOrder($id_order)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur_order`
			WHERE `id_order` ='.(int)$id_order
			);
	}

	public static function setSeurOrder($id_order, $bultos, $peso, $imprimido = false)
	{
		$exists = self::getSeurOrder($id_order);

		if ($exists)
			return Db::getInstance()->Execute('
				UPDATE '._DB_PREFIX_.'seur_order
				SET `peso_bultos`='.(float)$peso.', `numero_bultos`='.(int)($bultos.( $imprimido ? ',`imprimido` ="'.$imprimido.'"' : ' ')).'
				WHERE `id_order` ='.(int)$id_order
				);
		else
			return Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'seur_order`
				VALUES ('.(int)$id_order.','.(int)$bultos.','.(float)$peso.',null, 0, 0, "");'
				);
	}

	public static function getModulosPago()
	{
		$modules = Module::getModulesOnDisk();
		$paymentModules = array();

		foreach ($modules as $module)
		{
			if ($module->tab == 'payments_gateways')
			{
				if ($module->id)
				{
					if (!get_class($module) == 'SimpleXMLElement')
						$module->country = array();

					$countries = DB::getInstance()->ExecuteS('
						SELECT `id_country`
						FROM `'._DB_PREFIX_.'module_country`
						WHERE `id_module` = '.(int)$module->id
					);

					foreach ($countries as $country)
						$module->country[] = (int)$country['id_country'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->currency = array();

					$currencies = DB::getInstance()->ExecuteS('
						SELECT `id_currency`
						FROM `'._DB_PREFIX_.'module_currency`
						WHERE `id_module` = "'.(int)$module->id.'"
					');

					foreach ($currencies as $currency)
						$module->currency[] = (int)$currency['id_currency'];

					if (!get_class($module) == 'SimpleXMLElement')

						$module->group = array();
					$groups = DB::getInstance()->ExecuteS('
						SELECT `id_group`
						FROM `'._DB_PREFIX_.'module_group`
						WHERE `id_module` = "'.(int)$module->id.'"
					');

					foreach ($groups as $group)
						$module->group[] = (int)$group['id_group'];

				}
				else
				{
					$module->country = null;
					$module->currency = null;
					$module->group = null;
				}
				$paymentModules[] = $module;
			}
		}

		return $paymentModules;
	}
}
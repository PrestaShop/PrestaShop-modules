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

if (!defined('_COD_REEMBOLSO_SEUR_'))
	define('_COD_REEMBOLSO_SEUR_', 40);

if (!class_exists('SeurLib'))
	require_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

include_once(_PS_MODULE_DIR_.'seur/classes/Range.php');
include_once(_PS_MODULE_DIR_.'seur/classes/User.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Rate.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Town.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Pickup.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Expedition.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Label.php');

class Seur extends CarrierModule
{
	const DEFAULT_PUDO_MOBILE = '666666666';

	private $configured;
	private $stateConfigured;

	public function __construct()
	{
		$this->name = 'seur';
		$this->version = '1.0';
		$this->author = 'www.lineagrafica.es';
		$this->need_instance = 0;
		$this->tab = 'shipping_logistics';

		$this->limited_countries = array('es', 'pt');

		$this->stateConfigured = 0;

		parent::__construct();

		$this->displayName = $this->l('SEUR');
		$this->description = $this->l('Manage your shipments with SEUR. Leader in the Express Shipping, National or International.');

		/** Backward compatibility 1.4 / 1.5 */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			require_once(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
			$this->context = Context::getContext();
			$this->smarty = $this->context->smarty;
		}

		if (!class_exists('SoapClient'))
		{
			$this->active = 1; //only to display warning before install
			$this->warning = $this->l('The SOAP extension is not enabled on your server, please contact to your hosting provider.');
		}
		elseif (!Configuration::get('SEUR_Configured'))
			$this->warning = $this->l('Still has not configured their SEUR module.');
	}

	public function install()
	{
		if (!extension_loaded('soap') || !class_exists('SoapClient'))
		{
			$this->_errors[] = $this->l('SOAP extension should be enabled on your server to use this module.');
			return false;
		}

		/* Check SoapClient extension before cotinues */
		if ($this->active == 1)
			return false;

		if (!parent::install() || !$this->registerHook('adminOrder') ||
			!$this->registerHook('orderDetail') ||
			!$this->registerHook('extraCarrier') || !$this->registerHook('updateCarrier') || !$this->registerHook('displayOrderConfirmation') ||
			!$this->registerHook('header') || !$this->registerHook('backOfficeHeader'))
			return false;
		
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (!$this->registerHook('orderDetailDisplayed'))
				return false;
		}
		else
		{
			if (!$this->registerHook('displayOrderDetail'))
				return false;
		}

		if (!$this->createDatabases() || !$this->createCarriers() || !$this->createAdminTab() || !$this->installSeurCashOnDelivery())
		{
			$this->uninstall();
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		$history_table = SeurLib::getSeurCarriers();

		if (!empty($history_table))
		{
			foreach ($history_table as $history_carrier)
			{
				if (in_array($history_carrier['type'], array('SEN', 'SEP', 'SCN', 'SCE')) == true)
				{
					$carrier = new Carrier((int)$history_carrier['id']);
					if (Validate::isLoadedObject($carrier))
					{
						$carrier->active = 0;
						$carrier->deleted = 1;
						@unlink(_PS_SHIP_IMG_DIR_.(int)$carrier->id.'.jpg');
						$carrier->save();
					}
				}
			}
		}

		if (!$this->uninstallTab() ||
			!$this->setCarriersGroups(0, true) ||
			!$this->deleteTables() ||
			!$this->deleteSettings() ||
			!$this->uninstallSeurCashOnDelivery())
			return false;

		return parent::uninstall();
	}

	private function uninstallSeurCashOnDelivery()
	{
		if ($module = Module::getInstanceByName('seurcashondelivery'))
		{
			if (Module::isInstalled($module->name) && !$module->uninstall())
				return false;

			$module_dir = _PS_MODULE_DIR_.str_replace(array('.', '/', '\\'), array('', '', ''), $module->name);
			$this->recursiveDeleteOnDisk($module_dir);
		}
		
		return true;
	}
	
	private function recursiveDeleteOnDisk($dir)
	{
		if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false)
			return;
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
				if ($object != '.' && $object != '..')
				{
					if (filetype($dir.'/'.$object) == 'dir')
						$this->recursiveDeleteOnDisk($dir.'/'.$object);
					else
						unlink($dir.'/'.$object);
				}
			reset($objects);
			rmdir($dir);
		}
	}

	private function installSeurCashOnDelivery()
	{
		if ($this->moveFiles())
		{
			$cash_on_delivery = Module::GetInstanceByName('seurcashondelivery');
			return $cash_on_delivery->install();
		}

		return false;
	}

	private function moveFiles()
	{
		if (!is_dir(_PS_MODULE_DIR_.'seurcashondelivery'))
		{
			$module_dir = _PS_MODULE_DIR_.str_replace(array('.', '/', '\\'), array('', '', ''), 'seurcashondelivery');
			$this->recursiveDeleteOnDisk($module_dir);
		}
		$dir = _PS_MODULE_DIR_.$this->name.'/install/seurcashondelivery';
		if (!is_dir($dir))
			return false;

		$this->copyDirectory($dir, _PS_MODULE_DIR_.'seurcashondelivery');

		return true;
	}

	private function copyDirectory($source, $target)
	{
		if (!is_dir($source))
		{
			copy($source, $target);
			return null;
		}

		@mkdir($target);
		chmod($target, 0755);
		$d = dir($source);
		$nav_folders = array('.', '..');
		while (false !== ($file_entry = $d->read() ))
		{
			if (in_array($file_entry, $nav_folders))
				continue;

			$s = "$source/$file_entry";
			$t = "$target/$file_entry";
			self::copyDirectory($s, $t);
		}
		$d->close();
	}

	private function deleteSettings()
	{
		$success = true;

		$success &= Configuration::deleteByName('SEUR_URLWS_SP');
		$success &= Configuration::deleteByName('SEUR_URLWS_R');
		$success &= Configuration::deleteByName('SEUR_URLWS_E');
		$success &= Configuration::deleteByName('SEUR_URLWS_A');
		$success &= Configuration::deleteByName('SEUR_URLWS_ET');
		$success &= Configuration::deleteByName('SEUR_URLWS_M');
		$success &= Configuration::deleteByName('SEUR_Configured');
		$success &= Configuration::deleteByName('SEUR_REMCAR_CARGO');
		$success &= Configuration::deleteByName('SEUR_REMCAR_CARGO_MIN');
		$success &= Configuration::deleteByName('SEUR_PRINTER_NAME');
		$success &= Configuration::deleteByName('SEUR_FREE_PRICE');
		$success &= Configuration::deleteByName('SEUR_FREE_WEIGTH');
		$success &= Configuration::deleteByName('SEUR_WS_USERNAME');
		$success &= Configuration::deleteByName('SEUR_WS_PASSWORD');

		return $success;
	}

	private function deleteTables()
	{
		return (Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'seur_merchant`')
			&& Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'seur_order_pos`')
			&& Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'seur_configuration`')
			&& Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'seur_pickup`')
		);
	}

	private function createDatabases()
	{
		/* Create databases */
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'seur_history`');

		$sql = Tools::file_get_contents(dirname(__FILE__).'/sql/install.sql');
		$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array (_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
		$sql = preg_split('/;\s*[\r\n]+/', trim($sql));

		foreach ($sql as $query)
			if (!Db::getInstance()->execute(trim($query)))
				return false;

		return $this->initializeDatabase();
	}

	private function initializeDatabase()
	{
		/* Webservices default configuration */
		Configuration::updateValue('SEUR_URLWS_SP', 'https://ws.seur.com/WSEcatalogoPublicos/servlet/XFireServlet/WSServiciosWebPublicos?wsdl');
		Configuration::updateValue('SEUR_URLWS_R', 'https://ws.seur.com/webseur/services/WSCrearRecogida?wsdl');
		Configuration::updateValue('SEUR_URLWS_E', 'https://ws.seur.com/webseur/services/WSConsultaExpediciones?wsdl');
		Configuration::updateValue('SEUR_URLWS_A', 'https://ws.seur.com/webseur/services/WSConsultaAlbaranes?wsdl');
		Configuration::updateValue('SEUR_URLWS_ET', 'http://cit.seur.com/CIT-war/services/ImprimirECBWebService?wsdl');
		Configuration::updateValue('SEUR_URLWS_M', 'http://cit.seur.com/CIT-war/services/DetalleBultoPDFWebService?wsdl');

		/* Global configuration */
		Configuration::updateValue('SEUR_Configured', 0);
		Configuration::updateValue('SEUR_PRINTER_NAME', 'Generic / Text Only');
		Configuration::updateValue('SEUR_REMCAR_CARGO', 5.5);
		Configuration::updateValue('SEUR_REMCAR_CARGO_MIN', 0);
		Configuration::updateValue('SEUR_WS_USERNAME', 'SEUR.COM USER');
		Configuration::updateValue('SEUR_WS_PASSWORD', 'SEUR.COM PASS');

		
		if (Context::getContext()->shop->isFeatureActive() == true)
		{
			$fields = '(`id_seur_configuration`, `international_orders`, `seur_cod`, `pos`, `notification_advice_radio`, `notification_distribution_radio`, `print_type`, `tarifa`, `pickup`, `advice_checkbox`, `distribution_checkbox`, `id_shop`)';
			$values = '(NULL, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1)';
		}
		else
		{
			$fields = '(`id_seur_configuration`, `international_orders`, `seur_cod`, `pos`, `notification_advice_radio`, `notification_distribution_radio`, `print_type`, `tarifa`, `pickup`, `advice_checkbox`, `distribution_checkbox`)';
			$values = '(NULL, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0)';
		}

		$seur_configuration = 'INSERT INTO `'._DB_PREFIX_.'seur_configuration` '.$fields.' VALUES '.$values.';';

		$seur_merchant = 'INSERT INTO `'._DB_PREFIX_.'seur_merchant`
			(`id_seur_datos`, `user`, `pass`, `cit`, `ccc`, `nif_dni`, `name`, `first_name`, `franchise`, `company_name`, `street_type`, `street_name`, `street_number`, `staircase`, `floor`, `door`, `post_code`, `town`, `state`, `country`, `phone`, `fax`, `email`)
			VALUES (NULL, "USER", "PASS", "CCC", "CIT", NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);';

		return Db::getInstance()->Execute($seur_configuration) && Db::getInstance()->Execute($seur_merchant);
	}

	private function createAdminTab()
	{
		$tab = new Tab();

		foreach (Language::getLanguages() as $language)
			$tab->name[$language['id_lang']] = 'SEUR';

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$tab->class_name = 'AdminSeur';
		else
			$tab->class_name = 'AdminSeur15';

		$tab->module = $this->name;
		$tab->id_parent = (int)Tab::getIdFromClassName('AdminShipping');

		return $tab->add();
	}

	private function uninstallTab()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$id_tab = (int)Tab::getIdFromClassName('AdminSeur');
		else
			$id_tab = (int)Tab::getIdFromClassName('AdminSeur15');

		if ($id_tab)
		{
			$tab = new Tab((int)$id_tab);
			return $tab->delete();
		}

		return true;
	}

	private function createCarriers()
	{
		$history_table = SeurLib::getLastSeurCarriers();

		if (empty($history_table) == false)
			return $this->updateCarriers($history_table);

		$carriers = array(
			array('name' => 'SEUR', 'active' => 1, 'type' => 'SEN', 'delay' =>
				array('es' => 'Envío Urgente', 'fr' => 'Livraison Express', 'default' => 'Express Delivery')
			),
			array('name' => 'SEUR Puntos de Venta', 'active' => 0, 'type' => 'SEP', 'delay' =>
				array('es' => 'Recogida en punto de venta', 'fr' => 'Retrait au point de venta', 'default' => 'Point of sale pickup')
			),
			array('name' => 'SEUR Canarias (M)', 'active' => 1, 'type' => 'SCN', 'delay' =>
				array('es' => 'Envío a Islas Canarias', 'fr' => 'Livraison a Canary Islands', 'default' => 'Delivery to Canary Islands')
			),
			array('name' => 'SEUR Canarias (48/72)', 'active' => 1, 'type' => 'SCE', 'delay' =>
				array('es' => 'Envío Urgente a Islas Canarias', 'fr' => 'Livraison Express a Canary Islands', 'default' => 'Express Delivery to Canary Islands')
			),
		);

		foreach ($carriers as &$values)
		{
			$carrier = new Carrier();
			$carrier->name = $values['name'];
			$carrier->id_tax_rules_group = 1;
			$carrier->need_range = true;
			$carrier->is_module = true;
			$carrier->external_module_name = $this->name;
			$carrier->url = 'http://www.seur.com';
			$carrier->active = $values['active'];

			$languages = Language::getLanguages();
			foreach ($languages as $language)
			{
				if (isset($values['delay'][$language['iso_code']]))
					$carrier->delay[(int)$language['id_lang']] = $values['delay'][$language['iso_code']];
				else
					$carrier->delay[(int)$language['id_lang']] = $values['delay']['default'];
			}

			if ($carrier->save() == false)
				return false;

			$values['id'] = (int)$carrier->id;

			@copy(dirname(__FILE__).'/img/logoSEUR.jpg', _PS_SHIP_IMG_DIR_.(int)$carrier->id.'.jpg');

			$groups = array();
			foreach (Group::getGroups((int)Context::getContext()->language->id) as $group)
				$groups[] = (int)$group['id_group'];

			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				if (!$this->setGroups14((int)$carrier->id, $groups))
					return false;
			}
			else
				if (!$carrier->setGroups($groups))
					return false;

		}
		SeurLib::updateSeurCarriers($carriers);

		return true;
	}

	private function setGroups14($id_carrier, $groups)
	{
		foreach ($groups as $id_group)
			if (!Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'carrier_group`
					(`id_carrier`, `id_group`)
				VALUES
					("'.(int)$id_carrier.'", "'.(int)$id_group.'")
			'))
				return false;
		return true;
	}

	private function updateCarriers($history_table)
	{
		$carriers = array();

		foreach ($history_table as $history_carrier)
		{
			$carrier = new Carrier((int)$history_carrier['id']);

			if (Validate::isLoadedObject($carrier))
			{
				if (strcmp($history_carrier['type'], 'SEP') === 0)
				{
					$carrier->active = 0;
					$carrier->deleted = 0;
				}
				elseif (in_array($history_carrier['type'], array('SEN', 'SCN', 'SCE')) == true)
				{
					$carrier->active = 1;
					$carrier->deleted = 0;
				}
				else
					continue;
	
				$carriers[] = array('id' => $carrier->id, 'type' => $history_carrier['type']);
				$carrier->save();
			}
		}

		return $carriers;
	}

	private function setDefaultWS()
	{
		Configuration::updateValue('SEUR_URLWS_SP', 'https://ws.seur.com/WSEcatalogoPublicos/servlet/XFireServlet/WSServiciosWebPublicos?wsdl');
		Configuration::updateValue('SEUR_URLWS_R', 'https://ws.seur.com/webseur/services/WSCrearRecogida?wsdl');
		Configuration::updateValue('SEUR_URLWS_E', 'https://ws.seur.com/webseur/services/WSConsultaExpediciones?wsdl');
		Configuration::updateValue('SEUR_URLWS_A', 'https://ws.seur.com/webseur/services/WSConsultaAlbaranes?wsdl');
		Configuration::updateValue('SEUR_URLWS_ET', 'http://cit.seur.com/CIT-war/services/ImprimirECBWebService?wsdl');
		Configuration::updateValue('SEUR_URLWS_M', 'http://cit.seur.com/CIT-war/services/DetalleBultoPDFWebService?wsdl');
	}

	private function storeWS()
	{
		Configuration::updateValue('SEUR_URLWS_SP', Tools::getValue('public_services'));
		Configuration::updateValue('SEUR_URLWS_R', Tools::getValue('pickups'));
		Configuration::updateValue('SEUR_URLWS_E', Tools::getValue('deliveries'));
		Configuration::updateValue('SEUR_URLWS_A', Tools::getValue('deliveries_notes'));
		Configuration::updateValue('SEUR_URLWS_ET', Tools::getValue('etiquetas'));
		Configuration::updateValue('SEUR_URLWS_M', Tools::getValue('packing_list'));
	}

	public function getContent()
	{
		if (!extension_loaded('soap') || !class_exists('SoapClient'))
			return $this->displayError($this->l('SOAP extension should be enabled on your server to use this module.'));

		$output = '';
		$this->configured = Configuration::get('SEUR_Configured');

		if (Tools::isSubmit('submitToDefault'))
			$this->setDefaultWS();
		elseif (Tools::isSubmit('submitWebservices'))
			$this->storeWS();
		elseif (Tools::isSubmit('submitConfiguration'))
		{
			$update_seur_carriers = array();
			$delivery = Tools::getValue('seur_cod');

			if ((Module::isInstalled('seurcashondelivery') == false) && $delivery)
			{
				$output .= $this->displayError($this->l('To enable Cash on delivery you must install the module "SEUR Cash on delivery".'));
				$delivery = '0';
			}

			$sql_update_configuration_table = '
				UPDATE `'._DB_PREFIX_.'seur_configuration`
				SET
					`pos` ='.(int)Tools::getValue('pos', 1).',
					`international_orders` ='.(int)Tools::getValue('international_orders').',
					`seur_cod` ='.(int)$delivery.',
					`notification_advice_radio` ='.(int)Tools::getValue('notification_advice_radio').',
					`notification_distribution_radio` ='.(int)Tools::getValue('notification_distribution_radio').',
					`print_type` ='.(int)Tools::getValue('print_type').',
					`pickup` ='.(int)Tools::getValue('pickup').',
					`advice_checkbox` ='.(int)(Tools::getValue('advice_checkbox') == 'on' ? 1 : 0).',
					`distribution_checkbox` ='.(int)(Tools::getValue('distribution_checkbox') == 'on' ? 1 : 0);

			if (Context::getContext()->shop->isFeatureActive())
				$sql_update_configuration_table .= ', `id_shop`  ='.(int)$this->context->shop->id;

			if (Tools::getValue('id_seur_carrier'))
				$update_seur_carriers[] = array('id' => (int)Tools::getValue('id_seur_carrier'), 'type' => 'SEN');

			if (Tools::getValue('id_seur_carrier_canarias_m'))
				$update_seur_carriers[] = array('id' => (int)Tools::getValue('id_seur_carrier_canarias_m'), 'type' => 'SCN');

			if (Tools::getValue('id_seur_carrier_canarias_48'))
				$update_seur_carriers[] = array('id' => (int)Tools::getValue('id_seur_carrier_canarias_48'), 'type' => 'SCE');

			if (Tools::getValue('id_seur_carrier_pos'))
				$update_seur_carriers[] = array('id' => (int)Tools::getValue('id_seur_carrier_pos'), 'type' => 'SEP');

			if (!empty($update_seur_carriers))
				SeurLib::updateSeurCarriers($update_seur_carriers);

			$sql_update_configuration_table .= ' WHERE `id_seur_configuration` = 1';
			$this->configured = 1;

			Configuration::updateValue('SEUR_Configured', 1);
			Configuration::updateValue('SEUR_REMCAR_CARGO', (float)Tools::getValue('contra_porcentaje'));
			Configuration::updateValue('SEUR_REMCAR_CARGO_MIN', (float)Tools::getValue('contra_minimo'));
			Configuration::updateValue('SEUR_PRINTER_NAME', (Tools::getValue('printer_name') ?
				pSQL(Tools::getValue('printer_name')) : 'Generic / Text Only'));
			Configuration::updateValue('SEUR_FREE_WEIGTH', (float)Tools::getValue('peso_gratis'));
			Configuration::updateValue('SEUR_FREE_PRICE', (float)Tools::getValue('precio_gratis'));

			$seur_carriers = array();

			foreach ($update_seur_carriers as $value)
				$seur_carriers[$value['type']] = $value['id'];

			$carrier_seur = new Carrier((int)$seur_carriers['SEN']);
			$carrier_pos = new Carrier((int)$seur_carriers['SEP']);

			if (Tools::getValue('international_orders') &&
				SeurLib::getConfigurationField('international_orders') != Tools::getValue('international_orders') &&
				SeurLib::getConfigurationField('tarifa') &&
				Validate::isLoadedObject($carrier_seur))
			{
				if (Tools::getValue('international_orders') && Tools::getValue('international_orders') == 1)
				{
					$carrier_seur->addZone((int)Zone::getIdByName('Europe'));
					$carrier_seur->save();
				}
				elseif (Tools::getValue('international_orders') == 0)
				{
					$carrier_seur->deleteZone((int)Zone::getIdByName('Europe'));
					$carrier_seur->save();
				}
			}

			if (in_array(Tools::getValue('pos'), array(0, 1)) == true && Validate::isLoadedObject($carrier_pos))
			{
				$carrier_pos->active = (int)Tools::getValue('pos');
				$carrier_pos->save();
			}

			if (!Db::getInstance()->Execute($sql_update_configuration_table))
				$output .= $this->displayError($this->l('Cannot update.'));
			else
				$output .= $this->displayConfirmation($this->l('Configuration updated.'));
		}
		elseif (Tools::isSubmit('submitLogin'))
		{		
			$sqlUpdateDataTable = 'UPDATE `'._DB_PREFIX_.'seur_merchant`
				SET
					`nif_dni` ="'.pSQL(Tools::strtoupper(Tools::getValue('nif_dni'))).'",
					`name` ="'.pSQL(Tools::strtoupper(Tools::getValue('name'))).'",
					`first_name` ="'.pSQL(Tools::strtoupper(Tools::getValue('first_name'))).'",
					`franchise` ="'.(Tools::getValue('franchise') ? pSQL(Tools::getValue('franchise')) : pSQL(Tools::getValue('franchise_cfg'))).'",
					`company_name` ="'.pSQL(Tools::strtoupper(Tools::getValue('company_name'))).'",
					`street_type` ="'.pSQL(Tools::getValue('street_type')).'",
					`street_name` ="'.pSQL(Tools::strtoupper(Tools::getValue('street_name'))).'",
					`street_number` ="'.pSQL(Tools::getValue('street_number')).'",
					'.(Tools::getValue('staircase') ? '`staircase` ="'.pSQL(Tools::strtoupper(Tools::getValue('staircase'))).'",' : ' ').'
					`floor` ="'.pSQL(Tools::getValue('floor')).'",
					`door` ="'.pSQL(Tools::getValue('door')).'",
					`post_code` ="'.(Tools::getValue('post_code') ? pSQL(Tools::getValue('post_code')) : pSQL(Tools::getValue('post_code_cfg'))).'",
					`town` ="'.(Tools::getValue('town') ? pSQL(Tools::strtoupper(Tools::getValue('town'))) : pSQL(Tools::strtoupper(Tools::getValue('town_cfg')))).'",
					`state` ="'.(Tools::getValue('state') ? pSQL(Tools::strtoupper(Tools::getValue('state'))) : pSQL(Tools::strtoupper(Tools::getValue('state_cfg')))).'",
					`country` ="'.(Tools::getValue('country') ? pSQL(Tools::strtoupper(Tools::getValue('country'))) : pSQL(Tools::strtoupper(Tools::getValue('country_cfg')))).'",
					`phone` ="'.(int)Tools::getValue('phone').'",
					`ccc` ="'.(int)Tools::getValue('ccc_cfg').'",
					`cit` ="'.(int)Tools::getValue('ci').'",
					'.(Tools::getValue('fax') ? '`fax` ='.(int)Tools::getValue('fax').',' : ' ').'
					`email` ="'.pSQL(Tools::strtolower(Tools::getValue('email'))).'"';
				
				if(Tools::getValue('user_cfg') && Tools::getValue('pass_cfg'))
					$sqlUpdateDataTable .= ', `USER`="'.pSQL(Tools::strtolower(Tools::getValue('user_cfg'))).'", `PASS`="'.pSQL(Tools::strtolower(Tools::getValue('pass_cfg'))).'" ';
				
				$sqlUpdateDataTable .= "WHERE `id_seur_datos` = 1;";
				
				if(Tools::getValue('user_seurcom') && Tools::getValue('pass_seurcom'))
				{	
					Configuration::updateValue('SEUR_WS_USERNAME', Tools::getValue('user_seurcom'));
					Configuration::updateValue('SEUR_WS_PASSWORD', Tools::getValue('pass_seurcom'));
				}
				

			if (!Db::getInstance()->Execute($sqlUpdateDataTable))
				$output .= $this->displayError($this->l('Database fail.'));
			else
			{
				$output .= $this->displayConfirmation($this->l('Configuration updated.'));
			}
			$this->stateConfigured = 1;
		}
		elseif (Tools::isSubmit('submitWithRanges'))
		{
			if (Range::setRanges())
			{
				$output .= $this->displayConfirmation($this->l('Prices configured correctly. '));
				SeurLib::setConfigurationField('tarifa', 1);
			}
			$this->configured = 1;
			Configuration::updateValue('SEUR_Configured', 1);
			die(Tools::redirectAdmin($this->getModuleLink('AdminModules')));
		}
		elseif (Tools::isSubmit('submitWithoutRanges'))
		{
			$this->configured = 1;
			Configuration::updateValue('SEUR_Configured', 1);
		}

		return $this->displayForm();
	}

	private function displayForm()
	{
		$this->context->smarty->assign(
			array(
				'configured' => (int)Configuration::get('SEUR_Configured'),
				'employee' => $this->context->employee,
				'img_path' => $this->_path.'img/',
				'module_path' => $this->_path,
				'module_local_path' => version_compare(_PS_VERSION_, '1.5', '<') ? _PS_MODULE_DIR_.$this->name.'/' : $this->local_path,
				'ps_version' => _PS_VERSION_,
				'seur_countries' => SeurLib::$seur_countries,
				'street_types' => SeurLib::$street_types,
				'state_configured' => (int)$this->stateConfigured,
				'token' => Tools::getValue('token'),
			)
		);

		if (Configuration::get('SEUR_Configured') == 1)
		{
			$id_lang = (int)$this->context->language->id;

			$this->context->smarty->assign(
				array(
					'carriers' => Carrier::getCarriers((int)$id_lang, false, false, false, null, ALL_CARRIERS),
					'configuration_table' => SeurLib::getConfiguration(),
					'currency' => $this->context->currency,
					'price_configured' => SeurLib::isPricesConfigured(),
					'seur_active_carriers' => SeurLib::getSeurCarriers(true),
					'seur_free_price' => Configuration::get('SEUR_FREE_PRICE'),
					'seur_free_weight' => (float)Configuration::get('SEUR_FREE_WEIGTH'),
					'seur_printer_name' => Configuration::get('SEUR_PRINTER_NAME'),
					'seur_remcar_cargo' => (float)Configuration::get('SEUR_REMCAR_CARGO'),
					'seur_remcar_cargo_min' => (float)Configuration::get('SEUR_REMCAR_CARGO_MIN'),
					'seur_urlws_a' => Configuration::get('SEUR_URLWS_A'),
					'seur_urlws_e' => Configuration::get('SEUR_URLWS_E'),
					'seur_urlws_et' => Configuration::get('SEUR_URLWS_ET'),
					'seur_urlws_m' => Configuration::get('SEUR_URLWS_M'),
					'seur_urlws_r' => Configuration::get('SEUR_URLWS_R'),
					'seur_urlws_sp' => Configuration::get('SEUR_URLWS_SP'),
					'seur_weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
				)
			);
		}
		
		$this->context->smarty->assign(
			array(
				'merchant_data' => SeurLib::getMerchantData(),
				'user_seurcom' => Configuration::get('SEUR_WS_USERNAME'),
				'pass_seurcom' => Configuration::get('SEUR_WS_PASSWORD'),
			)
		);
			
		$id_email_language = User::getIdEmailLanguage();
		
		if (!Configuration::get('SEUR_Configured') && !$id_email_language)
		{
			$this->displayWarning();
			$this->context->smarty->assign(array(
				'email_warning_message' => $this->l('Email template is missing'),
				'module_instance' => $this
			));
		}
		
		return $this->context->smarty->fetch((version_compare(_PS_VERSION_, '1.5', '<') ? _PS_MODULE_DIR_.$this->name.'/' : $this->local_path).'views/templates/admin/template.tpl');
	}
	
	private function displayWarning()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$this->context->smarty->assign('ps_14', true);
		
		if (!version_compare(_PS_VERSION_, '1.6', '<'))
			$this->context->smarty->assign('ps_16', true);
	}
	
	private function getModuleLink($tab)
	{
	# the ps15 way
		if (method_exists($this->context->link, 'getAdminLink'))
			return $this->context->link->getAdminLink($tab).'&configure='.$this->name;

		# the ps14 way
	return 'index.php?tab='.$tab.'&configure='.$this->name.'&token='.Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)$this->context->cookie->id_employee);
	}

	public function hookAdminOrder($params)
	{
		$versionSpecialClass = '';

		if (!file_exists(_PS_MODULE_DIR_.'seur/img/logonew_32.png') && file_exists(_PS_MODULE_DIR_.'seur/img/logonew.png'))
			ImageManager::resize(_PS_MODULE_DIR_.'seur/img/logonew.png', _PS_MODULE_DIR_.'seur/img/logonew_32.png', 32, 32, 'png');

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$versionSpecialClass = 'ver14';
		
		$this->displayWarning();
		
		if (Configuration::get('SEUR_Configured') == 1)
		{
			$cookie = $this->context->cookie;

			$token = Tools::getValue('token');
			$back = Tools::safeOutput($_SERVER['REQUEST_URI']);

			$seur_carriers = SeurLib::getSeurCarriers(false);
			$ids_seur_carriers = array();

			foreach ($seur_carriers as $value)
				$ids_seur_carriers[] = (int)$value['id'];

			$order = new Order((int)$params['id_order']);
			
			$address_saved = DB::getInstance()->getValue('
				SELECT `id_address_delivery`
				FROM `'._DB_PREFIX_.'seur_order`
				WHERE `id_order` = "'.(int)$order->id.'"
			');

			if ($address_saved === '0')
				$this->context->smarty->assign('pickup_point_warning', true);
			
			if (!Validate::isLoadedObject($order))
				return false;
			
			$delivery_price = $order_weigth = 0;
			$products = $order->getProductsDetail();

			foreach ($products as $product)
				$order_weigth += (float)$product['product_weight'] * (float)$product['product_quantity'];

			$order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);

			$customer = new Customer((int)$order->id_customer);
			$address_delivery = new Address((int)$order->id_address_delivery, (int)$cookie->id_lang);
			
			if (!Validate::isLoadedObject($address_delivery))
				return false;
			
			$iso_country = Country::getIsoById((int)$address_delivery->id_country);

			if ($iso_country == 'PT')
			{
				$post_code = explode(' ', $address_delivery->postcode);
				$post_code = $post_code[0];
			}
			else
				$post_code = $address_delivery->postcode;

			$international_orders = SeurLib::getConfigurationField('international_orders');
			$date_calculate = strtotime('-14 day', strtotime(date('Y-m-d')));
			$date_display = date('Y-m-d H:m:i', $date_calculate);
			if (strtotime($order->date_add) > strtotime($date_display) && in_array((int)$order->id_carrier, $ids_seur_carriers))
			{
				if ((!$international_orders && ($iso_country == 'ES' || $iso_country == 'PT' || $iso_country == 'AD')) || $international_orders)
				{
					if (!SeurLib::getSeurOrder((int)$order->id))
						SeurLib::setSeurOrder((int)$order->id, 1, $order_weigth, null);
					elseif (Tools::getValue('numBultos') && Tools::getValue('pesoBultos'))
						SeurLib::setSeurOrder((int)$order->id, (int)Tools::getValue('numBultos'), str_replace(',', '.', Tools::getValue('pesoBultos')), null);

					$order_data = SeurLib::getSeurOrder((int)$order->id);
					$response_post_code = Town::getTowns($post_code);
					$order_weigth = ((float)$order_weigth != $order_data['peso_bultos'] ? (float)$order_data['peso_bultos'] : (float)$order_weigth);

					if (is_object($response_post_code))
					{
						$towns = array();
						$num = (int)$response_post_code->attributes()->NUM[0];

						for ($i = 1; $i <= $num; $i++)
						{
							$name = 'REG'.$i;
							$towns[] = utf8_decode((string)$response_post_code->$name->NOM_POBLACION);
						}
					}

					$name = $address_delivery->firstname.' '.$address_delivery->lastname;
					$direccion = $address_delivery->address1.' '.$address_delivery->address2;
					$newcountry = new Country((int)$address_delivery->id_country, (int)$cookie->id_lang);
					$iso_merchant = SeurLib::getMerchantField('country');

					$rate_data = array(
						'town' => $address_delivery->city,
						'peso' => (float)$order_weigth,
						'post_code' => $post_code,
						'bultos' => $order_data['numero_bultos'],
						'ccc' => SeurLib::getMerchantField('ccc'),
						'franchise' => SeurLib::getMerchantField('franchise'),
						'iso' => $newcountry->iso_code,
						'iso_merchant' => $iso_merchant,
						'id_employee' => $cookie->id_employee,
						'token' => Tools::getAdminTokenLite('AdminOrders'),
						'back' => $back
					);

					$order_messages_str = '';
					$info_adicional_str = $address_delivery->other;
					$order_messages = Message::getMessagesByOrderId((int)$params['id_order']);

					if (is_array($order_messages))
					{
						foreach ($order_messages as $order_messag_tmp)
							$order_messages_str .= "\n".$order_messag_tmp['message'];

						if (substr_count($order_messages_str, "\n") > 5)
							$order_messages_str = str_replace(array("\r", "\n"), ' | ', $order_messages_str);

						if (Tools::strlen($order_messages_str) > 250)
							$order_messages_str = Tools::substr($order_messages_str, 0, 247).'...';

						$order_messages_str = trim($order_messages_str);
					}

					if (!empty($order_messages_str))
						$info_adicional_str = $order_messages_str;

					$label_data = array(
						'pedido' => sprintf('%06d', (int)$order->id),
						'total_bultos' => $order_data['numero_bultos'],
						'total_kilos' => (float)$order_weigth,
						'direccion_consignatario' => $direccion,
						'consignee_town' => $address_delivery->city,
						'codPostal_consignatario' => $post_code,
						'telefono_consignatario' => (!empty($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : $address_delivery->phone),
						'movil' => $address_delivery->phone_mobile,
						'name' => $name,
						'companyia' => (!empty($address_delivery->company) ? $address_delivery->company : ''),
						'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
						'dni' => $address_delivery->dni,
						'info_adicional' => $info_adicional_str,
						'country' => $newcountry->name,
						'iso' => $newcountry->iso_code,
						'iso_merchant' => $iso_merchant,
						'admin_dir' => utf8_encode(_PS_ADMIN_DIR_),
						'id_employee' => $cookie->id_employee,
						'token' => Tools::getAdminTokenLite('AdminOrders'),
						'back' => $back
					);

					if (strcmp($order->module, 'seurcashondelivery') == 0)
					{
						$rate_data['reembolso'] = (float)$order->total_paid;
						$label_data['reembolso'] = (float)$order->total_paid;
					}

					$carrier_pos = SeurLib::getSeurCarrier('SEP');
					$datospos = '';
					if ((int)$order->id_carrier == $carrier_pos['id'])
					{
						$datospos = SeurLib::getOrderPos((int)$order->id_cart);

						if (!empty($datospos))
						{
							$label_data = array(
								'pedido' => sprintf('%06d', (int)$order->id),
								'total_bultos' => $order_data['numero_bultos'],
								'total_kilos' => (float)$order_weigth,
								'direccion_consignatario' => $direccion,
								'consignee_town' => $datospos['city'],
								'codPostal_consignatario' => $datospos['postal_code'],
								'telefono_consignatario' => (!empty($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : $address_delivery->phone),
								'movil' => $address_delivery->phone_mobile,
								'name' => $name,
								'companyia' => $datospos['company'],
								'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
								'dni' => $address_delivery->dni,
								'info_adicional' => $info_adicional_str,
								'country' => $newcountry->name,
								'iso' => $newcountry->iso_code,
								'cod_centro' => $datospos['id_seur_pos'],
								'iso_merchant' => $iso_merchant
							);
							$rate_data['cod_centro'] = $datospos['id_seur_pos'];
						}
					}

					if ($iso_country == 'ES' || $iso_country == 'PT' || $iso_country == 'AD')
					{
						$xml = Rate::getPrivateRate($rate_data);

						if (is_object($xml))
							foreach ($xml as $tarifa)
								$delivery_price += (float)$tarifa->VALOR;
					}

					if (Tools::getValue('submitLabel'))
					{
						if ($this->isPrinted((int)$order->id))
							$success = true;
						else
							$success = Label::createLabels($label_data, 'pdf');

						if ($success === true)
						{
							if (!$this->setAsPrinted((int)$order->id))
								$this->context->smarty->assign('error', $this->l('Could not set printed value for this order'));
							else
								$this->printLabel((int)$order->id, 'pdf');
						}
						else
							$this->context->smarty->assign('error', $success);
					}

					if (Tools::getValue('submitPrint'))
					{
						if ($this->isPrinted((int)$order->id, true))
							$success = true;
						else
							$success = Label::createLabels($label_data, 'zebra');

						if ($success === true)
						{
							if (!$this->setAsPrinted((int)$order->id, true))
								$this->context->smarty->assign('error', $this->l('Could not set printed value for this order'));
							else
								$this->printLabel((int)$order->id, 'txt');
						}
						else
							$this->context->smarty->assign('error', $success);
					}

					$seur_carriers = SeurLib::getSeurCarriers(false);

					$pickup = Pickup::getLastPickup();

					if (!empty($pickup))
					{
						$pickup_date = explode(' ', $pickup['date']);
						$pickup_date = $pickup_date[0];
					}

					$address_error = 0;
					if (!empty($towns) && !in_array(mb_strtoupper($this->replaceAccentedChars($address_delivery->city), 'UTF-8'), $towns))
						$address_error = 1;
					$pickup_s = 0;
					if ($pickup && strtotime(date('Y-m-d')) == strtotime($pickup_date))
						$pickup_s = 1;
					$state = Expedition::getExpeditions(array('reference_number' => sprintf('%06d', (int)$order->id)));
					$is_empty_state = false;
					$xml_s = false;
					if (!empty($state->out))
						$is_empty_state = true;
					else
					{
						$string_xml = htmlspecialchars_decode($state->out);
						$string_xml = str_replace('&', '&amp; ', $string_xml);
						$xml_s = simplexml_load_string($string_xml);
					}
					$rate_data_ajax = Tools::jsonEncode($rate_data);
					$path = '../modules/seur/js/';
					$file = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/seur/files/deliveries_labels/'.sprintf('%06d', (int)$order->id).'.txt';
					$filePath = _PS_MODULE_DIR_.'seur/files/deliveries_labels/'.sprintf('%06d', (int)$order->id).'.txt';
					$label_data['file'] = $file;
					$this->context->smarty->assign(array(
						'path' => $this->_path,
						'request_uri' => $_SERVER['REQUEST_URI'],
						'module_instance' => $this,
						'address_error' => $address_error,
						'address_error_message' => $this->l('Addressess error, please check the customer address.'),
						'pickup_s' => $pickup_s,
						'pickup' => $pickup,
						'isEmptyState' => $is_empty_state,
						'xml' => $xml_s,
						'order_data' => $order_data,
						'iso_country' => $iso_country,
						'order_weigth' => $order_weigth,
						'delivery_price' => $delivery_price,
						'rate_data_ajax' => $rate_data_ajax,
						'js_path' => $path,
						'token' => $token,
						'order' => $order,
						'label_data' => $label_data,
						'fileExists' => file_exists($filePath),
						'file' => $file,
						'datospos' => $datospos,
						'versionSpecialClass' => $versionSpecialClass,
						'configured' => (int)Configuration::get('SEUR_Configured'),
						'printed' => (bool)($this->isPrinted((int)$order->id) || $this->isPrinted((int)$order->id, true))
					));

					return $this->display(__FILE__, 'views/templates/admin/orders.tpl');
				}
			}
		}
		else
		{
			$this->context->smarty->assign(array(
				'configured' => Configuration::get('SEUR_Configured'),
				'path' => $this->_path,
				'configuration_warning_message' => $this->l('Please, first configure your SEUR module as a merchant.')
			));

			return $this->display(__FILE__, 'views/templates/admin/orders.tpl');
		}
	}
	
	private function isPrinted($id_order, $label = false)
	{
		$field = $label ? 'printed_label' : 'printed_pdf';

		return DB::getInstance()->getValue('
			SELECT `'.bqSQL($field).'`
			FROM `'._DB_PREFIX_.'seur_order`
			WHERE `id_order` = "'.(int)$id_order.'"
		');
	}

	private function setAsPrinted($id_order, $label = false)
	{
		$field = $label ? 'printed_label' : 'printed_pdf';

		return DB::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur_order`
			SET `'.bqSQL($field).'` = 1
			WHERE `id_order` = "'.(int)$id_order.'"
		');
	}

	private function replaceAccentedChars($text)
	{
		if (version_compare(_PS_VERSION_, '1.4.5.1', '>='))
			return Tools::replaceAccentedChars($text);

		$patterns = array(
			/* Lowercase */
			/* a  */ '/[\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}\x{0101}\x{0103}\x{0105}\x{0430}]/u',
			/* b  */ '/[\x{0431}]/u',
			/* c  */ '/[\x{00E7}\x{0107}\x{0109}\x{010D}\x{0446}]/u',
			/* d  */ '/[\x{010F}\x{0111}\x{0434}]/u',
			/* e  */ '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}\x{0435}\x{044D}]/u',
			/* f  */ '/[\x{0444}]/u',
			/* g  */ '/[\x{011F}\x{0121}\x{0123}\x{0433}\x{0491}]/u',
			/* h  */ '/[\x{0125}\x{0127}]/u',
			/* i  */ '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}\x{0438}\x{0456}]/u',
			/* j  */ '/[\x{0135}\x{0439}]/u',
			/* k  */ '/[\x{0137}\x{0138}\x{043A}]/u',
			/* l  */ '/[\x{013A}\x{013C}\x{013E}\x{0140}\x{0142}\x{043B}]/u',
			/* m  */ '/[\x{043C}]/u',
			/* n  */ '/[\x{00F1}\x{0144}\x{0146}\x{0148}\x{0149}\x{014B}\x{043D}]/u',
			/* o  */ '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{014D}\x{014F}\x{0151}\x{043E}]/u',
			/* p  */ '/[\x{043F}]/u',
			/* r  */ '/[\x{0155}\x{0157}\x{0159}\x{0440}]/u',
			/* s  */ '/[\x{015B}\x{015D}\x{015F}\x{0161}\x{0441}]/u',
			/* ss */ '/[\x{00DF}]/u',
			/* t  */ '/[\x{0163}\x{0165}\x{0167}\x{0442}]/u',
			/* u  */ '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{0169}\x{016B}\x{016D}\x{016F}\x{0171}\x{0173}\x{0443}]/u',
			/* v  */ '/[\x{0432}]/u',
			/* w  */ '/[\x{0175}]/u',
			/* y  */ '/[\x{00FF}\x{0177}\x{00FD}\x{044B}]/u',
			/* z  */ '/[\x{017A}\x{017C}\x{017E}\x{0437}]/u',
			/* ae */ '/[\x{00E6}]/u',
			/* ch */ '/[\x{0447}]/u',
			/* kh */ '/[\x{0445}]/u',
			/* oe */ '/[\x{0153}]/u',
			/* sh */ '/[\x{0448}]/u',
			/* shh*/ '/[\x{0449}]/u',
			/* ya */ '/[\x{044F}]/u',
			/* ye */ '/[\x{0454}]/u',
			/* yi */ '/[\x{0457}]/u',
			/* yo */ '/[\x{0451}]/u',
			/* yu */ '/[\x{044E}]/u',
			/* zh */ '/[\x{0436}]/u',

			/* Uppercase */
			/* A  */ '/[\x{0100}\x{0102}\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}\x{0410}]/u',
			/* B  */ '/[\x{0411}]]/u',
			/* C  */ '/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}\x{0426}]/u',
			/* D  */ '/[\x{010E}\x{0110}\x{0414}]/u',
			/* E  */ '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}\x{0415}\x{042D}]/u',
			/* F  */ '/[\x{0424}]/u',
			/* G  */ '/[\x{011C}\x{011E}\x{0120}\x{0122}\x{0413}\x{0490}]/u',
			/* H  */ '/[\x{0124}\x{0126}]/u',
			/* I  */ '/[\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}\x{0418}\x{0406}]/u',
			/* J  */ '/[\x{0134}\x{0419}]/u',
			/* K  */ '/[\x{0136}\x{041A}]/u',
			/* L  */ '/[\x{0139}\x{013B}\x{013D}\x{0139}\x{0141}\x{041B}]/u',
			/* M  */ '/[\x{041C}]/u',
			/* N  */ '/[\x{00D1}\x{0143}\x{0145}\x{0147}\x{014A}\x{041D}]/u',
			/* O  */ '/[\x{00D3}\x{014C}\x{014E}\x{0150}\x{041E}]/u',
			/* P  */ '/[\x{041F}]/u',
			/* R  */ '/[\x{0154}\x{0156}\x{0158}\x{0420}]/u',
			/* S  */ '/[\x{015A}\x{015C}\x{015E}\x{0160}\x{0421}]/u',
			/* T  */ '/[\x{0162}\x{0164}\x{0166}\x{0422}]/u',
			/* U  */ '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{0168}\x{016A}\x{016C}\x{016E}\x{0170}\x{0172}\x{0423}]/u',
			/* V  */ '/[\x{0412}]/u',
			/* W  */ '/[\x{0174}]/u',
			/* Y  */ '/[\x{0176}\x{042B}]/u',
			/* Z  */ '/[\x{0179}\x{017B}\x{017D}\x{0417}]/u',
			/* AE */ '/[\x{00C6}]/u',
			/* CH */ '/[\x{0427}]/u',
			/* KH */ '/[\x{0425}]/u',
			/* OE */ '/[\x{0152}]/u',
			/* SH */ '/[\x{0428}]/u',
			/* SHH*/ '/[\x{0429}]/u',
			/* YA */ '/[\x{042F}]/u',
			/* YE */ '/[\x{0404}]/u',
			/* YI */ '/[\x{0407}]/u',
			/* YO */ '/[\x{0401}]/u',
			/* YU */ '/[\x{042E}]/u',
			/* ZH */ '/[\x{0416}]/u');

			// ö to oe
			// å to aa
			// ä to ae

		$replacements = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 'ss', 't', 'u', 'v', 'w', 'y', 'z', 'ae', 'ch',
			'kh', 'oe', 'sh', 'shh', 'ya', 'ye', 'yi', 'yo', 'yu', 'zh', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
			'P', 'R', 'S', 'T', 'U', 'V', 'W', 'Y', 'Z', 'AE', 'CH', 'KH', 'OE', 'SH', 'SHH', 'YA', 'YE', 'YI', 'YO', 'YU', 'ZH'
		);

		return preg_replace($patterns, $replacements, $text);
	}

	public function hookDisplayOrderDetail($params)
	{
		return $this->hookOrderDetailDisplayed($params);
	}

	public function hookOrderDetailDisplayed($params)
	{
		if (Configuration::get('SEUR_Configured') == 1)
		{
			$seur_carriers = SeurLib::getSeurCarriers(false);
			$ids_seur_carriers = array();

			foreach ($seur_carriers as $value)
				$ids_seur_carriers[] = (int)$value['id'];

			$order = new Order((int)$params['order']->id);
			
			if (!Validate::isLoadedObject($order))
				return false;

			if (in_array((int)$order->id_carrier, $ids_seur_carriers))
			{
				$referencia = sprintf('%06d', (int)$order->id);

				$datos = array();
				$datos['reference_number'] = $referencia;
				$response = Expedition::getExpeditions($datos);

				$string_xml = htmlspecialchars_decode($response->out);
				$xml = simplexml_load_string($string_xml);
				$seur_order_state = $xml->EXPEDICION->DESCRIPCION_PARA_CLIENTE;

				$this->context->smarty->assign(
					array(
						'logo' => $this->_path.'img/logo.png',
						'reference' => $datos['reference_number'],
						'delivery' => (string)$xml->EXPEDICION->EXPEDICION_NUM,
						'seur_order_state' => (!empty($seur_order_state) ? (string)$seur_order_state : $this->l('Sin estado')),
						'date' => (string)$xml->EXPEDICION->FECHA_CAPTURA
					)
				);
				return $this->display(__FILE__, 'views/templates/hook/orderDetail.tpl');
			}
		}
	}

	public function hookHeader($params)
	{
		if (Configuration::get('SEUR_Configured') == 1)
		{
			$seur_carrier_pos = SeurLib::getSeurCarrier('SEP');
			$pos_is_enabled = (int)SeurLib::getConfigurationField('pos');
			
			$this->context->smarty->assign(
				array(
					'id_seur_pos' => (int)$seur_carrier_pos['id'],
					'seur_dir' => $this->_path,
					'ps_version' => _PS_VERSION_
				)
			);
			
			$httpsStr = 0;
			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				$page = (Tools::getValue('controller') ? Tools::getValue('controller') : null);
				if (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode())
					$httpsStr = 1;
			}
			else
			{
				$page = explode('/', $_SERVER['SCRIPT_NAME']);
				$page = end($page);
				if (Configuration::get('PS_SSL_ENABLED') || (isset($_SERVER['HTTPS']) && (Tools::strtolower($_SERVER['HTTPS']) == 'on')))
					$httpsStr = 1;
			}
			
			if ($pos_is_enabled && ($page == 'order-opc.php' || $page == 'order.php' || $page == 'orderopc' || $page == 'order'))
			{
				$this->context->controller->addCSS($this->_path.'css/seurGMap.css');
				$this->context->controller->addJS($this->_path.'js/seurGMap.js');
				$js_url = 'http'.($httpsStr == 1 ? 's' : '').'://maps.google.com/maps/api/js?sensor=false';
				$this->context->controller->addJS($js_url);
				
				$current_customer_addresses_ids = array();
				foreach ($this->context->customer->getAddresses((int)$this->context->language->id) as $address)
					$current_customer_addresses_ids[] = (int)$address['id_address'];
				$this->context->smarty->assign('customer_addresses_ids', $current_customer_addresses_ids);
				
				return $this->display(__FILE__, 'views/templates/hook/header.tpl');
			}
		}
	}

	public function hookUpdateCarrier($params)
	{
		$seurCarriers = SeurLib::getSeurCarriers();

		if (is_array($seurCarriers) && ($params['id_carrier'] > 0) && is_object($params['carrier']) && ($params['carrier']->id > 0))
		{
			$newCarriesTmp = array();

			foreach ($seurCarriers as $carrierRecord)
				if ((int)$carrierRecord['id'] === (int)$params['id_carrier'])
					$newCarriesTmp[] = array('id' => $params['carrier']->id, 'type' => $carrierRecord['type']);

			if (count($newCarriesTmp) > 0)
				SeurLib::updateSeurCarriers($newCarriesTmp);
		}
	}

	public function hookExtraCarrier()
	{
		if (Configuration::get('SEUR_Configured') == 1)
		{
			$process_type = Configuration::get('PS_ORDER_PROCESS_TYPE');
			$seur_carrier_pos = SeurLib::getSeurCarrier('SEP');
			$seur_carriers = SeurLib::getSeurCarriers(true);
			$pos_is_enabled = SeurLib::getConfigurationField('pos');
			$seur_carriers_without_pos = '';

			foreach ($seur_carriers as $seur_carrier)
				if (($seur_carrier['id'] != $seur_carrier_pos['id']))
					$seur_carriers_without_pos .= (int)$seur_carrier['id'].',';

			$seur_carriers_without_pos = trim($seur_carriers_without_pos, ',');

			if ($process_type == '0')
				$this->context->smarty->assign('id_address', $this->context->cart->id_address_delivery);

			$this->context->smarty->assign(
				array(
					'posEnabled' => $pos_is_enabled,
					'id_seur_pos' => (int)$seur_carrier_pos['id'],
					'seur_resto' => $seur_carriers_without_pos,
					'src' => $this->_path.'img/unknown.gif',
					'ps_version' => version_compare(_PS_VERSION_, '1.5', '<') ? 'ps4' : 'ps5'
				)
			);

			return $this->display(__FILE__, 'views/templates/hook/seur.tpl');
		}
	}

	private function setCarriersGroups($id_carrier, $delete = false)
	{
		if ($id_carrier == 0)
			return Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'carrier_group`
				WHERE `id_carrier` IN (
					SELECT `id_seur_carrier`
					FROM '._DB_PREFIX_.'seur_history
				)
			');
		elseif ($delete)
			return Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'carrier_group`
				WHERE id_carrier = "'.(int)$id_carrier.'"
			');
		else
		{
			$group = Db::getInstance()->getRow('
				SELECT `id_group`
				FROM `'._DB_PREFIX_.'group`
				WHERE id_group = 1
			');
			return Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'carrier_group`
					(`id_group`, `id_carrier`)
				VALUES("'.(int)$group['id_group'].'", "'.(int)$id_carrier.'")
			');
		}
	}

	public function getOrderShippingCost($params, $shipping_cost)
	{
		return $shipping_cost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return null;
	}

	private function printLabel($id_order, $type)
	{
		$name = sprintf('%06d', (int)$id_order);
		$directory = _PS_MODULE_DIR_.'seur/files/deliveries_labels/';

		if ($type == 'txt')
		{
			if (file_exists($directory.$name.'.txt') && ($fp = Tools::file_get_contents($directory.$name.'.txt')))
			{
				ob_end_clean();
				header('Content-type: text/plain');
				header('Content-Disposition: attachment; filename='.$name.'.txt');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');

				echo $fp;
				exit;
			}
		}
		elseif ($type == 'pdf')
		{
			if (file_exists($directory.$name.'.pdf') && ($fp = Tools::file_get_contents($directory.$name.'.pdf')))
			{
				ob_end_clean();
				header('Content-type: application/pdf');
				header('Content-Disposition: inline; filename='.$name.'.pdf');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');

				echo $fp;
				exit;
			}
		}
		
		$this->context->smarty->assign('error', $this->l('Document was already printed, but is missing in module directory'));
	}
	
	public function hookBackOfficeHeader($params)
	{
		$tab = version_compare(_PS_VERSION_, '1.5', '<') ? Tools::strtolower(Tools::getValue('tab')) : Tools::strtolower(Tools::getValue('controller'));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->context->smarty->assign('tab', $tab);
			return $this->display(__FILE__, 'views/templates/hook/backoffice_header.tpl');
		}
		else
		{
			$this->context->controller->addJQuery();
			$this->context->controller->addJqueryPlugin('fancybox');

			$this->context->controller->addCSS($this->_path.'css/seur.css');

			if ($tab == 'AdminSeur15' || $tab == 'adminseur15')
				$this->context->controller->addJS($this->_path.'js/seurToolsAdmin.js');

			if ($tab == 'AdminOrders' || $tab == 'adminorders')
			{
				$this->context->controller->addJS($this->_path.'js/seurToolsOrder.js');
				$this->context->controller->addJS($this->_path.'js/html2canvas.js');
				$this->context->controller->addJS($this->_path.'js/jquery.plugin.html2canvas.js');
			}

			if (Tools::getValue('configure') == 'seur' && $tab == 'adminmodules')
				$this->context->controller->addJS($this->_path.'js/seurToolsConfig.js');
		}
	}
	
	public function hookDisplayOrderConfirmation($params)
	{
		$carrier_pos = SeurLib::getSeurCarrier('SEP');

		if ($carrier_pos['id'] != (int)$params['objOrder']->id_carrier) //check if COD carrier with pickup points
			return '';

		if (Db::getInstance()->getValue('
			SELECT `id_address_delivery`
			FROM `'._DB_PREFIX_.'seur_order`
			WHERE `id_order` = "'.(int)$params['objOrder']->id.'"
		'))
			return;

		$customer_address = new Address((int)$params['objOrder']->id_address_delivery);
		$pickup_point_info = SeurLib::getOrderPos((int)$params['objOrder']->id_cart);
		$pickup_point_address = new Address();
		$pickup_point_address->id_country = $customer_address->id_country;
		$pickup_point_address->id_state = $customer_address->id_state;
		$pickup_point_address->alias = $customer_address->alias;
		$pickup_point_address->company = urldecode($pickup_point_info['company']);
		$pickup_point_address->lastname = $customer_address->lastname;
		$pickup_point_address->firstname = $customer_address->firstname;
		$pickup_point_address->address1 = urldecode($pickup_point_info['address']);
		$pickup_point_address->postcode = urldecode($pickup_point_info['postal_code']);
		$pickup_point_address->city = urldecode($pickup_point_info['city']);
		$pickup_point_address->phone = $customer_address->phone_mobile ? $customer_address->phone_mobile : self::DEFAULT_PUDO_MOBILE;

		$order = new Order((int)$params['objOrder']->id);

		$products = $order->getProductsDetail();
		$order_weigth = 0;

		foreach ($products as $product)
			$order_weigth += (float)$product['product_weight'] * (float)$product['product_quantity'];

		$order_weigth = $order_weigth > 1 ? $order_weigth : 1;
		if ($pickup_point_address->save())
		{
			$order->id_address_delivery = (int)$pickup_point_address->id;
			
			if ($order->save())
				Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'seur_order`
					VALUES ("'.(int)$order->id.'", "1", "'.(float)$order_weigth.'", null, "0", "0", "'.(int)$pickup_point_address->id.'");'
				);
			else
				Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'seur_order`
					VALUES ("'.(int)$order->id.'", "1", "'.(float)$order_weigth.'", null, "0", "0", "");'
				);
		}
		else
			Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'seur_order`
				VALUES ("'.(int)$order->id.'", "1", "'.(float)$order_weigth.'", null, "0", "0", "");'
			);
	}
}

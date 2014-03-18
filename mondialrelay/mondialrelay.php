<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;
	
require_once(_PS_MODULE_DIR_.'mondialrelay/classes/MRTools.php');

class MondialRelay extends Module
{
	const INSTALL_SQL_FILE = 'sql/mrInstall.sql';

	private $_postErrors;

	public static $modulePath = '';
	public static $moduleURL = '';
	static public $MRFrontToken = '';
	static public $MRBackToken = '';

	// Added for 1.3 compatibility
	const ONLY_PRODUCTS = 1;
	const ONLY_DISCOUNTS = 2;
	const BOTH = 3;
	const BOTH_WITHOUT_SHIPPING = 4;
	const ONLY_SHIPPING = 5;
	const ONLY_WRAPPING = 6;
	const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;

	// SQL FILTER ORDER
	const NO_FILTER = 0;
	const WITHOUT_HOME_DELIVERY = 1;

	// Contains the details of the current shop used
	public $account_shop = array(
		'MR_ENSEIGNE_WEBSERVICE' => '',
		'MR_CODE_MARQUE' => '',
		'MR_KEY_WEBSERVICE' => '',
		'MR_LANGUAGE' => '',
		'MR_WEIGHT_COEFFICIENT' => '',
		'MR_ORDER_STATE' => 3,
		'id_shop' => 1
	);

	public $upgrade_detail = array();

	public function __construct()
	{
		$this->name = 'mondialrelay';
		$this->tab = 'shipping_logistics';
		$this->version = '1.8.10';
		$this->installed_version = '';
		$this->module_key = '366584e511d311cfaa899fc2d9ec1bd0';
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('Mondial Relay');
		$this->description = $this->l('Deliver in Relay points');

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.'/mondialrelay/backward_compatibility/backward.php');

		self::initModuleAccess();

		// Call everytime to prevent the change of the module by a recent one
		$this->_updateProcess();
		$this->initAccount();

		// Check if it's a mondialrelay ajax query
		$this->checkAJAX();
	}

	public function install()
	{
		if (!parent::install())
			return false;

		if (!$this->registerHookByVersion())
			return false;

		if ((!file_exists(MondialRelay::$modulePath.MondialRelay::INSTALL_SQL_FILE)) ||
			(!$sql = file_get_contents(MondialRelay::$modulePath.MondialRelay::INSTALL_SQL_FILE)))
			return false;

		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach($sql AS $k => $query)
			if (!empty($query))
				Db::getInstance()->execute(trim($query));

		$result = Db::getInstance()->getRow('
			SELECT id_tab
			FROM `' . _DB_PREFIX_ . 'tab`
			WHERE class_name="AdminMondialRelay"');

		if (!$result)
		{	
			$tab = new Tab();
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
				$tab->name[$language['id_lang']] = 'Mondial Relay';
			$tab->class_name = 'AdminMondialRelay';
			$tab->module = 'mondialrelay';
			$tab->id_parent = Tab::getIdFromClassName('AdminOrders');

			if (!$tab->add())
				return false;

			if (is_dir(_PS_MODULE_DIR_.'mondialrelay/'))
				@copy(_PS_MODULE_DIR_.'mondialrelay/AdminMondialRelay.gif', _PS_IMG_DIR_.'/AdminMondialRelay.gif');
		}

		// If module isn't installed, set default value
		if (!Configuration::get('MONDIAL_RELAY'))
		{
			Configuration::updateValue('MONDIAL_RELAY', $this->version);
			Configuration::updateValue('MONDIAL_RELAY_SECURE_KEY', md5(time().rand(0,10)));
		}
		else
		{
			$query = 'UPDATE `'._DB_PREFIX_.'carrier` c, `'._DB_PREFIX_.'mr_method` m
					SET c.`deleted` = 0
					WHERE c.`id_carrier` = m.`id_carrier`
					AND m.`is_deleted` = 0';

			// Reactive transport if database wasn't remove at the last uninstall
			Db::getInstance()->execute($query);
		}
		return true;
	}

	/*
	** Return the token depend of the type
	*/
	static public function getToken($type = 'front')
	{
		return ($type == 'front') ? MondialRelay::$MRFrontToken : (($type == 'back') ?
			MondialRelay::$MRBackToken : NULL);
	}

	/*
	** Register hook depending of the Prestashop version used
	*/
	private function registerHookByVersion()
	{
		if (_PS_VERSION_ >= '1.3' &&
			(!$this->registerHook('extraCarrier') ||
				!$this->registerHook('updateCarrier') ||
				!$this->registerHook('newOrder') ||
				!$this->registerHook('BackOfficeHeader') ||
				!$this->registerHook('header')))
			return false;

		if (_PS_VERSION_ >= '1.4' &&
			(!$this->registerHook('processCarrier') ||
				!$this->registerHook('orderDetail') ||
				!$this->registerHook('orderDetailDisplayed') ||
				!$this->registerHook('paymentTop')))
			return false;
		return true;
	}

	public function uninstallCommonData()
	{
		// Tab uninstall
		if (($id_tab = Tab::getIdFromClassName('AdminMondialRelay')))
		{
			$tab = new Tab($id_tab);
			$tab->delete();
		}

		if (!Db::getInstance()->execute('
					UPDATE '._DB_PREFIX_.'carrier c, '._DB_PREFIX_.'mr_method m
					SET c.`deleted` = 1
					WHERE c.`id_carrier` = m.`id_carrier`'))
			return false;

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		// Uninstall data that doesn't need to be keep
		if (!$this->uninstallCommonData())
			return false;

		if (Tools::getValue('keepDatabase'))
			return true;

		Configuration::deleteByName('MR_ACCOUNT_DETAIL');

		// Drop databases
		if (!Db::getInstance()->execute('
					DROP TABLE
					'._DB_PREFIX_ .'mr_history,
					'._DB_PREFIX_ .'mr_method,
					'._DB_PREFIX_ .'mr_selected,
					'._DB_PREFIX_ .'mr_method_shop'))
		{
			// If drop failed, try to turn off the carriers
			Db::getInstance()->execute('
					UPDATE '._DB_PREFIX_.'carrier c, '._DB_PREFIX_.'mr_method m
					SET c.`deleted` = 1
					WHERE c.`id_carrier` = m.`id_carrier`');
			return false;
		}
		return true;
	}

	/**
	 * Launch upgrade process for 1.3 and 1.4
	 */
	public function runUpgrade()
	{
		// List of upgraded version existing
		$files_version = array('1.8.0', '1.8.3');

		$upgrade_path = dirname(__FILE__).'/upgrade/';
		if (_PS_VERSION_ < '1.5')
			foreach ($files_version as $version)
			{
				$file = $upgrade_path.'install-'.$version.'.php';
				if ($this->installed_version < $version && file_exists($file))
				{
					include_once($file);
					call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
				}
			}
	}

	/*
	** UpdateProcess if merchant update the module without a
	** normal installation
	*/
	private function _updateProcess()
	{
		if (Module::isInstalled('mondialrelay') &&
			(($this->installed_version = Configuration::get('MONDIAL_RELAY')) ||
				$this->installed_version = Configuration::get('MONDIAL_RELAY_1_4'))
			&& $this->installed_version < $this->version)
			$this->runUpgrade();
	}

	/*
	** Get the content to ask for a backup of the database
	*/
	private function askForBackup($href)
	{
		return 'return PS_MRObject.uninstall(\''.$href.'\');';
	}

	/*
	** OnClick for input fields under the module list fields action
	*/
	public function onclickOption($type, $href = false)
	{
		$content = '';

		switch($type)
		{
			case 'desactive':
				break;
			case 'reset':
				break;
			case 'delete':
				break;
			case 'uninstall':
				$content = $this->askForBackup($href);
				break;
			default:
		}
		return $content;
	}

	/**
	 * Init the account_shop variable with the account detail for this shop
	 */
	public function initAccount()
	{
		// Get default value
		$id_order_state = $this->account_shop['MR_ORDER_STATE'];
		
		if (($account_shop_stored = MondialRelay::getAccountDetail()))
			$this->account_shop = $account_shop_stored;
			
		// Normally, this can't happen... 
		if (empty($this->account_shop['MR_ORDER_STATE']))
			$this->account_shop['MR_ORDER_STATE'] = $id_order_state;
	}

	/*
	** Init the access directory module for URL and file system
	** Allow a compatibility for Presta < 1.4
	*/
	public static function initModuleAccess()
	{
		MondialRelay::$modulePath =	_PS_MODULE_DIR_. 'mondialrelay/';
		MondialRelay::$MRFrontToken = sha1('mr'._COOKIE_KEY_.'Front');
		MondialRelay::$MRBackToken = sha1('mr'._COOKIE_KEY_.'Back');
		MondialRelay::$moduleURL = __PS_BASE_URI__.'modules/mondialrelay/';
	}

	public function fetchTemplate($path, $name)
	{
		if (_PS_VERSION_ < '1.4')
			$this->context->smarty->currentTemplate = $name;

		return $this->context->smarty->fetch(dirname(__FILE__).$path.$name.'.tpl');
	}

	public function hookNewOrder($params)
	{
		if (!MondialRelay::isMondialRelayCarrier($params['cart']->id_carrier))
			return;
		
		DB::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'mr_selected`
			SET `id_order` = '.(int)$params['order']->id.'
			WHERE `id_cart` = '.(int)$params['cart']->id);
	}

	public function hookBackOfficeHeader()
	{
		$overload_current_jquery = false;
		if (Tools::getValue('tab') == 'AdminMondialRelay')
			$overload_current_jquery = true;

		$this->context->smarty->assign(array(
			'MR_token' => MondialRelay::$MRBackToken,
			'MR_jQuery_overload_type' => true,
			'new_base_dir' => MondialRelay::$moduleURL,
			'MR_local_path' => MondialRelay::$modulePath,
			'MR_overload_current_jquery' => $overload_current_jquery,
			'MR_account_set' => MondialRelay::isAccountSet()
		));
		return $this->fetchTemplate('/tpl/', 'bo-header');
	}

	public function hookOrderDetail($params)
	{
		if (!Mondialrelay::isMondialRelayCarrier($params['order']->id_carrier))
			return ;

		$order = $params['order'];

		if (!empty($order->shipping_number))
			$this->context->smarty->assign('followup', $this->get_followup($order->shipping_number));
	}

	public function hookOrderDetailDisplayed($params)
	{
		if (!Mondialrelay::isMondialRelayCarrier($params['order']->id_carrier))
			return '';

		$res = Db::getInstance()->getRow('
			SELECT s.`MR_Selected_LgAdr1`, s.`MR_Selected_LgAdr2`, s.`MR_Selected_LgAdr3`, s.`MR_Selected_LgAdr4`,
			 s.`MR_Selected_CP`, s.`MR_Selected_Ville`, s.`MR_Selected_Pays`, s.`MR_Selected_Num`, s.`url_suivi`
			FROM `'._DB_PREFIX_.'mr_selected` s
			WHERE s.`id_cart` = '.$params['order']->id_cart);

		if ((!$res) OR ($res['MR_Selected_Num'] == 'LD1') OR ($res['MR_Selected_Num'] == 'LDS'))
			return '';

		$this->context->smarty->assign(
			array(
				'mr_addr' => $res['MR_Selected_LgAdr1'].
					($res['MR_Selected_LgAdr1'] ? ' - ' : '').$res['MR_Selected_LgAdr2'].
					($res['MR_Selected_LgAdr2'] ? ' - ' : '').$res['MR_Selected_LgAdr3'].
					($res['MR_Selected_LgAdr3'] ? ' - ' : '').$res['MR_Selected_LgAdr4'].
					($res['MR_Selected_LgAdr4'] ? ' - ' : '').$res['MR_Selected_CP'].' '.
					$res['MR_Selected_Ville'].' - '.$res['MR_Selected_Pays'],
				'mr_url' => $res['url_suivi']));

		return $this->fetchTemplate('/tpl/', 'order_detail');
	}

	/*
	** Update the carrier id to use the new one if changed
	*/
	public function hookupdateCarrier($params)
	{
		if ($params['id_carrier'] != $params['carrier']->id)
		{
			// Get the old id_mr_method
			$id_mr_method = Db::getInstance()->getValue('
				SELECT id_mr_method FROM `'._DB_PREFIX_.'mr_method`
				WHERE id_carrier='. (int)$params['id_carrier']);

			// Insert new entry keeping the last one linked to the id_carrier
			$query = '
				INSERT INTO `'._DB_PREFIX_.'mr_method`
				(name, country_list, col_mode, dlv_mode, insurance, id_carrier)
				(
					SELECT
						"'.pSQL($params['carrier']->name).'",
						country_list,
						col_mode,
						dlv_mode,
						insurance,
						'.(int)$params['carrier']->id.'
					FROM `'._DB_PREFIX_.'mr_method`
					WHERE id_carrier ='.(int)$params['id_carrier'].')';
			Db::getInstance()->execute($query);

			// Do the same process for the multishop table
			$query = '
				INSERT INTO `'._DB_PREFIX_.'mr_method_shop`
				(id_mr_method, id_shop)
				(
					SELECT
						'.(int)Db::getInstance()->INSERT_ID().',
						id_shop
					FROM `'._DB_PREFIX_.'mr_method_shop`
					WHERE id_mr_method ='.(int)$id_mr_method.')';
			Db::getInstance()->execute($query);

			// Set the last mr_method to delete
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'mr_method` SET `is_deleted` = 1
					WHERE `id_mr_method` = '.(int)$id_mr_method);
		}
	}

	/**
	 * Get a carrier list liable to the module
	 *
	 * @return array
	 */
	public function _getCarriers()
	{
		// Query don't use the external_module_name to keep the
		// 1.3 compatibility
		$query = '
			SELECT c.id_carrier, c.range_behavior, m.id_mr_method,
				m.dlv_mode, cl.delay
			FROM `'._DB_PREFIX_.'mr_method` m
			LEFT JOIN `'._DB_PREFIX_.'carrier` c
			ON c.`id_carrier` = m.`id_carrier`
			LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl
			ON c.`id_carrier` = cl.`id_carrier`
			LEFT JOIN `'._DB_PREFIX_.'mr_method_shop` ms
			ON m.`id_mr_method` = ms.`id_mr_method`
			WHERE  c.`deleted` = 0
			AND ms.`id_shop` = '.$this->account_shop['id_shop'] .'
			AND cl.id_lang = '.$this->context->language->id .'
			AND c.`active` = 1
			AND m.`is_deleted` = 0';

		$carriers = Db::getInstance()->executeS($query);

		if (!is_array($carriers))
			$carriers = array();
		return $carriers;
	}

	/**
	 * Get a specific method entry detail by a defined id_carrier
	 *
	 * @static
	 * @param $id_carrier
	 * @return array
	 */
	public static function getMethodByIdCarrier($id_carrier)
	{
		return Db::getInstance()->getRow('
			SELECT * FROM `'._DB_PREFIX_.'mr_method` m
			WHERE m.`id_carrier` = '.(int)$id_carrier);
	}

	/*
	** Added to be used properly with OPC 
	*/
	public function hookHeader($params)
	{
		if (!($file = basename(Tools::getValue('controller'))))
			$file = str_replace('.php', '', basename($_SERVER['SCRIPT_NAME']));

		if (in_array($file, array('order-opc', 'order', 'orderopc')))
		{
			$this->context->smarty->assign(
				array(
					'one_page_checkout' => (Configuration::get('PS_ORDER_PROCESS_TYPE') ? Configuration::get('PS_ORDER_PROCESS_TYPE') : 0),
					'warning_message' => $this->l('You need to select a dropp-off point before going to the next step.'),
					'new_base_dir' => MondialRelay::$moduleURL,
					'MR_local_path' => MondialRelay::$modulePath,
					'MRToken' => MondialRelay::$MRFrontToken,
					'MR_overload_current_jquery' => false
				)
			);
			return $this->fetchTemplate('/tpl/', 'header');
		}
		return '';
	}

	public function hookExtraCarrier($params)
	{
		// TODO : Makes it work with multi-shipping
		if (!MondialRelay::isAccountSet())
			return '';

		$id_carrier = false;
		$preSelectedRelay = $this->getRelayPointSelected($this->context->cart->id);
		$carriersList = MondialRelay::_getCarriers();
		$address = new Address($this->context->cart->id_address_delivery);
		$id_zone = Address::getZoneById((int)($address->id));

		// Check if the defined carrier are ok
		foreach ($carriersList as $k => $row)
		{
			// For now works only with single shipping (>= 1.5 compatibility)
			if (method_exists($this->context->cart, 'carrierIsSelected'))
				if ($this->context->cart->carrierIsSelected($row['id_carrier'], $params['address']->id))
					$id_carrier = $row['id_carrier'];

			// Temporary carrier for some test
			$carrier = new Carrier((int)($row['id_carrier']));
			if ((Configuration::get('PS_SHIPPING_METHOD') && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false) ||
				(!Configuration::get('PS_SHIPPING_METHOD') && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
				unset($carriersList[$k]);
			else if ($row['range_behavior'])
			{
				// Get id zone
				$id_zone = (isset($this->context->cart->id_address_delivery) && $this->context->cart->id_address_delivery) ?
					Address::getZoneById((int)$this->context->cart->id_address_delivery) :
					(int)$this->context->country->id_zone;

				if ((Configuration::get('PS_SHIPPING_METHOD') && (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->context->cart->getTotalWeight(), $id_zone))) ||
					(!Configuration::get('PS_SHIPPING_METHOD') &&
						(!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $this->context->cart->getOrderTotal(true, MondialRelay::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency) ||
							!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $this->context->cart->getOrderTotal(true, MondialRelay::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency))))
					unset($carriersList[$k]);
			}
		}

		$carrier = NULL;
		
		if (_PS_VERSION_ >= '1.5')
				$id_carrier = (int)$this->context->cart->id_carrier;

		if ($id_carrier && ($method = MondialRelay::getMethodByIdCarrier($id_carrier)))
		{
			$carrier = new Carrier((int)$id_carrier);
			// Add dynamically a new field
			$carrier->id_mr_method = $method['id_mr_method'];
			$carrier->mr_dlv_mode = $method['dlv_mode'];
		}

		$this->context->smarty->assign(
			array(
				'MR_Data'=> MRTools::jsonEncode(
					array(
						'carrier_list' => $carriersList,
						'carrier' => $carrier,
						'PS_VERSION' => _PS_VERSION_,
						'pre_selected_relay' => isset($preSelectedRelay['MR_selected_num']) ? $preSelectedRelay['MR_selected_num'] : 0,
					)
				)
			)
		);

		return $this->fetchTemplate('/tpl/', 'checkout_process');
	}

	/**
	 * Return the detailed account
	 *
	 * @static
	 * @return mixed
	 */
	public static function getAccountDetail()
	{
		return unserialize(Configuration::get('MR_ACCOUNT_DETAIL'));
	}

	/**
	 * Check if the account is set
	 *
	 * @static
	 * @return bool
	 */
	public static function isAccountSet()
	{
		$details = MondialRelay::getAccountDetail();

		if (!$details || !count($details))
			return false;

		foreach($details as $name => $value)
			if (empty($value))
				return false;

		return true;
	}

	/**
	 * Check any submitted form
	 */
	private function _postValidation()
	{
		// Account settings form validation
		if (Tools::isSubmit('submit_account_detail'))
		{
			if (Tools::getValue('MR_enseigne_webservice') == '' || !preg_match("#^[0-9A-Z]{2}[0-9A-Z ]{6}$#", Tools::getValue('MR_enseigne_webservice')))
				$this->_postErrors[] = $this->l('Invalid Enseigne');
			if (Tools::getValue('MR_code_marque') == '' || !preg_match("#^[0-9]{2}$#", Tools::getValue('MR_code_marque')))
				$this->_postErrors[] = $this->l('Invalid Mark code');
			if (Tools::getValue('MR_webservice_key') == '' || !preg_match("#^[0-9A-Za-z_\'., /\-]{2,32}$#", Tools::getValue('MR_webservice_key')))
				$this->_postErrors[] = $this->l('Invalid Webservice Key');
			if (Tools::getValue('MR_language') == '' || !preg_match("#^[A-Z]{2}$#", Tools::getValue('MR_language')))
				$this->_postErrors[] = $this->l('Invalid Language');
			if (!Tools::getValue('MR_weight_coefficient') OR !Validate::isInt(Tools::getValue('MR_weight_coefficient')))
				$this->_postErrors[] = $this->l('Invalid Weight Coefficient');
		}

		// Shipping form validation
		else if (Tools::isSubmit('submitMethod'))
		{
			if (!preg_match("#^[0-9A-Za-z_\'., /\-]{2,32}$#", Tools::getValue('mr_Name')))
				$this->_postErrors[] = $this->l('Invalid carrier name');
			if (Tools::getValue('mr_ModeCol') != 'CCC')
				$this->_postErrors[] = $this->l('Invalid Col mode');
			if (!preg_match("#^REL|24R|ESP|DRI|LDS|LDR|LD1$#", Tools::getValue('mr_ModeLiv')))
				$this->_postErrors[] = $this->l('Invalid delivery mode');
			if (!Validate::isInt(Tools::getValue('mr_ModeAss')) OR Tools::getValue('mr_ModeAss') > 5 OR Tools::getValue('mr_ModeAss') < 0)
				$this->_postErrors[] = $this->l('Invalid Assurance mode');
			if (!Tools::getValue('mr_Pays_list'))
				$this->_postErrors[] = $this->l('You must choose at least one delivery country.');
		}

		// Order state form validation
		else if (Tools::isSubmit('submit_order_state'))
		{
			if (!Validate::isUnsignedInt(Tools::getValue('id_order_state')))
				$this->_postErrors[] = $this->l('Invalid order state');
		}
	}

	/**
	 * Update account shop
	 *
	 * @return bool
	 */
	public function updateAccountShop()
	{
		return Configuration::updateValue('MR_ACCOUNT_DETAIL', serialize($this->account_shop));
	}

	/**
	 * Post process
	 *
	 * @return array
	 */
	private function _postProcess()
	{
		$post_action = array(
			'type' => Tools::safeOutput(Tools::getValue('MR_tab_name')),
			'message_success' => $this->l('Action Succeed'),
			'had_errors' => false
		);

		if (Tools::isSubmit('submit_account_detail'))
		{
			$this->account_shop = array(
				'MR_ENSEIGNE_WEBSERVICE' => Tools::safeOutput(Tools::getValue('MR_enseigne_webservice')),
				'MR_CODE_MARQUE' =>			Tools::safeOutput(Tools::getValue('MR_code_marque')),
				'MR_KEY_WEBSERVICE' =>		Tools::safeOutput(Tools::getValue('MR_webservice_key')),
				'MR_LANGUAGE' =>			Tools::safeOutput(Tools::getValue('MR_language')),
				'MR_ORDER_STATE' =>			Tools::safeOutput($this->account_shop['MR_ORDER_STATE']),
				'MR_WEIGHT_COEFFICIENT' =>	Tools::safeOutput(Tools::getValue('MR_weight_coefficient')),
				'id_shop' => $this->context->shop->id
			);

			if ($this->updateAccountShop())
				$post_action['message_success'] = $this->l('Account detail has been updated');
			else
				$this->_postErrors[] = $this->l('Cannot Update the account shop');
		}

		else if (Tools::isSubmit('submit_add_shipping'))
		{
			if (($result = $this->addShippingMethod()))
				$post_action['message_success'] = $this->l('Shipping method has been added');
		}

		else if (Tools::isSubmit('submit_order_state'))
		{
			Configuration::updateValue('MONDIAL_RELAY_ORDER_STATE', Tools::getValue('id_order_state'));
			$post_action['message_success'] = $this->l('Order state properly changed');
		}

		else if (($id_mr_method = Tools::getValue('delete_mr')) && $this->disableCarrier((int)$id_mr_method))
			$post_action['message_success'] = $this->l('Carrier has been deleted (disabled under the prestashop database)');

		if (count($this->_postErrors))
			$post_action['had_errors'] = true;

		return $post_action;
	}

	public function getContent()
	{
		$post_action = NULL;
		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$post_action = $this->_postProcess();
		}

		$carriers_list = Db::getInstance()->executeS('
			SELECT m.*
			FROM `'._DB_PREFIX_.'mr_method` m
			LEFT JOIN `'._DB_PREFIX_.'carrier` c
			ON (c.`id_carrier` = m.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'mr_method_shop` ms
			ON ms.`id_mr_method` = m.`id_mr_method`
			WHERE c.`deleted` = 0 AND ms.`id_shop` = '.(int)$this->account_shop['id_shop']);

		$controller = (_PS_VERSION_ < '1.5') ? 'AdminContact' : 'AdminStores';

		// TODO : Fill an array with admi controller name
		$this->context->smarty->assign(array(
				'MR_token_admin_performance' => Tools::getAdminToken('AdminPerformance'.(int)(Tab::getIdFromClassName('AdminPerformance')).(int)($this->context->cookie->id_employee)),
				'MR_token_admin_carriers' => Tools::getAdminToken('AdminCarriers'.(int)(Tab::getIdFromClassName('AdminCarriers')).(int)$this->context->employee->id),
				'MR_token_admin_contact' => array(
					'controller_name' => $controller, 
					'token' => Tools::getAdminToken($controller.(int)(Tab::getIdFromClassName($controller)).(int)$this->context->employee->id)),
				'MR_token_admin_mondialrelay' => Tools::getAdminToken('AdminMondialRelay'.(int)(Tab::getIdFromClassName('AdminMondialRelay')).(int)$this->context->employee->id),
				'MR_token_admin_module' => Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)$this->context->employee->id),
				'MR_enseigne_webservice' => Tools::getValue('MR_enseigne_webservice') ? Tools::getValue('MR_enseigne_webservice') : $this->account_shop['MR_ENSEIGNE_WEBSERVICE'],
				'MR_code_marque' => Tools::getValue('MR_code_marque') ? Tools::getValue('MR_code_marque') : $this->account_shop['MR_CODE_MARQUE'],
				'MR_webservice_key' => Tools::getValue('MR_webservice_key') ? Tools::getValue('MR_webservice_key') : $this->account_shop['MR_KEY_WEBSERVICE'],
				'MR_available_languages' => Language::getLanguages(),
				'MR_selected_language' => $this->account_shop['MR_LANGUAGE'],
				'MR_weight_coefficient' => Tools::getValue('MR_weight_coefficient') ? Tools::getValue('MR_weight_coefficient') : $this->account_shop['MR_WEIGHT_COEFFICIENT'],
				'MR_PS_WEIGHT_UNIT' => Configuration::get('PS_WEIGHT_UNIT'),
				'MR_order_states_list' => OrderState::getOrderStates($this->context->language->id),
				'MR_MONDIAL_RELAY_ORDER_STATE' => Configuration::get('MONDIAL_RELAY_ORDER_STATE'),
				'MR_CRON_URL' => Tools::getHttpHost(true, true)._MODULE_DIR_.$this->name.'/cron.php?secure_key='.Configuration::get('MONDIAL_RELAY_SECURE_KEY'),
				'MR_name' => Tools::getValue('MR_name') ? Tools::getValue('MR_name') : '',
				'MR_carriers_list' => $carriers_list,
				'MR_error_list' => $this->_postErrors,
				'MR_form_action' => $post_action,
				'MR_PS_ADMIN_IMG_' => _PS_ADMIN_IMG_,
				'MR_tab_selected' => Tools::getValue('MR_tab_name') ? Tools::getValue('MR_tab_name') : (MondialRelay::isAccountSet() ? 'account_form' : 'info_form'),
				'MR_delay' => Tools::getValue('MR_delay') ? Tools::getValue('MR_delay') : '',
				'MR_local_path' => MondialRelay::$modulePath,
				'MR_upgrade_detail' => $this->upgrade_detail,
				'MR_unit_weight_used' => Configuration::get('PS_WEIGHT_UNIT'),
				'MR_account_set' => MondialRelay::isAccountSet(),
				'MR_base_dir' => MondialRelay::$moduleURL)
		);
		return $this->fetchTemplate('/tpl/', 'configuration');
	}

	/**
	 * Add new carrier
	 *
	 * @param $name
	 * @param $delay
	 * @return bool|int
	 */
	private function addCarrier($name, $delay)
	{
		$ret = false;

		if (($carrier = new Carrier()))
		{
			$delay_lang = array();
			foreach (Language::getLanguages(false) as $lang)
				$delay_lang[$lang['id_lang']] = $delay;
 			$carrier->name = $name;
			$carrier->active = 1;
			$carrier->range_behavior = 1;
			$carrier->need_range = 1;
			$carrier->external_module_name = 'mondialrelay';
			$carrier->shipping_method = 1;
			$carrier->delay = $delay_lang;
			$carrier->is_module = (_PS_VERSION_ < '1.4') ? 0 : 1;

			$ret = $carrier->add();
		}
		return $ret ? $carrier->id : false;
	}
	
	public function displayInfoByCart($id_cart)
	{
		$html = '<p>';
		$simpleresul = Db::getInstance()->executeS('
			SELECT * FROM ' . _DB_PREFIX_ . 'mr_selected 
			WHERE id_cart='.(int)($id_cart));
	
		if (trim($simpleresul[0]['exp_number']) != '0') 
			$html .= $this->l('Nb expedition:').$simpleresul[0]['exp_number']."<br>";
		if (trim($simpleresul[0]['url_etiquette']) != '0') 
			$html .= "<a href='".$simpleresul[0]['url_etiquette']."' target='etiquette".$simpleresul[0]['url_etiquette']."'>".$this->l('Label URL')."</a><br>";
		if (trim($simpleresul[0]['url_suivi']) != '0')
			$html .= "<a href='".$simpleresul[0]['url_suivi']."' target='suivi".$simpleresul[0]['exp_number']."'>".$this->l('Follow-up URL')."</a><br>";
		if (trim($simpleresul[0]['MR_Selected_Num']) != '')
			$html .= $this->l('Nb Point Relay :').$simpleresul[0]['MR_Selected_Num']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr1']) != '')
			$html .= $simpleresul[0]['MR_Selected_LgAdr1']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr2']) != '')
			$html .= $simpleresul[0]['MR_Selected_LgAdr2']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr3']) != '')
			$html .= $simpleresul[0]['MR_Selected_LgAdr3']."<br>"; 
		if (trim($simpleresul[0]['MR_Selected_LgAdr4']) != '')
			$html .= $simpleresul[0]['MR_Selected_LgAdr4']."<br>"; 
		if (trim($simpleresul[0]['MR_Selected_CP']) != '')
			$html .= $simpleresul[0]['MR_Selected_CP']." ";
		if (trim($simpleresul[0]['MR_Selected_Ville']) != '')
			$html .= $simpleresul[0]['MR_Selected_Ville']."<br>";
		if (trim($simpleresul[0]['MR_Selected_Pays']) != '')
			$html .= $simpleresul[0]['MR_Selected_Pays']."<br>";
		$html .= '</p>';
		return $html;
	}

	/**
	 * Set necessaries values to the created carrier
	 *
	 * @param $id_carrier
	 * @param $dlv_mode
	 * @return bool
	 */
	private function addDefaultCarrierValue($id_carrier, $dlv_mode)
	{
		$weight_coef = $this->account_shop['MR_WEIGHT_COEFFICIENT'];

		// Default Range value depending of the delivery mode
		$range_weight = array(
			'24R' => array(0, 20000 / $weight_coef),
			'DRI' => array(20000 / $weight_coef, 130000 / $weight_coef),
			'LD1' => array(0, 60000 / $weight_coef),
			'LDS' => array(30000 / $weight_coef, 130000 / $weight_coef)
		);

		// Set range weight for a dlv_mode
		if (!Db::getInstance()->execute(
			'INSERT INTO `'._DB_PREFIX_.'range_weight`
					(`id_carrier`, `delimiter1`, `delimiter2`)
					VALUES ('.(int)$id_carrier.', '.$range_weight[$dlv_mode][0].', '.$range_weight[$dlv_mode][1].')'))
		{
			$this->_postErrors[] = $this->l('Range weight can\'t be added');
			return false;
		}

		$range_weight_id = Db::getInstance()->Insert_ID();

		// Set a range price
		if (!Db::getInstance()->execute(
			'INSERT INTO `'._DB_PREFIX_.'range_price`
					 (`id_carrier`, `delimiter1`, `delimiter2`)
					 VALUES ('.(int)$id_carrier.', 0.000000, 10000.000000)'))
		{
			$this->_postErrors[] = $this->l('Range price can\'t be added');
			return false;
		}

		$range_price_id = Db::getInstance()->Insert_ID();

		$groups = Group::getGroups(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($groups as $group)
			if (!Db::getInstance()->execute(
				'INSERT INTO `'._DB_PREFIX_.'carrier_group`
						(id_carrier, id_group)
						VALUES('.(int)$id_carrier.', '.(int)($group['id_group']).')'))
			{
				$this->_postErrors[] = $this->l('Default zone can\'t be added');
				return false;
			}

		// Set default zone
		$zones = Zone::getZones();
		foreach ($zones as $zone)
		{
			if (!Db::getInstance()->execute(
				'INSERT INTO `'._DB_PREFIX_.'carrier_zone`
						(id_carrier, id_zone)
						VALUES('.(int)$id_carrier.', '.(int)($zone['id_zone']).')') ||
				!Db::getInstance()->execute(
					'INSERT INTO `'._DB_PREFIX_.'delivery`
						(id_carrier, id_range_price, id_range_weight, id_zone, price)
						VALUES('.(int)$id_carrier.', '.(int)($range_price_id).', NULL,'.(int)($zone['id_zone']).', 0.00)') ||
				!Db::getInstance()->execute(
					'INSERT INTO `'._DB_PREFIX_.'delivery`
						(id_carrier, id_range_price, id_range_weight, id_zone, price)
						VALUES('.(int)$id_carrier.', NULL, '.(int)($range_weight_id).','.(int)($zone['id_zone']).', 0.00)'))
			{
				$this->_postErrors[] = $this->l('Carrier zone or delivery data can\'t be added');
				return false;
			}
		}
		return true;
	}

	/**
	 * Add new shipping method
	 *
	 * @return bool
	 */
	private function addShippingMethod()
	{
		// Insert new carrier for under Prestashop
		if (!($id_carrier = $this->addCarrier(Tools::getValue('MR_name'), Tools::getValue('MR_delay'))))
		{
			$this->_postErrors[] = $this->l('Carrier can\'t be created in PrestaShop');
			return false;
		}

		$fields = $_POST;

		unset($fields['submit_add_shipping'], $fields['MR_tab_name'], $fields['tab'], $fields['MR_delay']);

		// Force col mod to CCC
		$fields['col_mode'] = 'CCC';
		$fields['id_carrier'] = $id_carrier;

		$query = 'INSERT INTO `'._DB_PREFIX_.'mr_method` (%s) VALUES(%s)';

		$keys = array();
		$values = array();
		foreach($fields as $key => $value)
		{
			$keys[] = '`'.str_replace('MR_', '', $key).'`';
			$values[] = '\''.(is_array($value) ? pSQL(implode(',', $value)) : pSQL($value)).'\'';
		}
		$query = sprintf($query, implode(',',$keys), implode(',', $values));

		if (!Db::getInstance()->execute($query) ||
			!Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'mr_method_shop`
					(id_mr_method, id_shop) VALUES('.(int)Db::getInstance()->INSERT_ID().', '.(int)$this->account_shop['id_shop'].')'))
		{
			$this->l('Carrier method can\'t be added for the module');
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'carrier` WHERE id_carrier='.(int)$id_carrier);
			return false;
		}

		return $this->addDefaultCarrierValue($id_carrier, $fields['MR_dlv_mode']);
	}

	/**
	 * Disable carrier instead of delete to keep history
	 *
	 * @param $id_mr_method
	 */
	public function disableCarrier($id_mr_method)
	{
		if (($id_carrier = Db::getInstance()->getValue(
				'SELECT `id_carrier`
				FROM `'._DB_PREFIX_ .'mr_method`
				WHERE `id_mr_method` = '.(int)($id_mr_method))) &&
			Db::getInstance()->execute(
				'UPDATE `'._DB_PREFIX_ .'carrier` c, `'._DB_PREFIX_ .'mr_method` m
				SET c.`active` = 0, c.`deleted` = 1, m.`is_deleted` = 1
				WHERE
				 	c.`id_carrier` = m.`id_carrier`
					AND c.`id_carrier` = '.(int)$id_carrier))
			return true;

		$this->_postErrors = $this->l('Carrier can\'t be deleted yet');
		return false;
	}

	/**
	 * Get the followup url
	 *
	 * @param $exp_number
	 * @return mixed
	 */
	public function get_followup($exp_number)
	{
		$query = '
			SELECT url_suivi
	  	FROM '._DB_PREFIX_ .'mr_selected
	  	WHERE exp_number='.(int)$exp_number;

		return Db::getInstance()->getValue($query);
	}

	/**
	 * Get the SQL query to fetch order with mr carrier
	 *
	 * @static
	 * @param $id_order_state
	 * @param $weight_coefficient
	 * @return string
	 */
	public static function getBaseOrdersSQLQuery($id_order_state, $weight_coefficient = 0)
	{
		return 'SELECT  o.`id_address_delivery` as id_address_delivery,
							o.`id_order` as id_order,
							o.`id_customer` as id_customer,
							o.`id_cart` as id_cart,
							o.`id_lang` as id_lang,
							mrs.`id_mr_selected` as id_mr_selected,
							CONCAT(c.`firstname`, " ", c.`lastname`) AS `customer`,
							o.`total_paid_real` as total, o.`total_shipping` as shipping,
							o.`date_add` as date, o.`id_currency` as id_currency, o.`id_lang` as id_lang,
							mrs.`MR_poids` as mr_weight, mr.`name` as mr_Name, mrs.`MR_Selected_Num` as MR_Selected_Num,
							mrs.`MR_Selected_Pays` as MR_Selected_Pays, mrs.`exp_number` as exp_number,
							mr.`col_mode` as mr_ModeCol, mr.`dlv_mode` as mr_ModeLiv, mr.`insurance` as mr_ModeAss,
							ROUND(SUM(odt.`product_weight` * odt.`product_quantity`)  * '.(int)$weight_coefficient.') AS "order_weight"
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'carrier` ca
			ON (ca.`id_carrier` = o.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'mr_selected` mrs
			ON (mrs.`id_cart` = o.`id_cart`)
			LEFT JOIN `'._DB_PREFIX_.'mr_method` mr
			ON (mr.`id_mr_method` = mrs.`id_method`)
			LEFT JOIN `'._DB_PREFIX_.'customer` c
			ON (c.`id_customer` = o.`id_customer`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` odt
			ON odt.`id_order` = o.`id_order`
			WHERE (
				SELECT moh.`id_order_state` 
				FROM `'._DB_PREFIX_.'order_history` moh 
				WHERE moh.`id_order` = o.`id_order` 
				ORDER BY moh.`date_add` DESC LIMIT 1) = '.(int)($id_order_state).'
			AND o.`id_order` = mrs.`id_order`';
	}

	/**
	 * Get orders details to create Tickets
	 *
	 * @static
	 * @param array $orderIdList
	 * @param int $filterEntries
	 * @param int $weight_coefficient
	 * @return array
	 */
	public static function getOrders($orderIdList = array(), $filterEntries = MondialRelay::NO_FILTER, $weight_coefficient = 0)
	{
		$account_shop = MondialRelay::getAccountDetail();
		$id_order_state = $account_shop['MR_ORDER_STATE'];
		$sql = MondialRelay::getBaseOrdersSQLQuery($id_order_state, $weight_coefficient);

		if (count($orderIdList))
		{
			$sql .= ' AND o.id_order IN (';
			foreach ($orderIdList as $id_order)
				$sql .= (int)$id_order.', ';
			$sql = rtrim($sql, ', ').')';
		}
		switch($filterEntries)
		{
			case MondialRelay::WITHOUT_HOME_DELIVERY:
				$sql .= 'AND mr.mr_ModeLiv != "LD1" AND mr.mr_ModeLiv != "LDS"';
				break;
		}
		$sql .= '
			GROUP BY o.`id_order`
			ORDER BY o.`date_add` ASC';
		return Db::getInstance()->executeS($sql);
	}

	/**
	 * Get Mondialrelay error code
	 *
	 * @param $code
	 * @return string
	 */
	public function getErrorCodeDetail($code)
	{
		global $statCode;

		if (isset($statCode[$code]))
			return $statCode[$code];
		return $this->l('This error isn\'t referred : ') . $code;
	}

	/**
	 * @param $id_cart
	 * @return mixed
	 */
	public function getRelayPointSelected($id_cart)
	{
		return Db::getInstance()->getRow('
			SELECT s.`MR_selected_num`
			FROM `'._DB_PREFIX_.'mr_selected` s
			WHERE s.`id_cart` = '.(int)$id_cart);
	}

	/**
	 * @param $id_carrier
	 * @return mixed
	 */
	public function isMondialRelayCarrier($id_carrier)
	{
		return Db::getInstance()->getRow('
			SELECT m.`id_mr_method`
			FROM `'._DB_PREFIX_.'mr_method` m
			WHERE `id_carrier` = '.(int)$id_carrier);
	}

	/**
	 *
	 * @param $params
	 */
	public function hookPaymentTop($params)
	{
		if ($this->isMondialRelayCarrier($params['cart']->id_carrier) &&
			!$this->getRelayPointSelected($params['cart']->id))
			$params['cart']->id_carrier = 0;
	}
	
	public function getMethodValueByIdCarier($id_carrier, $key)
	{
		$content = Db::getInstance()->executeS(
			'SELECT *
			FROM `'._DB_PREFIX_.'mr_method` m
			WHERE `id_carrier` = '.(int)$id_carrier);

		return isset($content[$key]) ? $content[$key] : '';
	}

	public function checkAJAX()
	{
		// Avoid loop inclusion
		static $is_included = false;

		// TODO : Find a way to use it on front
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			if (Tools::getValue('mrtoken') && !$is_included)
			{
				$is_included = true;
				include(dirname(__FILE__).'/ajax.php');
				exit(0);
			}
	}
}

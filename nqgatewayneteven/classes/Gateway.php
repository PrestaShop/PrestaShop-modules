<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

ini_set('memory_limit', '512M');
set_time_limit (0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once(dirname(__FILE__).'/GatewayOrder.php');
include_once(dirname(__FILE__).'/GatewayProduct.php');
include_once(dirname(__FILE__).'/Toolbox.php');

class Gateway
{
	private $log = '';
	private $id_order_state_neteven = 0;
	private $id_employee_neteven = 0;
	private $id_customer_neteven = 0;
	private $id_lang = 0;
	private $shipping_price_local = 0;
	private $shipping_price_international = 0;
	private $shipping_delay;
	private $comment;
	private $default_brand;
	private $id_country_default;
	private $default_passwd = '';
	private $feature_links;
	private $order_state_before = array();
	private $order_state_after = array();
	/* mailing system, if product not found in BDD.*/
	private $mail_list_alert = array();
	private $mail_active = false;
	/* Possible order states for an order.*/
	private $t_list_order_status = array('Canceled', 'Refunded', 'Shipped', 'toConfirmed');
	private $t_list_order_status_traite = array('Shipped', 'toConfirmed', 'toConfirm', 'Confirmed');
	private $t_list_order_status_retraite_order = array('Canceled', 'Refunded');
	private $debug = false;
	private $send_request_to_mail = false;
	/* Separator for attribute groups / features */
	private $separator = '$-# ';
	public static $send_order_state_to_neteven = true;
	public static $send_product_to_neteven = true;
	protected $client = null;
	public static $translations = array();
	
	public function __construct($client = null)
	{
		if ($client)
			$this->client = $client;
		else
		{	
			$this->client = new SoapClient(Gateway::getConfig('NETEVEN_URL'), array('trace' => 1));
			$auth = $this->createAuthentication(Gateway::getConfig('NETEVEN_LOGIN'), Gateway::getConfig('NETEVEN_PASSWORD'));
			$this->client->__setSoapHeaders(new SoapHeader(Gateway::getConfig('NETEVEN_NS'), 'AuthenticationHeader', $auth));
		}
		
		$connection = $this->testConnection();
		
		if ($connection != 'Accepted')
			Toolbox::manageError('Connection non acceptée', 'connexion au webservice');

		$this->affectProperties();
		$this->affectTranslations();
	}
	
	public function affectProperties()
	{
		$context = Context::getContext();
		
		// Get the configuration
		$this->shipping_delay = Gateway::getConfig('SHIPPING_DELAY');
		$this->comment = Gateway::getConfig('COMMENT');
		$this->default_brand = Gateway::getConfig('DEFAULT_BRAND');
		$this->id_country_default = Configuration::get('PS_COUNTRY_DEFAULT');
		$this->default_passwd = Gateway::getConfig('PASSWORD_DEFAULT');
		$this->id_employee_neteven = (int)Gateway::getConfig('ID_EMPLOYEE_NETEVEN');
		$this->id_customer_neteven = (int)Gateway::getConfig('ID_CUSTOMER_NETEVEN');
		$this->id_order_state_neteven = (int)Gateway::getConfig('ID_ORDER_STATE_NETEVEN');
		$this->shipping_price_local	= Gateway::getConfig('SHIPPING_PRICE_LOCAL');
		$this->shipping_price_international = Gateway::getConfig('SHIPPING_PRICE_INTERNATIONAL');
		$this->id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		// Get feature links NetEven/PrestaShop
		$feature_links = Db::getInstance()->ExecuteS('
            SELECT ogfl.*, agl.`name` as attribute_name, fl.`name` as feature_name, ogf.`value`
            FROM `'._DB_PREFIX_.'orders_gateway_feature_link` ogfl
            LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl
                ON (agl.`id_attribute_group` = ogfl.`id_attribute_group` AND agl.`id_lang` = '.(int)$context->cookie->id_lang.')
            LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl
                ON (fl.`id_feature` = ogfl.`id_feature` AND fl.`id_lang` = '.(int)$context->cookie->id_lang.')
            LEFT JOIN `'._DB_PREFIX_.'orders_gateway_feature` ogf
                ON (ogf.`id_order_gateway_feature` = ogfl.`id_order_gateway_feature`)
        ');
		
		$temp = array();
		foreach ($feature_links as $feature_link)
			if (!empty($feature_link['id_attribute_group']))
				$temp[$feature_link['attribute_name']] = $feature_link['value'];
			else
				$temp[$feature_link['feature_name']] = $feature_link['value'];

		$this->feature_links = $temp;

		/* Get order states */
		if (Gateway::getConfig('ORDER_STATE_BEFORE'))
			$this->order_state_before = explode(':', Gateway::getConfig('ORDER_STATE_BEFORE'));
		
		if (Gateway::getConfig('ORDER_STATE_AFTER'))
			$this->order_state_after = explode(':', Gateway::getConfig('ORDER_STATE_AFTER'));
		
		$this->mail_list_alert = explode(':', Gateway::getConfig('MAIL_LIST_ALERT'));
		$this->debug = (Gateway::getConfig('DEBUG') == 1) ? true : false;
		$this->send_request_to_mail = (Gateway::getConfig('SEND_REQUEST_BY_EMAIL') == 1) ? true : false;
		$this->mail_active = (Gateway::getConfig('MAIL_ACTIVE') == 1) ? true : false;
		
		if ($this->debug == true)
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}
	}
	
	public function affectTranslations()
	{
		require_once(dirname(__FILE__).'/../nqgatewayneteven.php');
		$nqgatewayneteven = new NqGatewayNeteven();
		
		self::$translations = $nqgatewayneteven->getL();
	}
	
	public static function getL($key)
	{
		if (!isset(self::$translations[$key]))
			return $key;
		
		return self::$translations[$key];
	}

	/**
	 * Creating authentication
	 * @param $login
	 * @param $password
	 * @return array
	 */
	private function createAuthentication($login, $password)
	{
		$seed = '*';
		$stamp = date('c', time());
		$signature = base64_encode(md5(implode('/', array($login, $stamp, $seed, $password)), true));
		
		return array(
			'Method' => '*',
			'Login' => $login,
			'Seed' => $seed,
			'Stamp' => $stamp,
			'Signature' => $signature
		);
	}

	/**
	 * Test of connection
	 * @return null|string
	 */
	private function testConnection()
	{
		try
		{
			$response = $this->client->TestConnection();
			$message = $response->TestConnectionResult;
		}
		catch (Exception $e)
		{
			Toolbox::manageError($e, 'Test connection');
			$message = null;
		}

		if (!is_null($message))
			return $message;

		return;
	}

	public function getValue($name)
	{
		if (empty($this->id_order_state_neteven))
			$this->affectProperties();

		return $this->{$name};
	}

	public static function getConfig($name)
	{
		$value = Db::getInstance()->getValue('
		    SELECT `value`
		    FROM `'._DB_PREFIX_.'orders_gateway_configuration`
		    WHERE name = "'.pSQL($name).'"
		');
		return $value ? $value : false;
	}

	public static function updateConfig($name, $value)
	{
		$config_exist = Db::getInstance()->getValue('
		    SELECT COUNT(*)
		    FROM `'._DB_PREFIX_.'orders_gateway_configuration`
		    WHERE `name` = "'.pSQL($name).'"
		');
		
		if (!$config_exist)
			Db::getInstance()->Execute('
			    INSERT INTO `'._DB_PREFIX_.'orders_gateway_configuration`
			    (`name`, `value`)
			    VALUES ("'.pSQL($name).'", "'.pSQL($value).'")
			');
		else
			Db::getInstance()->Execute('
			    UPDATE `'._DB_PREFIX_.'orders_gateway_configuration`
			    SET `value` = "'.pSQL($value).'"
			    WHERE `name` = "'.pSQL($name).'"
			');
		
	}
	
	public function sendDebugMail($emails, $subject, $message, $classic_mail = false)
	{
		if (!$emails)
			return;

		foreach ($emails as $email)
			if (Validate::isEmail($email))
			{
				if (!$classic_mail)
				{
					$id_lang = $this->id_lang ? (int)$this->id_lang : Configuration::get('PS_LANG_DEFAULT');
					$shop_email = Configuration::get('PS_SHOP_EMAIL');
					$shop_name = Configuration::get('PS_SHOP_NAME');
					Mail::Send(
                        $id_lang,
                        'debug',
                        $subject,
                        array(
                            '{message}' => $message
                        ),
                        $email,
                        null,
                        $shop_email,
                        $shop_name,
                        null,
                        null,
                        dirname(__FILE__).'/../mails/'
                    );
				}
				else
					mail($email, $subject, $message);

				if ($this->getValue('debug'))
					Toolbox::displayDebugMessage(self::getL('Send email to').' : '.$email);
			}

	}
}
<?php
/**
* 2014 PAYU LATAM
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
*  @author    PAYU LATAM <sac@payulatam.com>
*  @copyright 2014 PAYU LATAM
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'/payulatam/payulatam.php');

class PayUController extends FrontController
{
	public $ssl = true;

	public function setMedia()
	{
		parent::setMedia();
	}

	public function process()
	{
		parent::process();

		$params = $this->initParams();
		self::$smarty->assign(array(
				'formLink' => Configuration::get('PAYU_DEMO') != 'yes' ? 'https://gatewaylap.pagosonline.net/ppp-web-gateway/' :
				'https://stg.gatewaylap.pagosonline.net/ppp-web-gateway/',
				'payURedirection' => $params
			));
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_MODULE_DIR_.'payulatam/views/templates/front/redirect.tpl');
	}

public function initParams()
{
	$tax = (float)self::$cart->getOrderTotal() - (float)self::$cart->getOrderTotal(false);
	$base = (float)self::$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) + (float)self::$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS) - (float)$tax;
	if ($tax == 0)
		$base = 0;

	$currency = new Currency(self::$cart->id_currency);

	$language = new Language(self::$cart->id_lang);

	$customer = new Customer(self::$cart->id_customer);

	$ref = 'payU_'.Configuration::get('PS_SHOP_NAME').'_'.(int)self::$cart->id;

	$token = md5(Tools::safeOutput(Configuration::get('PAYU_API_KEY')).'~'.Tools::safeOutput(Configuration::get('PAYU_MERCHANT_ID')).'~'.$ref.'~'
	.(float)self::$cart->getOrderTotal().'~'.Tools::safeOutput($currency->iso_code));

	$params = array(
		array('value' => (Configuration::get('PAYU_DEMO') == 'yes' ? 1 : 0), 'name' => 'test'),
		array('value' => Tools::safeOutput(Configuration::get('PAYU_MERCHANT_ID')), 'name' => 'merchantId'),
		array('value' => $ref, 'name' => 'referenceCode'),
		array('value' => Tools::substr(Configuration::get('PS_SHOP_NAME').' Order', 0, 255), 'name' => 'description'),
		array('value' => (float)self::$cart->getOrderTotal(), 'name' => 'amount'),
		array('value' => Tools::safeOutput($customer->email), 'name' => 'buyerEmail'),
		array('value' => (float)$tax, 'name' => 'tax'),
		array('value' => 'PRESTASHOP', 'name' => 'extra1'),
		array('value' => (float)$base, 'name' => 'taxReturnBase'),
		array('value' => Tools::safeOutput($currency->iso_code), 'name' => 'currency'),
		array('value' => Tools::safeOutput($language->iso_code), 'name' => 'lng'),
		array('value' => Tools::safeOutput($token), 'name' => 'signature'),
		array('value' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'history.php', 'name' =>
		'responseUrl'),
		array('value' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/payulatam/validation.php', 'name' => 'confirmationUrl'),
	);

	if (Configuration::get('PAYU_ACCOUNT_ID') != 0)
		$params[] = array('value' => (int)Configuration::get('PAYU_ACCOUNT_ID'), 'name' => 'accountId');

	return $params;
}

	public function createPendingOrder()
	{
		$payu = new PayULatam();
		$payu->validateOrder((int)self::$cart->id, (int)Configuration::get('PAYU_WAITING_PAYMENT'), (float)self::$cart->getOrderTotal(),
		$payu->displayName, null, array(), null, false,	self::$cart->secure_key);
	}
}

$payUController = new PayUController();

if (Tools::getIsset(Tools::getValue('create-pending-order')))
	$payUController->createPendingOrder();
else
	$payUController->run();

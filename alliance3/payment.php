<?php
/*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'/merchantware/merchantware.php');
include_once(_PS_MODULE_DIR_.'/merchantware/class/call.php');

class MerchantWareController extends FrontController
{
	private $_paymentLink = array('test' => 'https://staging.merchantware.net/transportweb4/TransportWeb.aspx', 'prod' => 'https://transport.merchantware.net/v4/transportweb.aspx');
	public $ssl = true;

	public function process()
	{
		parent::process();

		$params = $this->initParams();
		$call = new Call();
		
		try
		{
			$result = $call->createTransaction($params);
		}
		catch (Exception $e)
		{
			//d($e);
		}
		if (isset($result->CreateTransactionResult) && isset($result->CreateTransactionResult->TransportKey) && $result->CreateTransactionResult->TransportKey != '')
		{
			self::$smarty->assign('formLink', $this->_paymentLink[Configuration::get('MERCHANT_WARE_MODE')]);
			self::$smarty->assign('transportKey', Tools::safeOutput($result->CreateTransactionResult->TransportKey));
		}
		elseif (isset($result->CreateTransactionResult))
		{
			Logger::addLog('Module merchantware: '.$result->CreateTransactionResult->Messages->Message[0]->Information, 2);
			self::$smarty->assign('error', true);
		}
		else
		{
			self::$smarty->assign('error', true);
			Logger::addLog('Module merchantware: no message returned', 2);
		}
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_MODULE_DIR_.'merchantware/tpl/redirect.tpl');
	}

	public function initParams()
	{
		$customer = new Customer(self::$cart->id_customer);
		$address = new Address(self::$cart->id_address_invoice);

		$params = array();
		$params['amount'] = self::$cart->getOrderTotal();
		$params['customer_id'] = self::$cart->id_customer;
		$params['cart_id'] = self::$cart->id;
		$params['store_name'] = Configuration::get('PS_SHOP_NAME');
		$params['customer_address'] = $address->address1.($address->address2 != '' ? ', '.$address->address2: '');
		$params['customer_zipcode'] = $address->postcode;
		$params['customer_lastname'] = $customer->lastname;
		$params['logo'] = Configuration::get('MW_LOGO');
		$params['validation_link'] = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/merchantware/validation.php';
		$params['cart_id'] = self::$cart->id;

		$params['layout']['ScreenBackgroundColor'] = Configuration::get('MW_SCREENBACKGROUNDCOLOR');
		$params['layout']['ContainerBackgroundColor'] = Configuration::get('MW_CONTAINERBACKGROUNDCOLOR');
		$params['layout']['ContainerFontColor'] = Configuration::get('MW_CONTAINERFONTCOLOR');
		$params['layout']['ContainerHelpFontColor'] = Configuration::get('MW_CONTAINERHELPFONTCOLOR');
		$params['layout']['ContainerBorderColor'] = Configuration::get('MW_CONTAINERBORDERCOLOR');
		$params['layout']['LogoBackgroundColor'] = Configuration::get('MW_LOGOBACKGROUNDCOLOR');
		$params['layout']['LogoBorderColor'] = Configuration::get('MW_LOGOBORDERCOLOR');
		$params['layout']['TooltipBackgroundColor'] = Configuration::get('MW_TOOLTIPBACKGROUNDCOLOR');
		$params['layout']['TooltipBorderColor'] = Configuration::get('MW_TOOLTIPBORDERCOLOR');
		$params['layout']['TooltipFontColor'] = Configuration::get('MW_TOOLTIPFONTCOLOR');
		$params['layout']['TextboxBackgroundColor'] = Configuration::get('MW_TEXTBOXBACKGROUNDCOLOR');
		$params['layout']['TextboxBorderColor'] = Configuration::get('MW_TEXTBOXBORDERCOLOR');
		$params['layout']['TextboxFocusBackgroundColor'] = Configuration::get('MW_TEXTBOXFOCUSBACKGROUNDCOLOR');
		$params['layout']['TextboxFocusBorderColor'] = Configuration::get('MW_TEXTBOXFOCUSBORDERCOLOR');
		$params['layout']['TextboxFontColor'] = Configuration::get('MW_TEXTBOXFONTCOLOR');

		return $params;
	}
}

$merchantWareController = new MerchantWareController();
$merchantWareController->run();

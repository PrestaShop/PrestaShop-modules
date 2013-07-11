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
*  @copyright  2007-20131 PrestaShop SA
*  @version  Release: $Revision: 9844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_."/fidbag/class/fidbagWebService.php");
require_once(_PS_MODULE_DIR_."/fidbag/class/fidbagUser.php");

class Fidbag extends Module
{

	private $_postErrors = array();
	private $_moduleName = 'fidbag';
	private $_fieldsList = array();

	function __construct()
	{
		$this->name = 'fidbag';
		$this->tab = 'pricing_promotion';
		$this->version = '1.2';
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('Fid\'Bag');
		$this->description = $this->l('Provide a loyalty program to your customers.');

		$warning = array();

		$this->loadingVar();

		foreach ($this->_fieldsList as $keyConfiguration => $name)
			if (!Configuration::get($keyConfiguration) && !empty($name))
				$warning[] = '\''.$name.'\' ';

		if (count($warning) > 1)
			$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly.').' ';
		elseif (count($warning) == 1)
			$this->warning .= implode(' , ',$warning).$this->l('has to be configured to use this module correctly.').' ';

		/**
		 * Backward compatibility
		 **/
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.'/'.$this->name.'/backward_compatibility/backward.php');
	}

	function install()
	{
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;
		if (!parent::install() || !$this->registerHook('paymentTop') || !$this->registerHook('newOrder') || !$this->registerHook('orderDetailDisplayed') || !$this->registerHook('extraRight'))
			return false;

		Configuration::updateValue('FIDBAG_MERCHANT_CERTIFICAT', '120890882');
		Configuration::updateValue('FIDBAG_TOKEN', md5(rand()));
		Configuration::updateValue('FIDBAG_TEST_ENVIRONMENT', true);

		return true;
	}

	function uninstall()
	{
		return parent::uninstall() &&
			$this->unregisterHook('newOrder') && 
			$this->unregisterHook('paymentTop') &&
			$this->unregisterHook('orderDetailDisplayed');
	}

	public function loadingVar()
	{
		// Loading Fields List
		$this->_fieldsList = array(
			'FIDBAG_MERCHANT_CODE' => $this->l('Fid\'bag Merchant Code'),
			'FIDBAG_MERCHANT_CERTIFICAT' => $this->l('Fid\'bag Merchant Certificat'),
			'FIDBAG_MERCHANT_ACTIVE' => '',
			'FIDBAG_TOKEN' => ''
		);
	}

	public function getContent()
	{
		$html = '';
		$error = array();
		$smarty = Context::getContext()->smarty;

		if (!empty($_POST) && Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
			{
				$this->_postProcess();
				$html .= $this->_displayValidation();
			}
			else
				foreach ($this->_postErrors AS $err)
					$error[] = $err;
		}
		$smarty->assign('error', $error);
		$html .= $this->_displayConfiguration();
		return $html;
	}

	private function _displayConfiguration()
	{
		$smarty = Context::getContext()->smarty;

		$var = array('path' => $this->_path,
					 'tab' => Tools::safeOutput(Tools::getValue('tab')),
					 'configure' => Tools::safeOutput(Tools::getValue('configure')),
					 'token' => Tools::safeOutput(Tools::getValue('token')),
					 'tab_module' => Tools::safeOutput(Tools::getValue('tab_module')),
					 'module_name' => Tools::safeOutput(Tools::getValue('module_name')),
					 'fidbag_token' => Configuration::get('FIDBAG_TOKEN'),
					 'img' => _PS_IMG_,
					 'module' => _PS_MODULE_DIR_.$this->_moduleName.'/');
		$smarty->assign('glob', $var);

		$merchant = array(
			'code' => Configuration::get('FIDBAG_MERCHANT_CODE'),
			'test_environment' => (bool)Configuration::get('FIDBAG_TEST_ENVIRONMENT'),
		);
		
		$smarty->assign('merchant', $merchant);
		return $this->display( __FILE__, 'views/templates/admin/configuration.tpl' );
	}

	private function _postValidation()
	{
		$code = Tools::getValue('fidbag_merchant_code');

		if (!$code)
			$this->_postErrors[] = $this->l('All the fields are required');
		elseif (extension_loaded('soap'))
		{
			$webService = new FidbagWebService();
			$return = $webService->action('GetMerchantInformations', array('MerchantCode' => Tools::getValue('fidbag_merchant_code')));
			if ($return != null)
			{
				$json_return = Tools::jsonDecode($return->GetMerchantInformationsResult);
				if ($json_return->returnInfos->mCode != 0)
					$this->_postErrors[] = $this->l('failed login credential');
			}
			else
				$this->_postErrors[] = $this->l('WebService Error. Please, Try again later.');
		}
		else
			$this->_postErrors[] = $this->l('Soap must be activated');
	}

	private function _displayValidation()
	{
		return '
			<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
			</div>';
	}

	private function _postProcess()
	{
		Configuration::updateValue('FIDBAG_MERCHANT_CODE', Tools::getValue('fidbag_merchant_code'));
		Configuration::updateValue('FIDBAG_TEST_ENVIRONMENT', (int)Tools::getValue('fidbag_environment'));
		Configuration::updateValue('FIDBAG_MERCHANT_ACTIVE', 'on');
	}

	private function _activeVerification()
	{
		if (!$this->active || Configuration::get('FIDBAG_MERCHANT_ACTIVE') != 'on' || !extension_loaded('soap'))
			return false;
		return true;
	}

	public function hookPaymentTop($param)
	{
		if (!$this->_activeVerification())
			return false;
		
		$smarty = Context::getContext()->smarty;

		$customer = new Customer($param['cart']->id_customer);
		$addresses = $customer->getAddresses(Context::getContext()->language->id);
		$address = new Address($addresses[0]['id_address']);
		$cart = new Cart($param['cart']->id);
		$link = new Link();
		
		$fidbag_user = new FidbagUser($param['cart']->id_customer);
		$fidbag_user->getFidBagUser();
		
		$var = array('path' => $this->_path,
					 'img' => _PS_IMG_,
					 'id_customer' => $param['cart']->id_customer,
					 'id_cart' => $param['cart']->id,
					 'fidbag_token' => Configuration::get('FIDBAG_TOKEN'),
					 'module' => _PS_MODULE_DIR_.$this->_moduleName.'/');

		$smarty->assign('glob', $var);
		$smarty->assign('main_url', $this->getMainUrl());
		$smarty->assign('fidbag_token', Tools::encrypt((int)$param['cart']->id_customer));
		$smarty->assign('price', $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING));
		$smarty->assign('shipment', $cart->getOrderTotal(true, Cart::ONLY_SHIPPING));
		$smarty->assign('total_cart', $cart->getOrderTotal());
		$smarty->assign('shipping', $cart->getOrderTotal(true, Cart::ONLY_SHIPPING));
		$smarty->assign('discount', $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
		$smarty->assign('fidbag_login', $fidbag_user->getLogin());
		$smarty->assign('fidbag_password', $fidbag_user->getPassword());
		
		if (_PS_VERSION_ < '1.5')
			$smarty->assign('base_dir', Tools::getProtocol().Tools::getHttpHost().__PS_BASE_URI__);
		
		if ((int)Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
			$smarty->assign('redirect', $link->getPageLink('order-opc.php'));
		else
		{
			if (_PS_VERSION_ >= '1.5')
				$smarty->assign('redirect', $link->getPageLink('order.php', false, null, array('step' => '3')));
			else
				$smarty->assign('redirect', $link->getPageLink('order.php?step=3'));
		}

		if (isset($customer->id_gender))
			$smarty->assign('sub_gender', $customer->id_gender);

		$smarty->assign('sub_lastname', $customer->lastname);
		$smarty->assign('sub_firstname', $customer->firstname);
		$smarty->assign('sub_email', $customer->email);
		$smarty->assign('sub_address', $address->address1.' '.$address->address2);
		$smarty->assign('sub_zipcode', $address->postcode);
		$smarty->assign('sub_city', $address->city);

		if (_PS_VERSION_ < '1.5')
			return $this->display( __FILE__, 'views/templates/hook/payment_top_14x.tpl' );
		return $this->display( __FILE__, 'views/templates/hook/payment_top.tpl' );
	}

	public function hookNewOrder($params)
	{
		if (!$this->_activeVerification())
			return false;

		$fidbag_user = new FidbagUser($params['cart']->id_customer);
		$cart = new Cart($params['cart']->id);	
		
		if ($fidbag_user->getFidBagUser() && !$fidbag_user->getPayed() && $cart->id == $fidbag_user->getIdCart())
		{
			if (_PS_VERSION_ >= '1.5')
				$discounts = $cart->getCartRules();
			else
				$discounts = $cart->getDiscounts();

			if (count($discounts))
			{
				foreach ($discounts as $key => $val)
				{
					if (strcmp($val['name'], 'Fid\'Bag') === 0)
					{
						if (_PS_VERSION_ >= '1.5')
						{
							$discount = new Discount($val['id_discount']);
							$discount_value = $discount->reduction_amount;
						}
						else
						{
							$discount = $val;
							$discount_value = $val['value'];
						}
					}
				}
			}
			
			$webService = new FidbagWebService();
			$total_cart = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);

			if (isset($discount_value) && ((int)$discount_value > 0))
			{
				$return = $webService->action('ConsumeImmediateRebate', array(
					'CardNumber' => $fidbag_user->getCardNumber(),
					'MerchantCode' => Configuration::get('FIDBAG_MERCHANT_CODE'),
					'Amount' => (int)($total_cart),
					'RebateUsed' => (int)$discount_value,
					'AmountDo' => (int)$total_cart,
				));
				
				$json_return = Tools::jsonDecode($return->ConsumeImmediateRebateResult);
			}
			else
			{
				$return = $webService->action('CreditFidCard', array(
					'MerchantCode' => Configuration::get('FIDBAG_MERCHANT_CODE'),
					'FidCardNumber' => $fidbag_user->getCardNumber(),
					'PurchaseOrderAmountTTC' => (int)$total_cart
				));
				
				$json_return = Tools::jsonDecode($return->CreditFidCardResult);
			}
			$fidbag_user->setPayed(true);
		}
	}

	public function hookOrderDetailDisplayed($params)
	{
		if (!$this->_activeVerification())
			return false;

		$fidBagUser = new FidbagUser($params['order']->id_customer);
		if (!$fidBagUser)
			return false;

		$smarty = Context::getContext()->smarty;

		$webService = new FidbagWebService();
		$fidBagUser->getFidBagUser();
		$return = $webService->action('GetFidBagCardInformations',
			array(
				'MerchantCode' => Configuration::get('FIDBAG_MERCHANT_CODE'),
				'FidCardNumber' => $fidBagUser->getCardNumber()
			)
		);
		
		if ($return != null)
		{
			$json_return = Tools::jsonDecode($return->GetFidBagCardInformationsResult);
			$smarty->assign('fidbag', $json_return);
		}
		return $this->display( __FILE__, 'views/templates/hook/order.tpl' );
	}

	public function hookExtraRight($params)
	{
		$product = new Product((int)Tools::getValue('id_product'));

		if ($product->getPrice() <= 0)
			return false;

		$points = (int)round(10 * $product->getPrice());
		$pointsBefore = 0;
		$cart = null;

		if (isset($params['cart']))
		{
			$cart = new Cart((int)$params['cart']->id);
			$pointsBefore = (int)round(10 * $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING));
		}

		$pointsAfter = $pointsBefore + $points;

		if (_PS_VERSION_ < '1.5')
			$this->smarty->assign('base_dir', Tools::getProtocol().Tools::getHttpHost().__PS_BASE_URI__);

		$this->smarty->assign(
			array(
				'points' => (int)$points,
				'total_points' => (int)$pointsAfter,
				'points_in_cart' => (int)$pointsBefore
			)
		);

		return $this->display(__FILE__, 'views/templates/hook/product.tpl');
	}
	
	protected function getMainUrl()
	{
		$protocol_link = Tools::usingSecureMode() ? 'https://' : 'http://';
		return $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__;
	}
}

?>

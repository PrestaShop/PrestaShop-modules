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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Alliedwallet extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'alliedwallet';
		$this->tab = 'payments_gateways';
		$this->author = 'PrestaShop';

		$this->version = '1.2';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Allied Wallet');
		$this->description = $this->l('Accept credit and debit cards online with Allied Wallet. You\'ll be able to accept 164 different currencies around the globe with safety and security.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

		/** Backward compatibility 1.4 and 1.5 */
		require(_PS_MODULE_DIR_.'/alliedwallet/backward_compatibility/backward.php');
	}

	public function install()
	{
		if (!parent::install() || !Configuration::updateValue('ALLIEDWALLET_MERCHANT_ID', '') || 
		!Configuration::updateValue('ALLIEDWALLET_SITE_ID', '')	|| 
		!Configuration::updateValue('ALLIEDWALLET_CONFIRM_PAGE', 'http://'.Tools::safeOutput($_SERVER['HTTP_HOST']).
		__PS_BASE_URI__.'modules/alliedwallet/validation.php') || 
		!Configuration::updateValue('ALLIEDWALLET_RETURN_PAGE', 'http://'.Tools::safeOutput($_SERVER['HTTP_HOST']).__PS_BASE_URI__.'history.php') || 
		!$this->registerHook('payment'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ALLIEDWALLET_MERCHANT_ID') || !Configuration::deleteByName('ALLIEDWALLET_SITE_ID') || !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>
		<p><img src="../modules/alliedwallet/alliedwallet.gif" alt="alliedwallet"/></p>
		<fieldset style="margin-bottom:10px">
<b>'.$this->l('Accept payments with Allied Wallet now.').'</b><br/><br/>'.$this->l('Are you ready to begin accepting 164 different currencies in nearly every card brand, direct debit, and ACH?').'<br/>'.$this->l('Are you ready to protect your business with state-of-the-art security?').'<br/>'.$this->l('Are you ready to see more ease in your business with our tracking and reporting?').'<br/><br/>'.$this->l('Pair your PrestaShop store with the most innovative and fully-featured payment solution available. See your profit today and use the module below.').'<br/><br/>'.$this->l('If you don\'t have an Allied Wallet account yet,').' <a target="_BLANK" style="color:blue;text-decoration:underline" href="https://www.alliedwallet.com/sign-up"><b>'.$this->l('sign up today').'</b></a> '.$this->l('and begin processing in as little as 24 hours at a rate starting as low as 1.95%.').'</fieldset>';

		if (isset($_POST['submitAlliedwallet']))
		{
			if (empty($_POST['merchant_id']))
				$this->_postErrors[] = $this->l('Allied Wallet Merchant ID is required');
			if (empty($_POST['site_id']))
				$this->_postErrors[] = $this->l('Allied Wallet Site ID is required');
			if (empty($_POST['return_url']))
				$this->_postErrors[] = $this->l('Complete URL is required and must be correct.');

			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('ALLIEDWALLET_MERCHANT_ID', $_POST['merchant_id']);
				Configuration::updateValue('ALLIEDWALLET_SITE_ID', $_POST['site_id']);
				Configuration::updateValue('ALLIEDWALLET_RETURN_PAGE', $_POST['return_url']);
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displayFormSettings();

		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') :
				$this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ?
					$this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('ALLIEDWALLET_MERCHANT_ID', 'ALLIEDWALLET_SITE_ID'));

		$this->_html .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" class="clear">
		<fieldset style="margin-top: 10px;">
			<legend><img src="../img/admin/contact.gif" alt="" />'.$this->l('Settings').'</legend>
			<label>'.$this->l('Allied Wallet Merchant ID').'</label>
			<div class="margin-form"><input type="text" size="40" name="merchant_id" value="'.Tools::safeOutput(isset($_POST['merchant_id']) ? $_POST['merchant_id'] : (isset($conf['ALLIEDWALLET_MERCHANT_ID']) ? $conf['ALLIEDWALLET_MERCHANT_ID'] : '')).'" /> <sup>*</sup></div>
			<label>'.$this->l('Allied Wallet Site ID').'</label>
			<div class="margin-form"><input type="text" size="40" name="site_id" value="'.Tools::safeOutput(isset($_POST['site_id']) ? $_POST['site_id'] : (isset($conf['ALLIEDWALLET_SITE_ID']) ? $conf['ALLIEDWALLET_SITE_ID'] : '')).'" /> <sup>*</sup></div>
			<label>'.$this->l('Redirect URL').'</label>
			<div class="margin-form"><input type="text" size="40" name="return_url" value="'.Tools::safeOutput(Configuration::get('ALLIEDWALLET_RETURN_PAGE')).'" /> '.$this->l('Please enter the URL of the page where customers will be redirected after their purchases.').'</div>
			<div class="margin-form"><input type="submit" name="submitAlliedwallet" value="'.$this->l('Update settings').'" class="button" /></div>
		</fieldset>
		</form>';
	}

	public function hookPayment($params)
	{
		if (!$this->active || !Configuration::get('ALLIEDWALLET_MERCHANT_ID') || !Configuration::get('ALLIEDWALLET_SITE_ID'))
			return;

		/* Get Customer's information and the current Currency */
		$address = new Address((int)$params['cart']->id_address_invoice);
		$customer = new Customer((int)$params['cart']->id_customer);
		$currency = new Currency((int)$params['cart']->id_currency);
		if (!Validate::isLoadedObject($address) || !Validate::isLoadedObject($customer) || !Validate::isLoadedObject($currency))
			return;

		/* Get products and discounts in the current Shopping cart */
		$products = $params['cart']->getProducts();
		$discounts = _PS_VERSION_ >= 1.5 ? $params['cart']->getCartRules() : $params['cart']->getDiscounts();
		if (count($discounts))
			foreach ($discounts as $k => $v)
			{
				$v['total_wt'] = (!isset($v['value']) ? $v['reduction_amount'] : $v['value']) * -1;
				$v['cart_quantity'] = 1;
				$v['name'] = 'Discount #'.(int)$v['id_discount'].(!empty($v['name']) ? ' ('.Tools::safeOutput($v['name']).')' : '');
				$v['id_product'] = (int)$v['id_discount'];
				$products[] = $v;
			}

		/* Allied Wallet requires 50 chars max. for the two address lines, and 20 chars for the city */
		$address->address1 = substr($address->address1, 0, 50);
		if (isset($address->address2) && !empty($address->address2))
			$address->address2 = substr($address->address2, 0, 50);
		$address->city = substr($address->city, 0, 20);

		$smarty = $this->context->smarty;
		$smarty->assign(array(
			'address' => $address,
			'country' => new Country((int)$address->id_country),
			'customer' => $customer,
			'merchant_id' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_MERCHANT_ID')),
			'site_id' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_SITE_ID')),
			'currency' => $currency,
			'shipping' =>  number_format((_PS_VERSION_ >= 1.5 ? $params['cart']->getOrderTotal(true, Cart::ONLY_SHIPPING) : $params['cart']->getOrderShippingCost()), 2, '.', ''),
			'alliedProducts' => $products,
			'total' => number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '.', ''),
			'id_cart' => (int)$params['cart']->id,
			'goBackUrl' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_RETURN_PAGE')),
			'confirmUrl' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_CONFIRM_PAGE'))));

		return $this->display(__FILE__, 'alliedwallet.tpl');
	}
}
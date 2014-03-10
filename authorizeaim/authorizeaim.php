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

class authorizeAIM extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'authorizeaim';
		$this->tab = 'payments_gateways';
		$this->version = '1.4.7';
		$this->author = 'PrestaShop';
		$this->aim_available_currencies = array('USD','AUD','CAD','EUR','GBP','NZD');

		parent::__construct();

		$this->displayName = 'Authorize.net AIM (Advanced Integration Method)';
		$this->description = $this->l('Receive payment with Authorize.net');

		/* For 1.4.3 and less compatibility */
		$updateConfig = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		/* Check if cURL is enabled */
		if (!is_callable('curl_exec'))
			$this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		return parent::install() &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('payment') &&
			$this->registerHook('header') &&
			Configuration::updateValue('AUTHORIZE_AIM_DEMO', 1) &&
			Configuration::updateValue('AUTHORIZE_AIM_HOLD_REVIEW_OS', _PS_OS_ERROR_);
	}

	public function uninstall()
	{
		Configuration::deleteByName('AUTHORIZE_AIM_DEMO');
		Configuration::deleteByName('AUTHORIZE_AIM_CARD_VISA');
		Configuration::deleteByName('AUTHORIZE_AIM_CARD_MASTERCARD');
		Configuration::deleteByName('AUTHORIZE_AIM_CARD_DISCOVER');
		Configuration::deleteByName('AUTHORIZE_AIM_CARD_AX');
		Configuration::deleteByName('AUTHORIZE_AIM_HOLD_REVIEW_OS');
		
		/* Removing credentials configuration variables */
		$currencies = Currency::getCurrencies(false, true);
		foreach ($currencies as $currency)
			if (in_array($currency['iso_code'], $this->aim_available_currencies))
			{
				Configuration::deleteByName('AUTHORIZE_AIM_LOGIN_ID_'.$currency['iso_code']);
				Configuration::deleteByName('AUTHORIZE_AIM_KEY'.$currency['iso_code']);
			}

		return parent::uninstall();
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR'))
			$this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
		else
			$this->context->smarty->assign('status', 'failed');

		return $this->display(__FILE__, 'hookorderconfirmation.tpl');
	}

	public function getContent()
	{
		$html = '';
		if (Tools::isSubmit('submitModule'))
		{
			Configuration::updateValue('AUTHORIZE_AIM_DEMO', Tools::getvalue('authorizeaim_demo_mode'));
			Configuration::updateValue('AUTHORIZE_AIM_CARD_VISA', Tools::getvalue('authorizeaim_card_visa'));
			Configuration::updateValue('AUTHORIZE_AIM_CARD_MASTERCARD', Tools::getvalue('authorizeaim_card_mastercard'));
			Configuration::updateValue('AUTHORIZE_AIM_CARD_DISCOVER', Tools::getvalue('authorizeaim_card_discover'));
			Configuration::updateValue('AUTHORIZE_AIM_CARD_AX', Tools::getvalue('authorizeaim_card_ax'));
			Configuration::updateValue('AUTHORIZE_AIM_HOLD_REVIEW_OS', Tools::getvalue('authorizeaim_hold_review_os'));
			
			/* Updating credentials for each active currency */
			foreach ($_POST as $key => $value)
			{
				if (strstr($key, 'authorizeaim_login_id_'))
					Configuration::updateValue('AUTHORIZE_AIM_LOGIN_ID_'.str_replace('authorizeaim_login_id_', '', $key), $value);
				elseif (strstr($key, 'authorizeaim_key_'))
					Configuration::updateValue('AUTHORIZE_AIM_KEY_'.str_replace('authorizeaim_key_', '', $key), $value);		
			}

			$html .= $this->displayConfirmation($this->l('Configuration updated'));
		}

		// For "Hold for Review" order status
		$orderStates = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

		$this->context->controller->addCSS($this->_path.'css/authorizeaim.css');
		
		$html .= '
		<script type="text/javascript">
			/* Fancybox */
			$(document).ready(function(){
				$("a.authorizeaim-video-btn").live("click", function(){
				$.fancybox({
					"type" : "iframe",
					"href" : "//www.youtube.com/embed/8SQ3qst0_Pk?&rel=0&autoplay=1&origin=http://'.Configuration::get('PS_SHOP_DOMAIN').'",
					"swf": {"allowfullscreen":"true", "wmode":"transparent"},
					"overlayShow" : true,
					"centerOnScroll" : true,
					"speedIn" : 100,
					"speedOut" : 50,
					"width" : 853,
					"height" : 480
					});
				return false;
				});
			})
		</script>

		<div class="authorizeaim-wrapper">
			<a href="http://reseller.authorize.net/application/prestashop/" class="authorizeaim-logo" target="_blank"><img src="../modules/'.$this->name.'/img/logo_authorize.png" alt="Authorize.net" border="0" /></a>
			<p class="authorizeaim-intro">'.$this->l('Start accepting payments through your PrestaShop store with Authorize.Net, the pioneering provider of ecommerce payment services.  Authorize.Net makes accepting payments safe, easy and affordable.').'</p>
			<p class="authorizeaim-sign-up">'.$this->l('Do you require a payment gateway account? ').'<a href="http://reseller.authorize.net/application/prestashop/" target="_blank">'.$this->l('Sign Up Now').'</a></p>
			<div class="authorizeaim-content">
				<div class="authorizeaim-leftCol">
					<h3>'.$this->l('Why Choose Authorize.Net?').'</h3>
					<ul>
						<li>'.$this->l('Leading payment gateway since 1996 with 400,000+ active merchants').'</li>
						<li>'.$this->l('Multiple currency acceptance').'</li>
						<li>'.$this->l('FREE award-winning customer support via telephone, email and online chat').'</li>
						<li>'.$this->l('FREE Virtual Terminal for mail order/telephone order transactions').'</li>
						<li>'.$this->l('No Contracts or long term commitments ').'</li>
						<li>'.$this->l('Additional services include: ').'
							<ul class="none">
								<li>'.$this->l('- Advanced Fraud Detection Suite™').'</li>
								<li>'.$this->l('- Automated Recurring Billing ™').'</li>
								<li>'.$this->l('- Customer Information Manager').'</li>
							</ul>
						</li>
						<li>'.$this->l('Gateway and merchant account set up available').'</li>
						<li>'.$this->l('Simple setup process').'
					</li>
					</ul>
					<ul class="none" style = "display: inline; font-size: 13px;">
						<li><a href="http://reseller.authorize.net/application/prestashop/" target="_blank" class="authorizeaim-link">'.$this->l('Sign up Now').'</a></li>
					</ul>		
				</div>
				<div class="authorizeaim-video">
					<p>'.$this->l('Have you ever wondered how credit card payments work? Connecting a payment application to the credit card processing networks is difficult, expensive and beyond the resources of most businesses. Authorize.Net provides the complex infrastructure and security necessary to ensure secure, fast and reliable transactions. See How:').'</p>
					<a href="http://www.youtube.com/watch?v=8SQ3qst0_Pk" class="authorizeaim-video-btn">
						<img src="../modules/'.$this->name.'/img/video-screen.jpg" alt="Merchant Warehouse screencast" />
						<img src="../modules/'.$this->name.'/img/btn-video.png" alt="" class="video-icon" />
					</a>
				</div>
			</div>';
		
		
		$html .= '<form action="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
				<legend><img src="../img/admin/contact.gif" alt="" />'.$this->l('Configure your existing Authorize.Net Accounts').'</legend>';
				
		/* Determine which currencies are enabled on the store and supported by Authorize.net & list one credentials section per available currency */
		$currencies = Currency::getCurrencies(false, true);
		foreach ($currencies as $currency)
			if (in_array($currency['iso_code'], $this->aim_available_currencies))
			{
				$configuration_id_name = 'AUTHORIZE_AIM_LOGIN_ID_'.$currency['iso_code'];
				$configuration_key_name = 'AUTHORIZE_AIM_KEY_'.$currency['iso_code'];
				
				$html .= '
				<table>
					<tr>
						<td>
							<p>'.$this->l('Credentials for').' <b>'.$currency['iso_code'].'</b> '.$this->l('currency').'</p>
							<label for="authorizeaim_login_id">'.$this->l('Login ID:').'</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_login_id_'.$currency['iso_code'].'" name="authorizeaim_login_id_'.$currency['iso_code'].'" value="'.Configuration::get($configuration_id_name).'" /></div>
							<label for="authorizeaim_key">'.$this->l('Key:').'</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_key_'.$currency['iso_code'].'" name="authorizeaim_key_'.$currency['iso_code'].'" value="'.Configuration::get($configuration_key_name).'" /></div>
						</td>
					</tr>
				<table><br />
				<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />';
			}
		
			$html .= '				
				<label for="authorizeaim_demo_mode">'.$this->l('Mode:').'</label>
				<div class="margin-form" id="authorizeaim_demo">
					<input type="radio" name="authorizeaim_demo_mode" value="0" style="vertical-align: middle;" '.(!Tools::getValue('authorizeaim_demo_mode', Configuration::get('AUTHORIZE_AIM_DEMO')) ? 'checked="checked"' : '').' />
					<span style="color: #080;">'.$this->l('Production').'</span>
					<input type="radio" name="authorizeaim_demo_mode" value="1" style="vertical-align: middle;" '.(Tools::getValue('authorizeaim_demo_mode', Configuration::get('AUTHORIZE_AIM_DEMO')) ? 'checked="checked"' : '').' />
					<span style="color: #900;">'.$this->l('Sandbox').'</span>
				</div>
				<label for="authorizeaim_cards">'.$this->l('Cards* :').'</label>
				<div class="margin-form" id="authorizeaim_cards">
					<input type="checkbox" name="authorizeaim_card_visa" '.(Configuration::get('AUTHORIZE_AIM_CARD_VISA') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/cards/visa.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_mastercard" '.(Configuration::get('AUTHORIZE_AIM_CARD_MASTERCARD') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/cards/mastercard.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_discover" '.(Configuration::get('AUTHORIZE_AIM_CARD_DISCOVER') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/cards/discover.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_ax" '.(Configuration::get('AUTHORIZE_AIM_CARD_AX') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/cards/ax.gif" alt="visa" />
				</div>

				<label for="authorizeaim_hold_review_os">'.$this->l('Order status:  "Hold for Review" ').'</label>
				<div class="margin-form">
								<select id="authorizeaim_hold_review_os" name="authorizeaim_hold_review_os">';
		// Hold for Review order state selection
		foreach ($orderStates as $os)
			$html .= '
				<option value="'.(int)$os['id_order_state'].'"'.((int)$os['id_order_state'] == (int)Configuration::get('AUTHORIZE_AIM_HOLD_REVIEW_OS') ? ' selected' : '').'>'.
			Tools::stripslashes($os['name']).
			'</option>'."\n";
		return $html.'</select></div>
				<br /><center><input type="submit" name="submitModule" value="'.$this->l('Update settings').'" class="button" /></center>
				<sub>*Subject to region</sub>
			</fieldset>
		</form>
		</div>';
	}

	public function hookPayment($params)
	{
		$currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
		if (!Validate::isLoadedObject($currency))
			return false;
		
		if (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off'))
		{
			$isFailed = Tools::getValue('aimerror');

			$cards = array();
			$cards['visa'] = Configuration::get('AUTHORIZE_AIM_CARD_VISA') == 'on';
			$cards['mastercard'] = Configuration::get('AUTHORIZE_AIM_CARD_MASTERCARD') == 'on';
			$cards['discover'] = Configuration::get('AUTHORIZE_AIM_CARD_DISCOVER') == 'on';
			$cards['ax'] = Configuration::get('AUTHORIZE_AIM_CARD_AX') == 'on';

			if (method_exists('Tools', 'getShopDomainSsl'))
				$url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$this->name.'/';
			else
				$url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';

			$this->context->smarty->assign('x_invoice_num', (int)$params['cart']->id);
			$this->context->smarty->assign('cards', $cards);
			$this->context->smarty->assign('isFailed', $isFailed);
			$this->context->smarty->assign('new_base_dir', $url);
			
			return $this->display(__FILE__, 'authorizeaim.tpl');
		}
	}

	public function hookHeader()
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.validate.creditcard2-1.0.1.js');
		else
			$this->context->controller->addJqueryPlugin('validate-creditcard');
	}

	/**
	 * Set the detail of a payment - Call before the validate order init
	 * correctly the pcc object
	 * See Authorize documentation to know the associated key => value
	 * @param array fields
	 */
	public function setTransactionDetail($response)
	{
		// If Exist we can store the details
		if (isset($this->pcc))
		{
			$this->pcc->transaction_id = (string)$response[6];

			// 50 => Card number (XXXX0000)
			$this->pcc->card_number = (string)substr($response[50], -4);

			// 51 => Card Mark (Visa, Master card)
			$this->pcc->card_brand = (string)$response[51];

			$this->pcc->card_expiration = (string)Tools::getValue('x_exp_date');

			// 68 => Owner name
			$this->pcc->card_holder = (string)$response[68];
		}
	}
}

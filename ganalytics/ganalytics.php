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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class GAnalytics extends Module
{
	public function __construct()
	{
		$this->name = 'ganalytics';
		$this->tab = 'analytics_stats';
		$this->version = '1.8.1';
		$this->author = 'PrestaShop';
		$this->displayName = 'Google Analytics';
		$this->module_key = 'fd2aaefea84ac1bb512e6f1878d990b8';

		parent::__construct();

		if ($this->id && !Configuration::get('GANALYTICS_ID'))
			$this->warning = $this->l('You have not yet set your Google Analytics ID');
		$this->description = $this->l('Integrate Google Analytics script into your shop');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		return (parent::install() && $this->registerHook('header') && $this->registerHook('orderConfirmation'));
	}

	public function getContent()
	{
		$output = '<h2>Google Analytics</h2>';
		if (Tools::isSubmit('submitGAnalytics'))
		{
			Configuration::updateValue('GANALYTICS_ID', Tools::getValue('ganalytics_id'));
			Configuration::updateValue('UGANALYTICS', Tools::getValue('universal_analytics'));
			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'
			</div>';
		}

		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="width2">
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Your username').'</label>
				<div class="margin-form">
					<input type="text" name="ganalytics_id" value="'.Tools::safeOutput(Tools::getValue('ganalytics_id', Configuration::get('GANALYTICS_ID'))).'" />
					<p class="clear">'.$this->l('Example:').' UA-1234567-1</p>
					<input type="checkbox" name="universal_analytics" '.(Tools::getValue('universal_analytics', Configuration::get('UGANALYTICS')) ? 'checked="checked"' : '').' />
					<p class="clear">'.$this->l('Universal Analytics Active').'</p>
				</div>
				<center><input type="submit" name="submitGAnalytics" value="'.$this->l('Update ID').'" class="button" /></center>
			</fieldset>
		</form>';

		$output .= '
		<fieldset class="space">
			<legend><img src="../img/admin/unknown.gif" alt="" class="middle" />'.$this->l('Help').'</legend>
			 <h3>'.$this->l('The first step of tracking e-commerce transactions is to enable e-commerce reporting for your website\'s profile.').'</h3>
			 '.$this->l('To enable e-Commerce reporting, please follow these steps:').'
			 <ol>
			 	<li>'.$this->l('Log in to your account').'</li>
			 	<li>'.$this->l('Click Edit next to the profile you would like to enable.').'</li>
			 	<li>'.$this->l('On the Profile Settings page, click Edit (next to Main Website Profile Information).').'</li>
			 	<li>'.$this->l('Change the e-Commerce Website radio button from No to Yes').'</li>
			</ol>
			<h3>'.$this->l('To set up your goals, enter Goal Information:').'</h3>
			<ol>
				<li>'.$this->l('Return to Your Account main page').'</li>
				<li>'.$this->l('Find the profile for which you will be creating goals, then click Edit').'</li>
				<li>'.$this->l('Select one of the 4 goal slots available for that profile, then click Edit').'</li>
				<li>'.$this->l('Enter the Goal URL. Reaching this page marks a successful conversion.').'</li>
				<li>'.$this->l('Enter the Goal name as it should appear in your Google Analytics account.').'</li>
				<li>'.$this->l('Turn on Goal.').'</li>
			</ol>
			<h3>'.$this->l('Then, define a funnel by following these steps:').'</h3>
			<ol>
				<li>'.$this->l('Enter the URL of the first page of your conversion funnel. This page should be a common page to all users working their way towards your Goal.').'</li>
				<li>'.$this->l('Enter a Name for this step.').'</li>
				<li>'.$this->l('If this step is a required step in the conversion process, mark the checkbox to the right of the step.').'</li>
				<li>'.$this->l('Continue entering goal steps until your funnel has been completely defined. You may enter up to 10 steps, or only one step.').'</li>
			</ol>
			'.$this->l('Finally, configure Additional settings by following the steps below:').'
			<ol>
				<li>'.$this->l('If the URLs entered above are case sensitive, mark the checkbox.').'</li>
				<li>'.$this->l('Select the appropriate goal Match Type. (').'<a href="http://www.google.com/support/analytics/bin/answer.py?answer=72285">'.$this->l('Learn more').'</a> '.$this->l('about Match Types and how to choose the appropriate goal Match Type for your goal.)').'</li>
				<li>'.$this->l('Enter a Goal value. This is the value used in Google Analytics\' ROI calculations.').'</li>
				<li>'.$this->l('Click Save Changes to create this Goal and funnel, or Cancel to exit without saving.').'</li>
			</ol>
			<h3>'.$this->l('Demonstration: The order process').'</h3>
			<ol>
				<li>'.$this->l('After having enabled your e-commerce reports and selected the respective profile enter \'order-confirmation.php\' as the targeted page URL.').'</li>
				<li>'.$this->l('Name this goal (for example \'Order process\')').'</li>
				<li>'.$this->l('Activate the goal').'</li>
				<li>'.$this->l('Add \'product.php\' as the first page of your conversion funnel').'</li>
				<li>'.$this->l('Give it a name (for example, \'Product page\')').'</li>
				<li>'.$this->l('Do not mark the \'required\' checkbox because the customer could be visiting directly from an \'adding to cart\' button such as in the homefeatured block on the homepage.').'</li>
				<li>'.$this->l('Continue by entering the following URLs as goal steps:').'
					<ul>
						<li>order/step0.html '.$this->l('(required)').'</li>
						<li>authentication.php '.$this->l('(required)').'</li>
						<li>order/step1.html '.$this->l('(required)').'</li>
						<li>order/step2.html '.$this->l('(required)').'</li>
						<li>order/step3.html '.$this->l('(required)').'</li>
					</ul>
				</li>
				<li>'.$this->l('Check the \'Case sensitive\' option').'</li>
				<li>'.$this->l('Save this new goal').'</li>
			</ol>
		</fieldset>';

		return $output;
	}

	public function hookHeader($params)
	{
		if ((method_exists('Language', 'isMultiLanguageActivated') && Language::isMultiLanguageActivated())
			|| Language::countActiveLanguages() > 1
		)
			$multilang = (string)Tools::getValue('isolang').'/';
		else
			$multilang = '';

		$default_meta_order = Meta::getMetaByPage('order', $this->context->language->id);
		if (strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.'order.php') === 0 || strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.$multilang.$default_meta_order['url_rewrite']) === 0)
			$this->context->smarty->assign('pageTrack', '/order/step'.(int)Tools::getValue('step').'.html');

		$this->context->smarty->assign('ganalytics_id', Configuration::get('GANALYTICS_ID'));
		$this->context->smarty->assign('universal_analytics', Configuration::get('UGANALYTICS'));
		$this->context->smarty->assign('isOrder', false);

		return $this->display(__FILE__, 'views/templates/hook/header.tpl');
	}

	public function hookFooter($params)
	{
		// for retrocompatibility
		if (!$this->isRegisteredInHook('header'))
			$this->registerHook('header');
	}

	public function hookOrderConfirmation($params)
	{
		// Setting parameters
		$parameters = Configuration::getMultiple(array('PS_LANG_DEFAULT'));

		$order = $params['objOrder'];
		if (Validate::isLoadedObject($order))
		{
			$delivery_address = new Address((int)$order->id_address_delivery);

			$conversion_rate = 1;
			$currency = new Currency((int)$order->id_currency);

			if ($order->id_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
				$conversion_rate = (int)$currency->conversion_rate;

			$state_name = '';
			if ((int)$delivery_address->id_state > 0)
			{
				$state = New State($delivery_address->id_state);
				$state_name = $state->name;
			}

			// Order general information
			$trans = array(
				'id' => (int)$order->id,
				'store' => htmlentities(Configuration::get('PS_SHOP_NAME')),
				'total' => Tools::ps_round((float)$order->total_paid / (float)$conversion_rate, 2),
				'tax' => $order->getTotalProductsWithTaxes() - $order->getTotalProductsWithoutTaxes(),
				'shipping' => Tools::ps_round((float)$order->total_shipping / (float)$conversion_rate, 2),
				'city' => addslashes($delivery_address->city),
				'state' => $state_name,
				'country' => addslashes($delivery_address->country),
				'currency' => $currency->iso_code
			);

			// Product information
			$products = $order->getProducts();
			$items = array();
			foreach ($products as $product)
			{
				$category = Db::getInstance()->getRow('
								SELECT name FROM `'._DB_PREFIX_.'category_lang` , '._DB_PREFIX_.'product 
								WHERE `id_product` = '.(int)$product['product_id'].' AND `id_category_default` = `id_category`
								AND `id_lang` = '.(int)$parameters['PS_LANG_DEFAULT']);

				$items[] = array(
					'OrderId' => (int)$order->id,
					'SKU' => addslashes($product['product_id']),
					'Product' => addslashes($product['product_name']),
					'Category' => addslashes($category['name']),
					'Price' => Tools::ps_round((float)$product['product_price_wt'] / (float)$conversion_rate, 2),
					'Quantity' => addslashes((int)$product['product_quantity'])
				);
			}
			$ganalytics_id = Configuration::get('GANALYTICS_ID');

			$this->context->smarty->assign('items', $items);
			$this->context->smarty->assign('trans', $trans);
			$this->context->smarty->assign('ganalytics_id', $ganalytics_id);
			$this->context->smarty->assign('universal_analytics', Configuration::get('UGANALYTICS'));
			$this->context->smarty->assign('isOrder', true);

			return $this->display(__FILE__, 'views/templates/hook/header.tpl');
		}
	}
}

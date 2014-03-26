<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

if (!defined('_PS_VERSION_'))
	exit;

class GiveItShippingPricesView {
	private $module_instance;

	private $context;

	public function __construct($module_instance = null)
	{
		$this->module_instance = ($module_instance) ? $module_instance : Module::getInstanceByName('giveit');
		$this->context = Context::getContext();
	}

	public function getPageContent()
	{
		$ship_rules = GiveItShipping::getShippingRules();
		$page_url = GiveIt::CURRENT_INDEX.Tools::getValue('token').'&configure='.Tools::getValue('configure').'&menu='.Tools::getValue('menu');
		if ($id_shipping = Tools::getValue('edit_rule'))
			$page_url.='&edit_rule='.$id_shipping;
		
		$default_language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		
		$this->context->smarty->assign(array(
			'shipping_rules' => $ship_rules,
			'page_url' => $page_url,
			'default_language_notice' => $default_language->iso_code
		));
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/shipping_prices_ps16.tpl');
		else
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/shipping_prices.tpl');
	}

}

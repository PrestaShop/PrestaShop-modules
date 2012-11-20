<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 7091 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paysafecard.php');

$module = new PaysafeCard();

// Use for 1.4 / 1,5 to display the front
class PSC_Display extends FrontController
{
	public $module;

	// Assign template, on 1.4 create it else assign for 1.5
	public function setTemplate($template)
	{
		if (_PS_VERSION_ >= '1.5')
			parent::setTemplate($template);
		else
			$this->template = $template;
	}

	// Need module to get the error message
	public function __construct($module)
	{
		parent::__construct();

		$this->module = $module;
	}

	// Overload displayContent for 1.4
	public function displayContent()
	{
		echo Context::getContext()->smarty->fetch($this->template);
	}

	// Assign smarty content
	public function displayTemplate()
	{
		$this->setTemplate(dirname(__FILE__).'/redirect.tpl');
		Context::getContext()->smarty->assign('psc_error_message', $this->module->getL('cant_create_dispo'));
	}
}

$url = (_PS_VERSION_ < '1.5') ? 'order.php?step=3' : 'index.php?controller=order&step=3';

if (!$cart->id OR $cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$module->active)
	Tools::redirect($url);
	
$currency = new Currency($cart->id_currency);
if (!$module->isCurrencyActive($currency->iso_code))
	Tools::redirect($url);

$result = $module->createDisposition($cart);

if ($result['return_code'] != 0)
{
	$controller = new PSC_Display($module);
	$controller->displayTemplate();
	$controller->run();
}
else
	Tools::redirectLink($result['message']);


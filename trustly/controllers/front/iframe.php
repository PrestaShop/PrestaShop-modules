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

// This is the 1.4.x version of the controller
if (isset($_GET['compat14']))
{
	require(dirname(__FILE__).'/../../../../config/config.inc.php');
	require(dirname(__FILE__).'/../../../../init.php');
	require(dirname(__FILE__).'/../../../../header.php');
	require_once(_PS_MODULE_DIR_.'trustly/trustly.php');
	
	$url = '';
	$errors = array();
	$trustly = new Trustly();
	try {
		$url = $trustly->retrievePaymentUrl();
	} catch (Exception $e) {
		$errors[] = $e->getMessage();
	}
	Context::getContext()->smarty->assign(array(
		'nbProducts' => Context::getContext()->cart->nbProducts(),
		'trustly_iframe_url' => $url,
		'errors' => $errors
	));
	Context::getContext()->smarty->display(dirname(__FILE__).'/../../views/templates/front/iframe.tpl');
	require(dirname(__FILE__).'/../../../../footer.php');
	die;
}

// This is the 1.5.x version of the controller
class TrustlyIframeModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	
	public $display_column_left = false;

	public function initContent()
	{
		parent::initContent();

		require_once(_PS_MODULE_DIR_.'trustly/trustly.php');
		
		$url = '';
		$trustly = new Trustly();
		try {
			$url = $trustly->retrievePaymentUrl();
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
		}
		$this->context->smarty->assign(array(
			'nbProducts' => $this->context->cart->nbProducts(),
			'trustly_iframe_url' => $url
		));
		$this->setTemplate('iframe.tpl');
	}
}

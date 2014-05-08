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

class OgoneConfirmationModuleFrontController extends ModuleFrontController
{
	public function initContent()
    {   parent::initContent();

        $this->context = Context::getContext();     
     
		$ogone = new Ogone();
		$id_module = $ogone->id;
		$id_cart = Tools::getValue('orderID');
		$key = Db::getInstance()->getValue('SELECT secure_key FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)$this->context->customer->id);

		$ogone_link = $this->context->link->getPageLink('order-confirmation');

		$this->context->smarty->assign(
			array(
				'id_module' => $id_module,
				'id_cart' => $id_cart,
				'key' => $key,
				'ogone_link' => $ogone_link
			)
		);

        $this->setTemplate('waiting.tpl');
    }
}

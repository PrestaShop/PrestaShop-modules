<?php
/*
* 2007-2011 PrestaShop
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
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Tax extends TaxCore
{
	/**
	 * Return the product tax
	 *
	 * @param integer $id_product
	 * @param integer $id_address
	 * @return Tax Rate
	 */
	public static function getProductTaxRate($id_product, $id_address = null, $getCarrierRate = false)
	{
		include_once(_PS_ROOT_DIR_.'/modules/avalaratax/avalaratax.php');

		/* Instanciate the Avalara module and check if active */
		$avalara = new AvalaraTax();
		if (!$avalara->active)
			return parent::getProductTaxRate($id_product, $id_address, $getCarrierRate);

		/* With Avalara, we disable the tax for non logged users */
		if (!(int)$id_address)
			return 0.;

		$region = Db::getInstance()->getValue('SELECT s.`iso_code`
									FROM '._DB_PREFIX_.'address a
									LEFT JOIN '._DB_PREFIX_.'state s ON (s.`id_state` = a.`id_state`)
									WHERE a.`id_address` = '.(int)$id_address);

		/* If the Merchant does not want to calculate taxes outside his state and we are outside the state, we return 0 */
		if ((!empty($region) && $region != Configuration::get('AVALARATAX_STATE') && !Configuration::get('AVALARATAX_TAX_OUTSIDE')))
			return 0.;

		return (float)Db::getInstance()->getValue('SELECT ac.`tax_rate`
		FROM '._DB_PREFIX_.'avalara_'.($getCarrierRate ? 'carrier' : 'product').'_cache ac
		WHERE ac.`id_'.($getCarrierRate ? 'carrier' : 'product').'` = '.(int)$id_product.'
		AND ac.`region` = \''.pSQL($region).'\'');
	}

	public static function getCarrierTaxRate($id_carrier, $id_address = NULL)
	{
		return (float)self::getProductTaxRate($id_carrier, $id_address, true);
	}
}

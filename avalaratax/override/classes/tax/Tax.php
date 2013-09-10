<?php

class Tax extends TaxCore
{
	/**
	 * Return the product tax
	 *
	 * @param integer $id_product
	 * @param integer $id_address
	 * @param boolean $getCarrierRate
	 * @return Tax Rate
	 */
	public static function getProductTaxRate($id_product, $id_address = null, Context $context = null, $getCarrierRate = false)
	{
		if ($context == null)
			$context = Context::getContext();

		include_once(_PS_ROOT_DIR_.'/modules/avalaratax/avalaratax.php');

		/* Instanciate the Avalara module and check if active */
		$avalara = new AvalaraTax();
		if (!$avalara->active)
			return parent::getProductTaxRate((int)$id_product, (int)$id_address, $context);

		/* With Avalara, we disable the tax for non logged users */
		if (!(int)$id_address)
			return 0.;

		$region = Db::getInstance()->getValue('
		SELECT s.`iso_code`
		FROM '._DB_PREFIX_.'address a
		LEFT JOIN '._DB_PREFIX_.'state s ON (s.`id_state` = a.`id_state`)
		WHERE a.`id_address` = '.(int)$id_address);

		/* If the Merchant does not want to calculate taxes outside his state and we are outside the state, we return 0 */
		if ((!empty($region) && $region != Configuration::get('AVALARATAX_STATE') && !Configuration::get('AVALARATAX_TAX_OUTSIDE')))
			return 0.;

		/* Retrieve the tax rate from the cache table or populate the cache if necessary */
		$res = self::getTaxFromCache((int)$id_product, (int)$id_address, $region, $getCarrierRate);
		if (!Db::getInstance()->NumRows())
		{
			include_once(_PS_ROOT_DIR_.'/modules/avalaratax/CacheTools.php');

			if ($getCarrierRate)
				CacheTools::updateCarrierTax($avalara, $context->cart, (int)$id_address, $region, true);
			else
				CacheTools::updateProductsTax($avalara, $context->cart, (int)$id_address, $region, true);
			
			$res = self::getTaxFromCache((int)$id_product, (int)$id_address, $region, $getCarrierRate);
		}

		return $res;
	}
	
	public static function getTaxFromCache($id_product, $id_address = null, $region = null, $getCarrierRate = false)
	{
		return (float)Db::getInstance()->getValue('
		SELECT ac.`tax_rate`
		FROM '._DB_PREFIX_.'avalara_'.($getCarrierRate ? 'carrier' : 'product').'_cache ac
		WHERE ac.`id_'.($getCarrierRate ? 'carrier' : 'product').'` = '.(int)$id_product.'
		'.($getCarrierRate ? '' : ' AND id_address = '.(int)$id_address).'
		AND ac.`region` = \''.pSQL($region).'\'');
	}	

	public static function getCarrierTaxRate($id_carrier, $id_address = NULL)
	{
		return (float)self::getProductTaxRate($id_carrier, $id_address, null, true);
	}
}

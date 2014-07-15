<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.7.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'gointerpay/gointerpay.php');

$interpay = new GoInterpay();
if ($interpay->active)
{
	/* To check if an address is US based on the Front-office */
	if (Tools::getValue('check_address') == 1 && Tools::getValue('id_address') != '')
	{
		$address = new Address((int)Tools::getValue('id_address'));
		if (Validate::isLoadedObject($address) && $address->id_customer == Context::getContext()->customer->id)
		{
			if ((int)Configuration::get('PS_COUNTRY_DEFAULT') == $address->id_country)
			{
				$flag = true;
				echo '1';
			}
				
			/* Check that the selected ship to country is available with Interpay */
			$ship_to_iso_code = Country::getIsoById((int)$address->id_country);
			$ship_to_supported_by_interpay = Db::getInstance()->getValue('SELECT country_code FROM '._DB_PREFIX_.'gointerpay_countries WHERE country_code = \''.pSQL($ship_to_iso_code).'\'');
			if ($ship_to_supported_by_interpay != $ship_to_iso_code)
			{
				$flag = true;
				echo '-1';
			}
				
			if (!isset($flag))
				echo '0';			
		}
		die;
	}
	
	/* To retrieve States for the Configuration section of the module */
	else
	{
		$id_country = (int)Tools::getValue('id_country');
		$states[(int)$id_country] = State::getStatesByIdCountry((int)$id_country);
		echo count($states[$id_country]) ? Tools::jsonEncode($states) : 0;
	}
}
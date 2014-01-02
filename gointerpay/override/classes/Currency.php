<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.7.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

class Currency extends CurrencyCore
{
	public static function refreshCurrencies()
	{
		parent::refreshCurrencies();			
		
		if (!$feed = Tools::simplexml_load_file(_GOINTERPAY_RATES_URL_.Configuration::get('GOINTERPAY_MERCHANT_ID')))
			return Tools::displayError('Cannot parse Interpay feed.');
			
		foreach ($feed->rateOffer as $currency)
		{
			if ($currency->buyCurrency != 'USD')
				continue;
			$currency_to_update_id = Currency::getIdByIsoCode($currency->sellCurrency);
			if ($currency_to_update_id)
			{
				$currency_to_update = new Currency((int)$currency_to_update_id);
				if (Validate::isLoadedObject($currency_to_update))
				{
					$currency_to_update->conversion_rate = (float)$currency->rate;
					$currency_to_update->update();
			    	Db::getInstance()->Execute('
					UPDATE '._DB_PREFIX_.'currency
					SET rateoffer_id = \''.pSQL($currency->id).'\', expiry = \''.pSQL($currency->expiry).'\'
					WHERE id_currency = '.(int)$currency_to_update_id);
				}
			}
		}

		Configuration::updateValue('GOINTERPAY_CURRENCY_UPDATE', time());		
	}
}
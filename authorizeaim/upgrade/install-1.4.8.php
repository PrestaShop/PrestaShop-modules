<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_4_8($object)
{
	$upgrade_version = '1.4.8';

	$object->upgrade_detail[$upgrade_version] = array();

	// Variables name for Login and Key have now the currency
	if(Configuration::get('AUTHORIZE_AIM_LOGIN_ID') && Configuration::get('AUTHORIZE_AIM_KEY'))
	{
		$currencies = Currency::getCurrencies(false, true);
		foreach ($currencies as $currency)
		{
			if (in_array($currency['iso_code'], $object->aim_available_currencies))
			{
				$configuration_id_name = 'AUTHORIZE_AIM_LOGIN_ID_'.$currency['iso_code'];
				$configuration_key_name = 'AUTHORIZE_AIM_KEY_'.$currency['iso_code'];

				Configuration::updateValue($configuration_id_name, Configuration::get('AUTHORIZE_AIM_LOGIN_ID'));
				Configuration::updateValue($configuration_key_name, Configuration::get('AUTHORIZE_AIM_KEY'));
			}
		}
	}

	Configuration::deleteByName('AUTHORIZE_AIM_LOGIN_ID');
	Configuration::deleteByName('AUTHORIZE_AIM_KEY');

	Configuration::updateValue('AUTHORIZE_AIM', $upgrade_version);
	return true;
}

<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_4_11($object)
{
	if ((bool)Configuration::get('AUTHORIZE_AIM_DEMO'))
		return Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 1)
			&& Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 0)
			&& Configuration::deleteByName('AUTHORIZE_AIM_DEMO');
	else
		return Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 0)
			&& Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 1)
			&& Configuration::deleteByName('AUTHORIZE_AIM_DEMO');
}
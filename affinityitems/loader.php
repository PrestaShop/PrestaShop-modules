<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

function _autoload_require($path)
{
	foreach (glob(dirname(__FILE__).$path) as $class)
	{
		if (!preg_match('/index.php/i', $class))
			require_once($class);
	}
}

	/*
		load core
	*/
		_autoload_require('/sdk/interface/*.php');
		_autoload_require('/adapter/*.php');
		_autoload_require('/sdk/core/*.php');

	/*
		load module
	*/
		_autoload_require('/sdk/class/repository/*.php');
		_autoload_require('/sdk/class/synchronize/*.php');
		_autoload_require('/sdk/class/request/*.php');
		_autoload_require('/sdk/class/recommendation/*.php');
		_autoload_require('/sdk/class/abtesting/*.php');
		_autoload_require('/sdk/class/notification/*.php');
		_autoload_require('/*.php');

		?>
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

include( '../../config/config.inc.php' );
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/TntWebService.php");

$postal = htmlentities(Tools::getValue('code'));

$cities = array();

if ($postal == '75000')
{
	for ($i = 1; $i <= 20; $i++)
	{
		if ($i < 10)
			$nb = '0'.$i;
		else
			$nb = $i;
		echo '<option value="PARIS '.$nb.'">PARIS '.$nb.'</option>';
	}
}
else if ($postal == '69000')
{
	for ($i = 1; $i < 10; $i++)
		echo '<option value="LYON 0'.$i.'">LYON 0'.$i.'</option>';
}
else if ($postal == '13000')
{
	for ($i = 1; $i <= 16; $i++)
	{
		if ($i < 10)
			$nb = '0'.$i;
		else
			$nb = $i;
		echo '<option value="MARSEILLE '.$nb.'">MARSEILLE '.$nb.'</option>';
	}
}
else
{
	try
	{
		if (Tools::getValue('id_shop'))
			$tntWebService = new TntWebService(Tools::safeOutput(Tools::getValue('id_shop')));
		else
			$tntWebService = new TntWebService();
		$cities = $tntWebService->getCity($postal);
	} 
	catch( SoapFault $e ) 
	{
		$erreur = $e->faultstring;
	}
	catch( Exception $e ) 
	{
		$erreur = "Problem : follow failed";
	}
	if (!isset($erreur))
	{
		if (isset($cities->City) && is_array($cities->City))
			{
				foreach ($cities->City as $v)
					echo '<option value="'.$v->name.'">'.$v->name.'</option>';
			}
		else if (isset($cities->City))
			echo '<option value="'.$cities->City->name.'">'.$cities->City->name.'</option>';
		else
			echo '<option value="">Aucune ville</option>';
	}
	else if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
		echo 'account';
	else
		echo '<option>ERROR</option>';
}


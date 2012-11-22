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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/ogone.php');

$ogone = new Ogone();

/* First we need to check var presence */
$neededVars = array('orderID', 'amount', 'currency', 'PM', 'ACCEPTANCE', 'STATUS', 'CARDNO', 'PAYID', 'NCERROR', 'BRAND', 'SHASIGN');
$params = '<br /><br />'.$ogone->l('Received parameters:').'<br /><br />';

foreach ($neededVars as $k)
	if (!isset($_GET[$k]))
		die($ogone->l('Missing parameter:').' '.$k);
	else
		$params .= Tools::safeOutput($k).' : '.Tools::safeOutput($_GET[$k]).'<br />';

/* Then, load the customer cart and perform some checks */
$cart = new Cart((int)($_GET['orderID']));
if (Validate::isLoadedObject($cart))
{
	/* Fist, check for a valid SHA-1 signature */
	$ogoneParams = array();
	$ignoreKeyList = $ogone->getIgnoreKeyList();

	foreach ($_GET as $key => $value)
		if (strtoupper($key) != 'SHASIGN' && $value != '' && !in_array($key, $ignoreKeyList))
		$ogoneParams[strtoupper($key)] = $value;

	ksort($ogoneParams);
	$shasign = '';
	foreach ($ogoneParams as $key => $value)
		$shasign .= strtoupper($key).'='.$value.Configuration::get('OGONE_SHA_OUT');
	$sha1 = strtoupper(sha1($shasign));	

	if ($sha1 == $_GET['SHASIGN'])
	{
		switch ($_GET['STATUS'])
		{
			case 1:
				/* Real error or payment canceled */
				$ogone->validate((int)$_GET['orderID'], Configuration::get('PS_OS_ERROR'), 0, Tools::safeOutput($_GET['NCERROR']).$params, Tools::safeOutput($_GET['secure_key']));
				break;
			case 2:
				/* Real error - authorization refused */
				$ogone->validate((int)$_GET['orderID'], Configuration::get('PS_OS_ERROR'), 0, $ogone->l('Error (auth. refused)').'<br />'.Tools::safeOutput($_GET['NCERROR']).$params, Tools::safeOutput($_GET['secure_key']));
				break;
			case 5:
			case 9:
				/* Payment OK */
				$ogone->validate((int)$_GET['orderID'], Configuration::get('PS_OS_PAYMENT'), (float)$_GET['amount'], $ogone->l('Payment authorized / OK').$params, Tools::safeOutput($_GET['secure_key']));
				break;
			case 6:
			case 7:
			case 8:
				// Payment canceled later
				if ($id_order = (int)Order::getOrderByCartId((int)$_GET['orderID']))
				{
					// Update the amount really paid
					$order = new Order((int)$id_order);
					$order->total_paid_real = 0;
					$order->update();
					
					// Send a new message and change the state
					$history = new OrderHistory();
					$history->id_order = (int)$id_order;
					$history->changeIdOrderState(Configuration::get('PS_OS_ERROR'), (int)$id_order);
					$history->addWithemail(true, array());
				}
				break;
			default:
				$ogone->validate((int)$_GET['orderID'], Configuration::get('PS_OS_ERROR'), (float)($_GET['amount']), $ogone->l('Unknown status:').' '.Tools::safeOutput($_GET['STATUS']).$params, Tools::safeOutput($_GET['secure_key']));
		}
		exit;
	}
	else
	{
		$message = $ogone->l('Invalid SHA-1 signature').'<br />'.$ogone->l('SHA-1 given:').' '.Tools::safeOutput($_GET['SHASIGN']).'<br />'.
		$ogone->l('SHA-1 calculated:').' '.Tools::safeOutput($sha1).'<br />'.$ogone->l('Plain key:').' '.Tools::safeOutput($shasign);
		$ogone->validate((int)$_GET['orderID'], Configuration::get('PS_OS_ERROR'), 0, $message.'<br />'.$params, Tools::safeOutput($_GET['secure_key']));
	}
}

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

/*
 * File called by ajax. It's like a controller, you have to send the
 * method name of the webservice and implement it.
 * Each Name method allow to instanciate an object containing
 * methods to manage correctly the data and name fields
 */

// Clean displayed content for Admin ajax query 
ob_clean();

// Front Ajax query, need the front cookie and MR class
// When it's back query, the PS core made the work
if (!defined('_PS_ADMIN_DIR_'))
{
	require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));
	require_once(realpath(dirname(__FILE__).'/../../init.php'));
	require_once(dirname(__FILE__).'/mondialrelay.php');
}

$mondialrelay = isset($this) ? $this : new Mondialrelay();

require_once(dirname(__FILE__).'/classes/MRCreateTickets.php');
require_once(dirname(__FILE__).'/classes/MRGetTickets.php');
require_once(dirname(__FILE__).'/classes/MRGetRelayPoint.php');
require_once(dirname(__FILE__).'/classes/MRRelayDetail.php');
require_once(dirname(__FILE__).'/classes/MRManagement.php');

// Can't use Tools at this time... Need to know if _PS_ADMIN_DIR_ has to be defined
$method = Tools::getValue('method');
$token = Tools::getValue('mrtoken');

// Access page List liable to the generated token
$accessPageList = array(
	MondialRelay::getToken('front') => array(
		'MRGetRelayPoint',
		'addSelectedCarrierToDB'
	),
	MondialRelay::getToken('back') => array(
		'MRGetTickets',
		'MRCreateTickets',
		'MRDeleteHistory',
		'uninstallDetail',
		'DeleteHistory'
	)
);

$params = array();
$result = array();

// If the method name associated to the token received doesn't match with
// the list, then we kill the request
if (!isset($accessPageList[$token]) || !in_array($method, $accessPageList[$token]))
	exit();

// Method name allow to instanciate his object to properly call the
// implemented interface method and do his job
switch($method)
{
	case 'MRCreateTickets':
		$params['orderIdList'] = Tools::getValue('order_id_list');
		$params['totalOrder'] = Tools::getValue('numSelected');
		$params['weightList'] = Tools::getValue('weight_list');
		break;
	case 'MRGetTickets':
		$params['detailedExpeditionList'] = Tools::getValue('detailedExpeditionList');
		break;
	case 'DeleteHistory':
		$params['historyIdList'] = Tools::getValue('history_id_list');
		break;
	case 'uninstallDetail':
		$params['action'] = Tools::getValue('action');
		break;
	case 'MRGetRelayPoint':
		$params['id_carrier'] = Tools::getValue('id_carrier');
		$params['weight'] = Context::getContext()->cart->getTotalWeight();
		$params['id_address_delivery'] = Context::getContext()->cart->id_address_delivery;
		break;
	case 'addSelectedCarrierToDB':
		$params['id_carrier'] = Tools::getValue('id_carrier');
		$params['id_cart'] = Context::getContext()->cart->id;
		$params['id_customer'] = Context::getContext()->customer->id;
		$params['id_mr_method'] = Tools::getValue('id_mr_method');
		$params['relayPointInfo'] = Tools::getValue('relayPointInfo');
		break;
	default:
}

// Try to instanciate the method object name and call the necessaries method
try
{
	if (class_exists($method, false))
	{
		// $this is the current mondialrelay object loaded when use in BO. Use for perf
		$obj = new $method($params, $mondialrelay);

		// Verify that the class implement correctly the interface
		// Else use a Management class to do some ajax stuff
		if (($obj instanceof IMondialRelayWSMethod))
		{
			$obj->init();
			$obj->send();
			$result = $obj->getResult();
		}
		unset($obj);
	}
	else if (($management = new MRManagement($params)) &&
		method_exists($management, $method))
		$result = $management->{$method}();
	else
		throw new Exception('Method Class : '.$method.' can\'t be found');
	unset($management);
}
catch(Exception $e)
{
	echo MRTools::jsonEncode(array('other' => array('error' => array($e->getMessage()))));
	exit(-1);
}
echo MRTools::jsonEncode($result);
exit(0);

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

class AEAjaxAdapter {

	public static function initHosts()
	{
		$hosts = unserialize(AEAdapter::getLocalHosts());
		if (!is_array($hosts))
			$hosts = array();
		if (!in_array($_SERVER['SERVER_ADDR'], $hosts))
		{
			array_push($hosts, $_SERVER['SERVER_ADDR']);
			AEAdapter::setLocalHosts(serialize($hosts));
		}
	}

	public static function authentication()
	{
		if (!Tools::getValue('aetoken') || Tools::getValue('aetoken') != AEAdapter::getBackOfficeToken())
			die('ERROR');

		$ini = parse_ini_file(dirname(__FILE__).'/../configuration/configuration.ini');

		$ret = array();

		if (Tools::getIsset('email') && Tools::getIsset('password'))
		{
			$customer = new stdClass();
			$customer->siteName = AEAdapter::getShopName();
			$customer->email = Tools::safeOutput(Tools::getValue('email'));
			$customer->password = Tools::safeOutput(Tools::getValue('password'));

			if (_PS_VERSION_ >= '1.5')
			{
				if (!AELibrary::isEmpty(ShopUrl::getMainShopDomain(Shop::getContextShopID(true))))
					$customer->domain = ShopUrl::getMainShopDomain(Shop::getContextShopID(true));
				else
					$ret['_errorMessage'] = 'We can find your shop URL';
			}
			else
				$customer->domain = Tools::getHttpHost();

			$customer->origin = $ini['origin'];
			$customer->platform = 'Prestashop';
			$customer->platformVersion = _PS_VERSION_;
			$customer->ip = $_SERVER['SERVER_ADDR'];

			if (Tools::safeOutput(Tools::getValue('action')) == 'register' && Tools::getIsset('confirmPassword') && Tools::getIsset('activity')
				&& Tools::getIsset('firstname') && Tools::getIsset('lastname'))
				{
				$customer->firstname = Tools::safeOutput(Tools::getValue('firstname'));
				$customer->lastname = Tools::safeOutput(Tools::getValue('lastname'));
				$customer->activity = Tools::safeOutput(Tools::getValue('activity'));
				if (Tools::safeOutput(Tools::getValue('password')) == Tools::safeOutput(Tools::getValue('confirmPassword')))
				{
					$request = new CustomerRequest($customer);
					$response = $request->registerCustomer();
				}
		}
		else if (Tools::safeOutput(Tools::getValue('action')) == 'login')
		{
			$customer->activity = AEAdapter::getActivity();
			$request = new CustomerRequest($customer);
			$response = $request->loginCustomer();
		}

		if ($response)
		{
			if ($response->_ok == 'true')
			{
				AEAdapter::authentication($response->email, $response->password,
					$response->siteId, $response->securityKey);
				self::initHosts();
			}
			else
				$ret['_errorMessage'] = $response->_errorMessage;
			$ret['_ok'] = $response->_ok;
		}
		else
			$ret['_ok'] = false;
	}

	return Tools::jsonEncode($ret);
}

public static function setAbTestingPercentage()
{
	if (!Tools::getValue('aetoken') || Tools::getValue('aetoken') != AEAdapter::getBackOfficeToken())
		die('ERROR');

	$response = array();

	if (Tools::getIsset('percentage'))
	{
		try {
			AEAdapter::setAbTestingPercentage(Tools::safeOutput(Tools::getValue('percentage')));
			$response['_ok'] = true;
		} catch (Exception $e)
		{
			AELogger::log('[ERROR]', $e->getMessage());
			$response['_ok'] = false;
		}
	}

	return Tools::jsonEncode($response);
}

public static function setHosts()
{
	if (!Tools::getValue('aetoken') || Tools::getValue('aetoken') != AEAdapter::getBackOfficeToken())
		die('ERROR');

	$response = array();

	if (Tools::getIsset('ip') && Tools::getIsset('type'))
	{
		if (Tools::safeOutput(Tools::getValue('type')) == 'local')
		{
			try {
				$hosts = unserialize(AEAdapter::getLocalHosts());
				if (!is_array($hosts))
					$hosts = array();
				if (preg_match(AELibrary::$check_ip, Tools::safeOutput(Tools::getValue('ip'))))
				{
					array_push($hosts, Tools::getValue('ip'));
					AEAdapter::setLocalHosts(serialize($hosts));
					$response['_ok'] = true;
				}
				else
					$response['_ok'] = false;
			} catch(Exception $e)
			{
				$response['_ok'] = false;
				AELogger::log('[ERROR]', $e->getMessage());
			}
		}
	}
	else if (Tools::getIsset('ipList') && Tools::getIsset('type'))
	{
		if (Tools::safeOutput(Tools::getValue('type')) == 'remote')
		{
			try {
				$host_request = new HostRequest(Tools::getValue('ipList'));
				if ($host_request->post())
					$response['_ok'] = true;
				else
					$response['_ok'] = false;
			} catch(Exception $e)
			{
				$response['_ok'] = false;
				AELogger::log('[ERROR]', $e->getMessage());
			}
		}
	}

	return Tools::jsonEncode($response);
}

public static function resetSync()
{
	Synchronize::setLock(0);
	Synchronize::setStartDate('');
}

public static function launchSync()
{
	$response = array();
	try {
		if (!AELibrary::isEmpty(AEAdapter::getSiteId())
			&& !AELibrary::isEmpty(AEAdapter::getSecurityKey()))
			{
			$sync = new Synchronize();
			$sync->syncElement();
		}
		$response['_ok'] = true;
	} catch (Exception $e)
	{
		AELogger::log('[ERROR]', $e->getMessage());
		$response['_ok'] = false;
	}
}

public static function checkSyncDiff()
{
	return (((time() - Synchronize::getStartDate()) > (AEAdapter::getSyncDiff() * 60)));
}

public static function synchronize()
{
	$response = array();

	if (Tools::getIsset('synchronize'))
	{
		if ((bool)Synchronize::getLock())
		{
			if (!AELibrary::isEmpty(Synchronize::getStartDate()))
			{
				if (self::checkSyncDiff())
				{
					self::resetSync();
					self::launchSync();
				}
			}
		}
		else
		{
			if (!AELibrary::isEmpty(Synchronize::getStartDate()))
			{
				if (self::checkSyncDiff())
					self::launchSync();
			}
			else
				self::launchSync();
		}
	}
	if (Tools::getIsset('getInformation'))
	{
		$response['_ok'] = true;
		$response['_step'] = ((int)Synchronize::getStep() + 1);
		$response['_lock'] = (bool)Synchronize::getLock();
		$response['_lastStart'] = Synchronize::getStartDate();
		$response['_percentage'] = (((int)Synchronize::getStep() + 1) * (100 / 5));
	}

	return Tools::jsonEncode($response);
}

public static function postAction()
{
	if (Tools::getIsset('productId') && Tools::getIsset('action'))
	{

		$instance = new AffinityItems();
		$person = $instance->getPerson();

		$action = new stdClass();
		$action->productId = (int)Tools::getValue('productId');
		if (Tools::getIsset('recoType'))
			$action->recoType = Tools::safeOutput(Tools::strtoupper(Tools::getValue('recoType')));
		$action->context = Tools::safeOutput(Tools::getValue('action'));

		if ($person instanceof stdClass)
			exit;
		else if ($person instanceof AEGuest)
			$action->guestId = $person->personId;

		if ($group = $person->getGroup())
			$action->group = $group;

		$content = $action;
		$request = new ActionRequest($content);

		if (!AELibrary::isEmpty(AEAdapter::getSiteId())
			&& !AELibrary::isEmpty(AEAdapter::getSecurityKey()))
			{
			if (!$request->post())
			{
				$repository = new ActionRepository();
				$repository->insert(AELibrary::castArray($content));
			}
		}
		return Tools::jsonEncode((array('_ok' => true)));
	}
	else
		return Tools::jsonEncode((array('_ok' => false)));
}

public static function syncNotification()
{
	if (!Tools::getValue('aetoken') || Tools::getValue('aetoken') != AEAdapter::getBackOfficeToken())
		die('ERROR');

	$response = array();

	if (Tools::getIsset('notificationId'))
	{
		try {
			$notification = new stdClass();
			$notification->id = Tools::safeOutput(Tools::getValue('notificationId'));
			$notifications = array($notification);
			$instance = new AENotification($notifications);
			$instance->syncUpdateElement();
			$response['_ok'] = true;
		} catch(Exception $e)
		{
			AELogger::log('[ERROR]', $e->getMessage());
			$response['_ok'] = false;
		}
	}

	return Tools::jsonEncode($response);
}

}


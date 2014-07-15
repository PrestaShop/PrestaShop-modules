<?php
	/**
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
	 * @author    boxdrop Group AG
	 * @copyright boxdrop Group AG
	 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	 * International Registered Trademark & Property of boxdrop Group AG
	 */

	/**
	 * accepting AJAX requests for FrontOffice and BackOffice actions. We want to hide a separated admin dispatcher,
	 * so we'll decide here wheter we include the admin includes or not.
	 *
	 * @author sweber <sw@boxdrop.com>
	 */
	/*
	 * Only load the admin config if we have an AJAX request that needs it.
	 * Done here and not in BoxdropHelper because there may be some global vars not initialized correctly when in wrong context.
	 */
	require_once (realpath(dirname(__FILE__).'/../lib/').'/BoxdropHelper.class.php');

	if (BoxdropHelper::isBackendRequest())
	{
		$admin_dir = BoxdropHelper::getAdminDirPath();

		if ($admin_dir !== null)
		{
			define('_PS_ADMIN_DIR_', $admin_dir);
			if (!defined('PS_ADMIN_DIR'))
				define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);

			$_GET['controller'] = 'AdminCarriersController';
			/*
			 * its important to load config.inc.php after defining _PS_ADMIN_DIR_ to get the full admin context!
			 */
			require_once (realpath(dirname(__FILE__).'/../../../').'/config/config.inc.php');
			require (_PS_ADMIN_DIR_.'/functions.php');
			$context = Context::getContext();
			$context->controller = new AdminCarriersController();
		}

		/*
		 * Finally, we just want to continue here if someone is logged in.
		 */
		if (!is_object(Context::getContext()->employee))
			die();

		if (!Context::getContext()->employee->isLoggedBack())
			die();
	}
	else
		require_once (realpath(dirname(__FILE__).'/../../../').'/config/config.inc.php');

	/*
	 * load the rest we need and dispatch the request.
	 */
	require_once (realpath(dirname(__FILE__).'/../').'/boxdropshipment.php');
	$request = new BoxdropAjaxRequest('boxdropshipment');
	if (!$request->isValidRequest())
		die();

	echo $request->run();

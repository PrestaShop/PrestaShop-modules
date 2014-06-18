<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once '../../config/settings.inc.php';
require_once '../../config/defines.inc.php';

if (version_compare(_PS_VERSION_, '1.5', '<'))
{
	require_once 'KwixoFrontController.php';
	$kwixo = new KwixoPayment();

	/*token security for PS 1.4*/
	if (Tools::getValue('token') == Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey()))
	{
		/*build xml order and redirect to Kwixo payment, for PS 1.4*/
		$controller = new KwixoFrontController();
		echo '<center><h4>Vous allez être redirigé sur la page de paiement dans quelques secondes. Merci de votre patience.</h4></center>';
		$form = $controller->generateForm();
		echo $form;
	}
	else
		header('Location: ../');
}
else
	header('Location: ../');

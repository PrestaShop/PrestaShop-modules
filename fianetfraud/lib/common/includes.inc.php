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

if (!class_exists('CertissimControl', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimControl.class.php';

if (!class_exists('CertissimUtilisateur', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimUtilisateur.class.php';

if (!class_exists('CertissimSiteconso', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimSiteconso.class.php';

if (!class_exists('CertissimAdresse', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimAdresse.class.php';

if (!class_exists('CertissimAppartement', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimAppartement.class.php';

if (!class_exists('CertissimInfocommande', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimInfocommande.class.php';

if (!class_exists('CertissimTransport', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimTransport.class.php';

if (!class_exists('CertissimPointrelais', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimPointrelais.class.php';

if (!class_exists('CertissimProductList', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimProductList.class.php';

if (!class_exists('CertissimPaiement', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimPaiement.class.php';

if (!class_exists('CertissimXMLResult', false))
	require_once SAC_ROOT_DIR.'/lib/common/CertissimXMLResult.class.php';
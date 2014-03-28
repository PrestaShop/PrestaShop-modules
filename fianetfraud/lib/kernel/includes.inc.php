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

if (!class_exists('CertissimMother', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimMother.class.php';

if (!class_exists('CertissimTools', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimTools.class.php';

if (!class_exists('CertissimLogger', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimLogger.class.php';

if (!class_exists('CertissimXMLElement', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimXMLElement.class.php';

if (!class_exists('CertissimFianetSocket', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimFianetSocket.class.php';

if (!class_exists('CertissimService', false))
	require_once SAC_ROOT_DIR.'/lib/kernel/CertissimService.class.php';
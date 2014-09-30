<?php
/**
 * 2007-2013 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__) . '/pagseguroPS14.php';
include_once dirname(__FILE__) . '/pagseguroPS15.php';
include_once dirname(__FILE__) . '/pagseguroPS16.php';
include_once dirname(__FILE__) . '/pagseguroPS1601.php';
include_once dirname(__FILE__) . '/pagseguroPS1501toPS1503.php';

class PagSeguroFactoryInstallModule
{
        
    public static function createModule($version)
    {
        $context = "";
        switch ($version) {
            case version_compare($version, '1.5.0.1', '<'):
                return new PagSeguroPS14($context);
            case version_compare($version, '1.5.0.1', '>=') && version_compare($version, '1.5.0.3', '<='):
                return new PagSeguroPS1501ToPS1503();
            case version_compare($version, '1.5.0.3', '>') && version_compare($version, '1.6.0.1', '<'):
                return new PagSeguroPS15();
            case version_compare($version, '1.6.0.1', '>=') && version_compare($version, '1.6.0.2', '<'):
                return new PagSeguroPS1601();
            case version_compare($version, '1.6.0.2', '>='):
                return new PagSeguroPS16();
        }
    }
}

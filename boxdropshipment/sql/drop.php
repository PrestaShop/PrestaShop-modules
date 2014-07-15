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

	if (!defined('_PS_VERSION_'))
		exit;

	$statements = array();
	$statements[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.BoxdropOrder::$definition['table'].'`;';
	$statements[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.BoxdropOrderShipment::$definition['table'].'`;';
	$statements[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.BoxdropOrderShipmentParcel::$definition['table'].'`;';
	$statements[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail`;';

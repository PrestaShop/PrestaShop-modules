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

$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_log`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_product_repository`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_category_repository`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_order_repository`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_cart_repository`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_guest_action_repository`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_notification_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_notification`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ae_cart_ab_testing`;';

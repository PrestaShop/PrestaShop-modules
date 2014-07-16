<?php
/**
 * 	Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 */

define('RISKIFIED_ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));

if (!class_exists('CertissimLogger', false))
	require_once RISKIFIED_ROOT_DIR.'/lib/RiskifiedLogger.php';


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
*  @version  Release: $Revision: 9702 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

global $smarty;
include('../../config/config.inc.php');
include('../../header.php');
include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/plugindata.php');

$language = Language::getLanguage((int)$cookie->id_lang);
$opts = array('http' =>
	array(
		'ignore_errors' => true,
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(array_merge($_GET, array(
			'lang' => $language['iso_code'],
			'api_key' => Configuration::get('PS_ITEMBASE_APIKEY'),
		)))
	)
);
$context = stream_context_create($opts);
echo file_get_contents(PS_ITEMBASE_SERVER_EMBED.'/embed/publicpage', false, $context);

include('../../footer.php');
<?php
/*
* OpenSi Connect for Prestashop
*
* NOTICE OF LICENSE
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or (at your
* option) any later version.
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
* for more details.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author Speedinfo SARL
* @copyright 2003-2012 Speedinfo SARL
* @contact contact@speedinfo.fr
* @url http://www.speedinfo.fr
*
*/

/* Init */
$sql = array();

/* Drop databases */
$sql[] = 'DROP TABLE `'._DB_PREFIX_.'opensi_order`;';
$sql[] = 'DROP TABLE `'._DB_PREFIX_.'opensi_invoice`;';
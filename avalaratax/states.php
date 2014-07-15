<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');

$iso_code = Tools::getValue('country_iso_code');

$states[$iso_code] = Db::getInstance()->executeS('
        SELECT *, s.iso_code AS state_iso_code, c.iso_code AS country_iso_code
        FROM `'._DB_PREFIX_.'state` s, `'._DB_PREFIX_.'country` c
        WHERE s.`id_country` = c.`id_country` AND c.`iso_code` = "'.pSQL($iso_code).'"');

if (sizeof($states[$iso_code]))
	die(Tools::jsonEncode($states));
die('0');

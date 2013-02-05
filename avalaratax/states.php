<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');

$iso_code = Tools::getValue('country_iso_code');

$states[$iso_code] = Db::getInstance()->executeS('
        SELECT *
        FROM `'._DB_PREFIX_.'state` s, `'._DB_PREFIX_.'country` p
        WHERE s.`id_country` = p.`id_country` AND p.`iso_code` = "'.pSQL($iso_code).'"');

if (sizeof($states[$iso_code]))
	die(Tools::jsonEncode($states));
die('0');

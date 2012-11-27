<?php

$configPath = '../../../config/config.inc.php';
if (file_exists($configPath))
{
	include('../../../config/config.inc.php');
	if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
		die('ERROR :X');

	Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', array('sync' => (int)($_GET['action'])), 'UPDATE', '`id_category` = '.(int)$_GET['id_category']);

	if(version_compare(_PS_VERSION_, '1.5', '>')){
		$nbProducts = Db::getInstance()->getValue('
		SELECT COUNT(*) AS nb FROM(
	       SELECT p.id_product 
			FROM '._DB_PREFIX_.'product AS p
			INNER JOIN '._DB_PREFIX_.'stock_available AS s ON s.id_product = p.id_product
			WHERE s.`quantity` > 0 AND `active` = 1
			AND p.`id_category_default` IN (SELECT `id_category` FROM `'._DB_PREFIX_.'ebay_category_configuration` WHERE `id_ebay_category` > 0 AND `sync` = 1)
			GROUP BY p.id_product) TableRequete');
	}
	else{
		$nbProducts = Db::getInstance()->getValue('
		SELECT COUNT(`id_product`) as nb
		FROM `'._DB_PREFIX_.'product`
		WHERE `quantity` > 0 AND `active` = 1
		AND `id_category_default` IN (SELECT `id_category` FROM `'._DB_PREFIX_.'ebay_category_configuration` WHERE `id_ebay_category` > 0 AND `sync` = 1)');
	}

	echo $nbProducts;
}
else
	echo 'ERROR';


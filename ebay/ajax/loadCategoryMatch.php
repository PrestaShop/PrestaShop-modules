<?php

$configPath = '../../../config/config.inc.php';
if (file_exists($configPath)) {
     include('../../../config/config.inc.php');
     if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
          die('ERROR : INVALID TOKEN');

     // Fix for limit db sql request in time
     sleep(1);

     $currentPath = Db::getInstance()->getRow('
	SELECT ecc.`id_ebay_category`, ec.`id_category_ref`, ec.`id_category_ref_parent`, ec.`level`
	FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` ecc
	LEFT JOIN `' . _DB_PREFIX_ . 'ebay_category` ec ON (ec.`id_ebay_category` = ecc.`id_ebay_category`)
	WHERE ecc.`id_category` = ' . (int) Tools::getValue('id_category'));
     $level = $currentPath['level'];
     $levels = array();
     $levels[$level] = $currentPath['id_ebay_category'];
     for ($levelStart = $level; $levelStart > 1; $levelStart--) {
          $currentPath = Db::getInstance()->getRow('
		SELECT ec.`id_ebay_category`, ec.`id_category_ref`, ec.`id_category_ref_parent`, ec.`level`
		FROM `' . _DB_PREFIX_ . 'ebay_category` ec
		LEFT JOIN `' . _DB_PREFIX_ . 'ebay_category_configuration` ecc ON (ecc.`id_ebay_category` = ec.`id_ebay_category`)
		WHERE ec.`id_category_ref` = ' . (int) $currentPath['id_category_ref_parent']);
          $levels[$levelStart - 1] = $currentPath['id_ebay_category'];
     }


     $levelExists = array();
     for ($i = 0; $i <= 5; $i++)
          if ($level >= $i) {
               if ($i > 0)
                    $eBayCategoryListLevel = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category` WHERE `level` = ' . (int) ($i + 1) . ' AND `id_category_ref_parent` IN (SELECT `id_category_ref` FROM `' . _DB_PREFIX_ . 'ebay_category` WHERE `id_ebay_category` = ' . (int) $levels[$i] . ')');
               if ($eBayCategoryListLevel) {
                    $levelExists[$i + 1] = true;
                    echo '<select name="category' . (int) $_GET['id_category'] . '" id="categoryLevel' . (int) ($i + 1) . '-' . (int) Tools::getValue('id_category') . '" rel="' . (int) Tools::getValue('id_category') . '" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch(' . (int) ($i + 1) . ', ' . (int) Tools::getValue('id_category') . ');">
					<option value="0">' . ('No category selected') . '</option>';
                    foreach ($eBayCategoryListLevel as $ec)
                         echo '<option value="' . (int) $ec['id_ebay_category'] . '" ' . ((isset($levels[$i]) && $levels[$i] == $ec['id_ebay_category']) ? 'selected="selected"' : '') . '>' . $ec['name'] . ($ec['is_multi_sku'] == 1 ? ' *' : '') . '</option>';
                    echo '</select> ';
               }
          }

     if (!isset($levelExists[$level + 1]))
          echo '<input type="hidden" name="category' . (int) $_GET['id_category'] . '" value="' . (int) $levels[$level] . '" />';
}
else
     echo 'ERROR';


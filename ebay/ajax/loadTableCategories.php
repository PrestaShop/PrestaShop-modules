<?php

$configPath = dirname(__FILE__) . '/../../../config/config.inc.php';
if (file_exists($configPath)) {
     include ($configPath);
     include dirname(__FILE__) . '/../ebay.php';

     class ebayLoadCat extends ebay {

          public function getTable() {

               $isOneDotFive = $this->isVersionOneDotFive();

               if ($isOneDotFive) {
                    $smarty = $this->context->smarty;
               } else {
                    global $smarty;
               }

               if (Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
                    return $this->l('Your are not logged');
               }

               $tabHelp = "&id_tab=7";

               $categoryList = $this->_getChildCategories(Category::getCategories(Tools::getValue('id_lang')), ($this->isVersionOneDotFive()) ? 1 : 0);

               $SQL = '
                    SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category` 
                    WHERE `id_category_ref` = `id_category_ref_parent`';
               $eBayCategoryList = Db::getInstance()->executeS($SQL);

               if ($this->isVersionOneDotFive()) {
                    $rq_getCatInStock = '
                    SELECT SUM(s.`quantity`) AS instockProduct, p.`id_category_default`
                    FROM `' . _DB_PREFIX_ . 'product` AS p
                    INNER JOIN `' . _DB_PREFIX_ . 'stock_available` AS s ON p.`id_product` = s.`id_product`
                    WHERE 1 ' . $this->addSqlRestrictionOnLang('s') . '
                    GROUP BY p.`id_category_default`'
                    ;
               } else {
                    $rq_getCatInStock = '
                    SELECT SUM(`quantity`) AS instockProduct, `id_category_default`
                    FROM `' . _DB_PREFIX_ . 'product`	
                    GROUP BY `id_category_default`';
               }

               $getCatsStock = Db::getInstance()->ExecuteS($rq_getCatInStock);
               $getCatInStock = array();
               foreach ($getCatsStock as $v) {
                    $getCatInStock[$v['id_category_default']] = $v['instockProduct'];
               }

               // Loading categories
               $categoryConfigList = $ebayCatsRef = array();
               $rq_config = '
                    SELECT * 
                    FROM `' . _DB_PREFIX_ . 'ebay_category` ec
                         LEFT OUTER JOIN `' . _DB_PREFIX_ . 'ebay_category_configuration` ecc
                              ON ec.`id_ebay_category` = ecc.`id_ebay_category`';
               $categoryConfigListTmp = Db::getInstance()->executeS($rq_config);
               foreach ($categoryConfigListTmp as $c) {
                    $categoryConfigList[$c['id_category']] = $c;
               }

               // Smarty
               $datasSmarty = array(
                   'tabHelp' => $tabHelp, //
                   '_path' => $this->_path, //
                   'categoryList' => $categoryList, //
                   'eBayCategoryList' => $eBayCategoryList,
                   'getCatInStock' => $getCatInStock, //
                   'categoryConfigList' => $categoryConfigList, //
                   'request_uri' => $_SERVER['REQUEST_URI'],
                   'noCatSelected' => $this->l('No category selected'),
                   'noCatFound' => $this->l('No category found')
               );

               $smarty->assign($datasSmarty);
               return $this->display(realpath(dirname(__FILE__) . '/../'), '/views/templates/hook/table_categories.tpl');
          }

     }

     $ebayCats = new ebayLoadCat();
     echo $ebayCats->getTable();
}
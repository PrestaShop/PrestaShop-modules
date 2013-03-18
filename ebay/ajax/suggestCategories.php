<?php

$configPath = dirname(__FILE__) . '/../../../config/config.inc.php';
if (file_exists($configPath)) {
     include_once ($configPath);
     include_once dirname(__FILE__) . '/../ebay.php';

     class ebaySuggestCategories extends ebay {

          public function getSuggest() {

               if (Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
                    return $this->l('You are not logged in');
               }

               // Loading categories
               $ebay = new eBayRequest();
               $categoryConfigList = array();
               $categoryConfigListTmp = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`');
               foreach ($categoryConfigListTmp as $c)
                    $categoryConfigList[$c['id_category']] = $c;
               // Get categories
               $categoryList = Db::getInstance()->executeS('SELECT `id_category`, `name` FROM `' . _DB_PREFIX_ . 'category_lang` WHERE `id_lang` = ' . (int) Tools::getValue('id_lang') . ' ' . (_PS_VERSION_ >= '1.5' ? $this->context->shop->addSqlRestrictionOnLang() : ''));

               // GET One Product by category
               $SQL = '
                    SELECT pl.`name`, pl.`description`, p.`id_category_default`
                    FROM `' . _DB_PREFIX_ . 'product` p 
                         LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl 
                              ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = ' . (int) Tools::getValue('id_lang') . ' 
                         ' . (_PS_VERSION_ >= '1.5' ? $this->context->shop->addSqlRestrictionOnLang('pl') : '') . ')
                    GROUP BY p.`id_category_default`
                    ';
               $products = Db::getInstance()->executeS($SQL);
               // Create array
               $productTest = array();
               foreach ($products as $product) {
                    $productTest[$product['id_category_default']] = array('description' => $product['description'], 'name' => $product['name']);
               }
               // cats ref
               $refCats = Db::getInstance()->executeS('SELECT `id_ebay_category`, `id_category_ref` FROM `' . _DB_PREFIX_ . 'ebay_category` ');
               if (is_array($refCats) && sizeof($refCats) && $refCats) {
                    foreach ($refCats as $cat) {
                         $refCategories[$cat['id_category_ref']] = $cat['id_ebay_category'];
                    }
               }
               else
                    return;
               $i = 0;
               $SQL = "REPLACE INTO `" . _DB_PREFIX_ . "ebay_category_configuration` (`id_country`, `id_ebay_category`, `id_category`, `percent`, `date_add`, `date_upd`) VALUES ";
               if (is_array($categoryList) && sizeof($categoryList) && $categoryList) {
                    // while categoryList
                    foreach ($categoryList as $k => $c)
                         if (!isset($categoryConfigList[$c['id_category']])) {
                              if (isset($productTest[$c['id_category']]) && !empty($productTest[$c['id_category']])) {
                                   $id_category_ref_suggested = $ebay->getSuggestedCategories($c['name'] . ' ' . $productTest[$c['id_category']]['name']);
                                   $id_ebay_category_suggested = isset($refCategories[$id_category_ref_suggested]) ? $refCategories[$id_category_ref_suggested] : 1;
                                   if ((int) $id_ebay_category_suggested > 0) {
                                        if ($i > 0)
                                             $SQL .= ', ';
                                        $SQL .= '(8, ' . (int) $id_ebay_category_suggested . ', ' . (int) $c['id_category'] . ', 0, NOW(), NOW()) ';
                                        $i++;
                                   }
                              }
                         }
                    if ($i > 0)
                         Db::getInstance()->execute($SQL);
                    return $this->l('Settings updated');
               }
          }

     }

     $ebaySuggest = new ebaySuggestCategories();
     echo $ebaySuggest->getSuggest();
}
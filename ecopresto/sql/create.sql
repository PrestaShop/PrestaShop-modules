/******************************************************************/
/* CREATE                                                         */
/******************************************************************/
CREATE TABLE `PREFIX_ec_ecopresto_attribute` (
 `id_attribute_eco` int(11) NOT NULL AUTO_INCREMENT,
 `value` varchar(255) NOT NULL,
 `id_lang` int(11) NOT NULL,
 PRIMARY KEY (`id_attribute_eco`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_attribute_shop` (
 `id_attribute_eco` int(11) NOT NULL,
 `id_attribute` int(11) NOT NULL,
 `id_shop` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_catalog` (
 `category_1` varchar(255) NOT NULL,
 `category_2` varchar(255) NOT NULL,
 `category_3` varchar(255) NOT NULL,
 `category_4` varchar(255) NOT NULL,
 `category_5` varchar(255) NOT NULL,
 `ss_category_1` varchar(255) NOT NULL,
 `ss_category_2` varchar(255) NOT NULL,
 `ss_category_3` varchar(255) NOT NULL,
 `ss_category_4` varchar(255) NOT NULL,
 `ss_category_5` varchar(255) NOT NULL,
 `name_1` varchar(255) NOT NULL,
 `name_2` varchar(255) NOT NULL,
 `name_3` varchar(255) NOT NULL,
 `name_4` varchar(255) NOT NULL,
 `name_5` varchar(255) NOT NULL,
 `reference` varchar(255) NOT NULL,
 `manufacturer` varchar(255) NOT NULL,
 `description_short_1` text NOT NULL,
 `description_short_2` text NOT NULL,
 `description_short_3` text NOT NULL,
 `description_short_4` text NOT NULL,
 `description_short_5` text NOT NULL,
 `description_1` text NOT NULL,
 `description_2` text NOT NULL,
 `description_3` text NOT NULL,
 `description_4` text NOT NULL,
 `description_5` text NOT NULL,
 `image_1` text NOT NULL,
 `image_2` varchar(255) NOT NULL,
 `image_3` varchar(255) NOT NULL,
 `image_4` varchar(255) NOT NULL,
 `image_5` varchar(255) NOT NULL,
 `image_6` varchar(255) NOT NULL,
 `rate` varchar(25) NOT NULL,
 `price` varchar(25) NOT NULL,
 `ean13` varchar(25) NOT NULL,
 `weight` varchar(25) NOT NULL,
 `pmvc` varchar(25) NOT NULL,
 PRIMARY KEY (`reference`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_catalog_attribute` (
 `reference_attribute` varchar(255) NOT NULL,
 `reference` varchar(255) NOT NULL,
 `price` varchar(25) NOT NULL,
 `pmvc` varchar(25) NOT NULL,
 `ean13` varchar(25) NOT NULL,
 `weight` varchar(50) NOT NULL,
 `attribute_1` varchar(255) NOT NULL,
 `attribute_2` varchar(255) NOT NULL,
 `attribute_3` varchar(255) NOT NULL,
 `attribute_4` varchar(255) NOT NULL,
 `attribute_5` varchar(255) NOT NULL,
 PRIMARY KEY (`reference_attribute`),
 KEY `reference` (`reference`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_category_shop` (
 `name` varchar(255) NOT NULL,
 `id_category` int(11) NOT NULL,
 `id_shop` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_configuration` (
 `id_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(32) NOT NULL,
 `value` text,
 `id_shop` int(11) NOT NULL,
 PRIMARY KEY (`id_configuration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_export_com` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_order` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_lang` (
 `id_lang_eco` int(11) NOT NULL,
 `lang` varchar(10) NOT NULL,
 PRIMARY KEY (`id_lang_eco`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_lang_shop` (
 `id_lang_eco` int(11) NOT NULL,
 `id_lang` int(11) NOT NULL,
 `id_shop` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_product_attribute` (
 `reference` varchar(50) NOT NULL,
 `id_product_attribute` int(11) NOT NULL,
 `id_shop` int(11) NOT NULL,
 PRIMARY KEY (`reference`,`id_shop`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_product_deleted` (
 `reference` varchar(255) NOT NULL,
 `dateDelete` varchar(255) NOT NULL,
 `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_product_imported` (
 `reference` varchar(50) NOT NULL,
 `id_shop` int(11) NOT NULL,
 `imported` tinyint(1) NOT NULL,
 PRIMARY KEY (`reference`,`id_shop`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_product_shop` (
 `reference` varchar(255) NOT NULL,
 `id_shop` int(11) NOT NULL,
 `imported` tinyint(1) NOT NULL,
 PRIMARY KEY (`reference`,`id_shop`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_product_shop_temp` (
 `reference` varchar(255) NOT NULL,
 `id_shop` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_tax` (
 `id_tax_eco` int(11) NOT NULL AUTO_INCREMENT,
 `rate` varchar(10) NOT NULL,
 PRIMARY KEY (`id_tax_eco`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_ec_ecopresto_tax_shop` (
 `id_tax_eco` int(11) NOT NULL,
 `id_tax_rules_group` int(11) NOT NULL,
 `id_shop` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE  `PREFIX_ec_ecopresto_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `PREFIX_ec_ecopresto_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `transport` varchar(255) NOT NULL,
  `numero` varchar(255) NOT NULL,
  `date_exp` varchar(50) NOT NULL,
  `url_exp` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/******************************************************************/
/* INSERT                                                         */
/******************************************************************/
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('ID_ECOPRESTO','demo123456789demo123456789demo12',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PMVC_TAX','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_PRICE','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_EAN','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('DATE_STOCK','-',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_NAME_DESCRIPTION','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_IMAGE','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_PRODUCT','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PARAM_LANG','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('DATE_ORDER','-',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PARAM_INDEX','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PARAM_MULTILANG','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('DATE_IMPORT_PS','-',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PA_TAX','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PARAM_MAJ_NEWPRODUCT','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('PARAM_NEWPRODUCT','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('DATE_IMPORT_ECO','-',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('DATE_UPDATE_SELECT_ECO','-',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('UPDATE_CATEGORY','0',1);
INSERT INTO `PREFIX_ec_ecopresto_configuration`(`name`, `value`, `id_shop`) VALUES ('IMPORT_AUTO','0',1);
INSERT INTO `PREFIX_ec_ecopresto_lang` (`id_lang_eco`, `lang`) VALUES (1, 'FR');
INSERT INTO `PREFIX_ec_ecopresto_lang` (`id_lang_eco`, `lang`) VALUES (2, 'EN');
INSERT INTO `PREFIX_ec_ecopresto_lang` (`id_lang_eco`, `lang`) VALUES (3, 'DE');
INSERT INTO `PREFIX_ec_ecopresto_lang` (`id_lang_eco`, `lang`) VALUES (4, 'IT');
INSERT INTO `PREFIX_ec_ecopresto_lang` (`id_lang_eco`, `lang`) VALUES (5, 'ES');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (1, 'ID_SHOP', '1');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (2, 'ID_LANG', '1');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (3, 'ECO_SUPPLIER', 'ECOPRESTO');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (4, 'ECO_URL_COM', 'http://www.ecopresto.info/prestashop_v2/order_validator.php');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (5, 'ECO_URL_STOCK', 'http://www.ecopresto.info/download_file_stock.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (6, 'ECO_URL_CATALOGUE', 'http://www.ecopresto.info/download_file_catalogue.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (7, 'ECO_URL_SORTIE', 'http://www.ecopresto.info/download_file_sortie.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (8, 'ECO_URL_TRACKING', 'http://www.ecopresto.info/download_file_tracking.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (9, 'ECO_URL_ACTU', 'http://www.ecopresto.info/download_file_news.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (10, 'ECO_URL_LIC', 'http://www.ecopresto.info/licence.php?id=');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (11, 'ECO_TOKEN', '1A23HY4LO57JkL6');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (12, 'ECO_INFO', '971767452');
INSERT INTO `PREFIX_ec_ecopresto_info` (`id`, `name`, `value`) VALUES (13, 'ECO_URL_STAT', 'http://www.ecopresto.info/prestashop_v2/tracing_market.php');

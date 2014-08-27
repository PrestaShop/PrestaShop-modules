DROP TABLE IF EXISTS `PREFIX_SCEAU_STATE_TABLE_NAME`;
DROP TABLE IF EXISTS `PREFIX_SCEAU_CATEGORY_TABLE_NAME`;
DROP TABLE IF EXISTS `PREFIX_SCEAU_ORDER_TABLE_NAME`;

CREATE TABLE IF NOT EXISTS `PREFIX_SCEAU_STATE_TABLE_NAME` (
	`id_fianetsceau_state` int(2) unsigned NOT NULL,
	`label` varchar(15),
PRIMARY KEY (`id_fianetsceau_state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_SCEAU_ORDER_TABLE_NAME` (
	`id_cart` int(10) unsigned NOT NULL auto_increment,
	`id_order` int(10),
	`id_fianetsceau_state` int(10) NOT NULL,
	`customer_ip_address` varchar(15),
	`date` varchar(20),
	`error` varchar(255),
PRIMARY KEY (`id_cart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_SCEAU_CATEGORY_TABLE_NAME` (
	`id_category` int(10) unsigned,
	`id_fianetsceau_subcategory` int(10) NOT NULL,
	`default_category` tinyint(1) NOT NULL,
	`id_shop` int(2) unsigned NOT NULL,
PRIMARY KEY (`id_category`,`id_shop`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_SCEAU_COMMENTS_TABLE_NAME` (
	`id_comment` int(10) unsigned NOT NULL auto_increment,
	`id_product` int(10),
	`comment` varchar(255),
	`note` float,
	`firstname` varchar(30),
	`name` varchar(30),
	`state` int(10),
	`date` datetime,
PRIMARY KEY (`id_comment`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_SCEAU_PRODUCT_COMMENTS_TABLE_NAME` (
	`id_product` int(10) unsigned NOT NULL auto_increment,
	`global_note` float,
	`nb_comments` int(10),
PRIMARY KEY (`id_product`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
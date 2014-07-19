CREATE TABLE IF NOT EXISTS `PREFIX_seur_merchant` (
`id_seur_datos` int(10) NOT NULL AUTO_INCREMENT,
`user` varchar(15) NOT NULL,
`pass` varchar(15) NOT NULL,
`cit` int(10) NOT NULL,
`ccc` int(10) NOT NULL,
`nif_dni` varchar(15),
`name` varchar(30),
`first_name` varchar(30),
`franchise` varchar(5),
`company_name` varchar(50),
`street_type` varchar(5),
`street_name` varchar(60),
`street_number` varchar(10),
`staircase` varchar(10),
`floor` varchar(10),
`door` varchar(10),
`post_code` varchar(12),
`town` varchar(50),
`state` varchar(50),
`country` varchar(15),
`phone` int(10),
`fax` int(10),
`email` varchar(50),
PRIMARY KEY (`id_seur_datos`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `PREFIX_seur_order_pos` (
`id_cart` int(10) NOT NULL,
`id_seur_pos` int(10) NOT NULL,
`company` varchar(50) NOT NULL ,
`address` varchar(100) NOT NULL ,
`city` varchar(15) NOT NULL ,
`postal_code` varchar(12) NOT NULL ,
`timetable` varchar(50) NOT NULL,
`phone` varchar(20) NOT NULL,
PRIMARY KEY (`id_cart`,`id_seur_pos`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_seur_order` (
`id_order` int(10) NOT NULL,
`numero_bultos` int(10) NOT NULL,
`peso_bultos` float(10) NOT NULL,
`imprimido` varchar(5),
`printed_label` int(1) NOT NULL,
`printed_pdf` int(1) NOT NULL,
`id_address_delivery` varchar(10),
PRIMARY KEY (`id_order`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_seur_pickup` (
`id_seur_pickup` int(10) NOT NULL AUTO_INCREMENT,
`localizer` varchar(20) NOT NULL,
`num_pickup` varchar(20) NOT NULL,
`tasacion` float NOT NULL,
`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id_seur_pickup`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS`PREFIX_seur_history` (
`id_seur_carrier` int(10) NOT NULL,
`type` varchar(3),
`active` tinyint(1),
PRIMARY KEY (`id_seur_carrier`,`type`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_seur_configuration` (
`id_seur_configuration` int(10) NOT NULL AUTO_INCREMENT,
`international_orders` tinyint(1) NOT NULL,
`seur_cod` tinyint(1) NOT NULL,
`pos` tinyint(1) NOT NULL,
`notification_advice_radio` tinyint(1) NOT NULL,
`notification_distribution_radio` tinyint(1) NOT NULL,
`print_type` tinyint(1) NOT NULL,
`tarifa` tinyint(1) NOT NULL,
`pickup` tinyint(1) NOT NULL,
`advice_checkbox` tinyint(1) NOT NULL,
`distribution_checkbox` tinyint(1) NOT NULL,
`id_shop` int(10) NOT NULL,
PRIMARY KEY (`id_seur_configuration`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
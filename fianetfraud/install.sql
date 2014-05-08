DROP TABLE IF EXISTS `PREFIX_certissim_state`;
DROP TABLE IF EXISTS `PREFIX_certissim_order`;

CREATE TABLE IF NOT EXISTS `PREFIX_certissim_state` (
	`id_certissim_state` int(2) unsigned NOT NULL,
	`label` varchar(15),
	PRIMARY KEY (`id_certissim_state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_certissim_order` (
	`id_cart` int(10) unsigned NOT NULL auto_increment,
	`id_order` int(10),
	`id_certissim_state` int(10) NOT NULL,
	`customer_ip_address` varchar(15),
	`date` varchar(20),
	`avancement` varchar(80),
	`score` varchar(15),
	`profil` varchar(80),
	`detail` varchar(80),
	`error` varchar(255),
	PRIMARY KEY (`id_cart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
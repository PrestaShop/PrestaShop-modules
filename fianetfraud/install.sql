CREATE TABLE IF NOT EXISTS `PREFIX_fianet_certissim` (
		`id_order` int(11) unsigned NOT NULL,
		`ip_address` varchar(15) NOT NULL,
		`avancement` varchar(255),
		`eval` varchar(255),
		`detail` varchar(255),
		`date` datetime NOT NULL,
			KEY `id_order_index` (`id_order`),
			KEY `ip_address_index` (`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

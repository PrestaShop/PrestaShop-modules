CREATE TABLE IF NOT EXISTS `PREFIX_rnp_categories` (
  `id_category` int(10) unsigned NOT NULL auto_increment,
  `id_rnp` int(10) NOT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_sac_carriers` (
		`id_carrier` int(11) NOT NULL,
		 `id_sac_carrier` int(11) NOT NULL,
			PRIMARY KEY `id_carrier` (`id_carrier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
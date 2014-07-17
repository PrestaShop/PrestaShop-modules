CREATE TABLE IF NOT EXISTS `PREFIX_riskified_ip_info`
(
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remote_ip` varchar(64) NOT NULL,
  `x_forwarded_for` varchar(64) NOT NULL,
  PRIMARY KEY (`order_id`)
) 
ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;


CREATE TABLE IF NOT EXISTS `PREFIX_adyen_event_data` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `psp_reference` varchar(55) DEFAULT NULL COMMENT 'pspReference',
  `adyen_event_code` varchar(55) DEFAULT NULL COMMENT 'Adyen Event Code',
  `adyen_event_result` text COMMENT 'Adyen Event Result',
  `id_order` int(10) DEFAULT NULL COMMENT 'Id order',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Payment Method',
  `created_at` datetime DEFAULT NULL COMMENT 'Created At',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=294 ;


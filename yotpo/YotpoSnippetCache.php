<?php
if (!defined('_PS_VERSION_'))
	exit;

class YotpoSnippetCache {
	public static function isValidCache($row) {
		return strtotime($row['ttl']) > time();
	}
	
	public static function getRichSnippet($product_id) {
		$db = Db::getInstance();
		$result = $db->getRow('SELECT * 
											 FROM `'._DB_PREFIX_.'yotposnippetcache` 
											 WHERE id_product='.(int)$product_id, true);
		if (is_array($result)) {			
			return $result;				
		}
		return false;	
	}
	
	public static function addRichSnippetToCahce($product_id, $row) {
		$expiration_at = time() + $row['ttl'];  
		$res = Db::getInstance()->execute(
		'INSERT INTO `'._DB_PREFIX_.'yotposnippetcache` (`id_product`, `reviews_average`, `reviews_count`, `ttl`) 
		 VALUES('.(int)$product_id.', '.$row['reviews_average'].', '.$row['reviews_count'].' , \''.date("Y-m-d H:i:s",$expiration_at).'\')'
		);		
		return $res;
	}
	
	public static function updateCahce($product_id, $row){
		$expiration_at = time() + $row['ttl'];	
		$res = Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'yotposnippetcache` 
		 SET `id_product`='.(int)$product_id.' , `reviews_average`= '.$row['reviews_average'].', `reviews_count`= '.$row['reviews_count'].', `ttl`=\''.date("Y-m-d H:i:s",$expiration_at).'\'
		 WHERE `id_product`='.(int)$product_id.''
		);		
		return $res;
	}
	
	public static function createDB() {
			$engine = defined('_MYSQL_ENGINE_') ? 'ENGINE='._MYSQL_ENGINE_.'' : '';
			$db = Db::getInstance();
		return $db->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'yotposnippetcache` (
			`id` INT NOT NULL AUTO_INCREMENT, 
			`id_product` INT UNSIGNED NOT NULL,
			`reviews_average` FLOAT UNSIGNED NOT NULL,
			`reviews_count` INT UNSIGNED NOT NULL,			
			`ttl` DATETIME NOT NULL,
			PRIMARY KEY (`id`))
			'.$engine.' DEFAULT CHARSET=utf8') &&  $db->execute('CREATE UNIQUE INDEX index_product_id ON '._DB_PREFIX_.'yotposnippetcache (id_product)');
	}	

		public static function updateDB() {			
		$db = Db::getInstance();			
		return $db->execute('
			DELETE FROM `'._DB_PREFIX_.'yotposnippetcache`') &&
		 $db->execute('
			ALTER TABLE `'._DB_PREFIX_.'yotposnippetcache` 
			DROP COLUMN `rich_snippet_code`,
			ADD `reviews_average` FLOAT UNSIGNED NOT NULL,
			ADD `reviews_count` INT UNSIGNED NOT NULL');
	}
	
	public static function dropDB() {
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'yotposnippetcache`');
	}
}

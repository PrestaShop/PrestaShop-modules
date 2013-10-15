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
	
	public static function addRichSnippetToCahce($product_id, $rich_snippet_code, $expiration_time) {
		$expiration_at = time() + $expiration_time;	
		$res = Db::getInstance()->execute(
		'INSERT INTO `'._DB_PREFIX_.'yotposnippetcache` (`id_product`, `rich_snippet_code`, `ttl`)
			VALUES('.(int)$product_id.', \''.pSQL($rich_snippet_code,true).'\', \''.date("Y-m-d H:i:s",$expiration_at).'\')'
		);		
		return $res;
	}
	
	public static function updateCahce($product_id, $rich_snippet_code, $expiration_time){
		$expiration_at = time() + $expiration_time;	
		$res = Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'yotposnippetcache` 
		 SET `id_product`='.(int)$product_id.' , `rich_snippet_code`= \''.pSQL($rich_snippet_code,true).'\', `ttl`=\''.date("Y-m-d H:i:s",$expiration_at).'\'
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
			`rich_snippet_code` text NOT NULL ,
			`ttl` DATETIME NOT NULL,
			PRIMARY KEY (`id`))
			'.$engine.' DEFAULT CHARSET=utf8') &&  $db->execute('CREATE UNIQUE INDEX index_product_id ON '._DB_PREFIX_.'yotposnippetcache (id_product)');
	}	
	
	public static function dropDB() {
		return Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'yotposnippetcache`');
	}
}

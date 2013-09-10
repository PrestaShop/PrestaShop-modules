<?php
/*
* 2007-2013 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: $Revision: 7776 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

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
											 WHERE id_product='.$product_id, true);
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
		 SET `id_product`='.$product_id.' , `rich_snippet_code`= \''.pSQL($rich_snippet_code,true).'\', `ttl`=\''.date("Y-m-d H:i:s",$expiration_at).'\'
		 WHERE `id_product`='.$product_id.''
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
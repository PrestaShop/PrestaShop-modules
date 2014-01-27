<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 17142 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class GAdwords extends Module
{
	function __construct()
	{
		$this->name = 'gadwords';
		$this->tab = 'advertising_marketing';
		$this->version = 1;
		$this->author = 'PrestaShop';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Google Adwords');
		$this->description = $this->l('Vous souhaitez être plus visible sur Google et attirer de nouveaux clients ? 75€ offerts sur Google AdWords !');
	}

	public function install()
	{
		return parent::install() && $this->registerHook('backOfficeHeader');
	}

	public function hookBackOfficeHeader()
	{
		$file_path = __PS_BASE_URI__.'modules/'.$this->name.'/css/gadwords.css';
		return '<link rel="stylesheet" href="'.$file_path.'" type="text/css" />';
	}

	public function getContent()
	{
		return $this->display(__FILE__, 'views/templates/admin/gadwords.tpl');
	}
}

<?php
/*
* 2007-2013 PrestaShop 
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
*  @copyright  2007-20131 PrestaShop SA
*  @version  Release: $Revision: 9844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FidbagWebService
{

	private $_code;
	private $_certificat;
	private $_file;

	public function __construct()
	{
		$new_env = Tools::getValue('fidbag_environment');
		$environment = ($new_env !== false) ? (int)$new_env : Configuration::get('FIDBAG_TEST_ENVIRONMENT');
		
		$this->_code = Configuration::get('FIDBAG_MERCHANT_CODE');
		$this->_certificat = Configuration::get('FIDBAG_MERCHANT_CERTIFICAT');
		
		if ((bool)$environment === false)
			$this->_file = 'http://services.partner.fidbag.com/Website.asmx?wsdl';
		else
			$this->_file = 'http://preprod.services.partner.fidbag.com/Website.asmx?wsdl';
	}

	public function getClient()
	{
		if (!extension_loaded('soap'))
			return null;

		try
		{
			return new SoapClient($this->_file, array('trace' => 1));
		}
		catch(SoapFault $e){
			return null;
		}
		catch(Exception $e){
			return null;
		}
	}

	public function action($action, $arg = array(), $chif = null)
	{
		if ($chif != null)
			$arg['Signature'] = $this->encryptSha1($chif);
		else
			$arg['Signature'] = $this->encryptSha1($arg);

		try
		{
			$client = $this->getClient();
			
			if ($client == null)
				return null;

			return call_user_func(array($client, $action), $arg);
		}
		catch(Exception $e )
		{
			return $e->getMessage();
		}
	}

	public function encryptSha1($param = array())
	{
		$str = implode(";", $param);
		return sha1($str.";".$this->_certificat);
	}
}

?>

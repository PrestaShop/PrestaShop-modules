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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

class HtpasswdGenerator
{
	/** @var string User  */
	private $sUser;

	/** @var string path of the XML repository */
	private $sPwd;

	/** @var string path of the XML repository */
	private $sRepositoryPath;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sUser Login
	  * @param string $sPwd Password
	  * @param string $sRepositoryPath path of the HTACCESS & HTPASSWD repository
	  */
	public function __construct($sUser, $sPwd, $sRepositoryPath)
	{
		$this->sUser = $sUser;
		$this->sPwd = $sPwd;
		$this->sRepositoryPath = $sRepositoryPath;
	}

	/**
	  * Execute the activation or disactivation of the protection
	  *
	  * @return array containing the login & password
	  */
	public function generate()
	{
		if(!empty($this->sUser)
		&& !empty($this->sPwd))
		{
			if($this->enable())
				return array(
					'user' =>  $this->sUser,
					'pwd' =>  $this->sPwd
				);
		}
		else
		{
			if($this->disable())
				return array(
					'user' =>  '',
					'pwd' =>  ''
				);
		}
		return false;
	}

	/**
	  * Disable the protection
	  *
	  * @return always return true
	  */
	public function disable()
	{
		@unlink($this->sRepositoryPath.'/.htaccess');
		@unlink($this->sRepositoryPath.'/.htpasswd');
		return true;
	}

	/**
	  * Enable the protection, launch the HTACCESS & HTPASSWD generation
	  *
	  * @return bool return true
	  */
	public function enable()
	{
		return(	$this->generateHtaccess()
				&& $this->generateHtpasswd());

	}

	/**
	  * Generate the HTACCESS file
	  *
	  * @return bool success or failed
	  */
	private function generateHtaccess()
	{
		if($handle = fopen($this->sRepositoryPath.'.htaccess', 'w'))
		{
			$sContent = 'AuthUserFile '.$this->sRepositoryPath.'.htpasswd'."\n".
						'AuthName "Restricted Access"'."\n".
						'AuthType Basic'."\n".
						'Require valid-user'."\n";

			if(!fwrite($handle, $sContent))
				return false;
			fclose($handle);
			return true;
		}
		else
			return false;
	}

	/**
	  * Generate the HTPASSWD file
	  *
	  * @return bool success or failed
	  */
	private function generateHtpasswd()
	{
		if($handle = fopen($this->sRepositoryPath.'.htpasswd', 'w'))
		{
			if(!fwrite($handle, $this->sUser.':'.crypt($this->sPwd)))
				return false;
			fclose($handle);
			return true;
		}
		else
			return false;
	}

}
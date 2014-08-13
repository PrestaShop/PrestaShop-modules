<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

class AECookie {

	private static $instance;

	private $aecookie;

	private function __construct()
	{
		$this->aecookie = new Cookie('AffinityEngine', '/', time() + 1835136000);
	}

	private function __clone()
	{
	}

	public static function getInstance()
	{
		if (!(self::$instance instanceof self))
			self::$instance = new self();

		return self::$instance;
	}

	public function getCookie()
	{
		return $this->aecookie;
	}

	public function setcookie($aecookie)
	{
		$this->aecookie = $aecookie;
	}

}


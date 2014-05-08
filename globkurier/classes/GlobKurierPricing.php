<?php
/*
 * 2007-2014 PrestaShop
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class GlobKurierPricing {

	private $flo_weight;
	private $int_lenght;
	private $int_width;
	private $int_height;
	private $int_client_id;
	private $str_import;
	private $str_export;
	private $int_count;
	private $int_delivery_postal_code;

	/**
	 * Set default data
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		$this->int_client_id = null;
		$this->setStrImport(null);
		$this->setStrExport(null);
		$this->setIntCount(1);
		$this->int_delivery_postal_code = null;
	}

	public function setFloWeight($flo_weight)
	{
		$this->flo_weight = $flo_weight;
	}

	protected function getFloWeight()
	{
		return $this->flo_weight;
	}

	public function setIntLenght($int_lenght)
	{
		$this->int_lenght = $int_lenght;
	}

	protected function getIntLenght()
	{
		return $this->int_lenght;
	}

	public function setIntWidth($int_width)
	{
		$this->int_width = $int_width;
	}

	protected function getIntWidth()
	{
		return $this->int_width;
	}

	public function setIntHeight($int_height)
	{
		$this->int_height = $int_height;
	}

	protected function getIntHeight()
	{
		return $this->int_height;
	}

	public function setIntClientId($int_client_id)
	{
		$this->int_client_id = $int_client_id;
	}

	protected function getIntClientId()
	{
		return $this->int_client_id;
	}

	public function setStrImport($str_import)
	{
		$this->str_import = $str_import;
	}

	protected function getStrImport()
	{
		return $this->str_import;
	}

	public function setStrExport($str_export)
	{
		$this->str_export = $str_export;
	}

	protected function getStrExport()
	{
		return $this->str_export;
	}

	public function setIntCount($int_count)
	{
		$this->int_count = $int_count;
	}

	protected function getIntCount()
	{
		return $this->int_count;
	}

	public function setIntDeliveryPostalCode($int_delivery_postal_code)
	{
		$this->int_delivery_postal_code = $int_delivery_postal_code;
	}

	protected function getIntDeliveryPostalCode()
	{
		return $this->int_delivery_postal_code;
	}

	/**
	 * Given array to rest post
	 * 
	 * @param void
	 * @return array:
	 */
	protected function getData()
	{
		$this->arr_post = array(
			'weight' => $this->getFloWeight(),
			'length' => $this->getIntLenght(),
			'width' => $this->getIntWidth(),
			'height' => $this->getIntHeight(),
			'client_id' => $this->getIntClientId(),
			'count' => $this->getIntCount()
		);

		if ($this->getStrImport() !== null)
			$this->arr_post = array_merge(array('import' => $this->getStrImport()), $this->arr_post);

		if ($this->getStrExport() !== null)
			$this->arr_post = array_merge(array('export' => $this->getStrExport()), $this->arr_post);

		return $this->arr_post;
	}

	/**
	 * Send data to webservice over POST method
	 * 
	 * @param void
	 * @return http json response
	 */
	public function sendData()
	{
		$fields_string = null;
		foreach ($this->getData() as $key => $value)
			$fields_string .= $key.'='.$value.'&';
		if (GlobKurierTools::isCurl())
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GlobKurierConfig::PS_GK_URL_PRICING.'?'.$fields_string);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			$page = curl_exec($c);
			curl_close($c);
			return $page;
		}
		else
			die('ERROR: You need to enable curl php extension to use GlobKurier module.');
	}
}
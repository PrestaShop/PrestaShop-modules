<?php
/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/
class TextMasterDataWithCookiesManager
{
	const TEXTMASTER_WARNING_MESSAGE 		= 'textmaster_warning_message';
	const TEXTMASTER_SUCCESS_MESSAGE 		= 'textmaster_success_message';
	const TEXTMASTER_ERROR_MESSAGE 			= 'textmaster_error_message';
	const TEXTMASTER_PROJECT				= 'textmaster_project';
	const TEXTMASTER_SELECTED_PRODUCTS_IDS 	= 'selected_products_ids';
	const TEXTMASTER_PROJECT_DATA			= 'project_data';
	const TEXTMASTER_DOCUMENTS				= 'documents';
	const TEXTMASTER_AUTHORS				= 'authors';
	const TEXTMASTER_SHOP					= 'textmaster_shop';

	private $context;

	public function __construct()
	{
		$this->context = Context::getContext();
	}

	public function __get($name)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();
		return isset($project_data[$name]) ? $project_data[$name] : null;
	}

	public function setWarningMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::TEXTMASTER_WARNING_MESSAGE, $message);
	}

	public function setSuccessMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::TEXTMASTER_SUCCESS_MESSAGE, $message);
	}

	public function setErrorMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::TEXTMASTER_ERROR_MESSAGE, $message);
	}

	public function getWarningMessage()
	{
		$message = $this->context->cookie->{self::TEXTMASTER_WARNING_MESSAGE};
		$this->context->cookie->__unset(self::TEXTMASTER_WARNING_MESSAGE);
		return $message ? array($message) : '';
	}

	public function getSuccessMessage()
	{
		$message = $this->context->cookie->{self::TEXTMASTER_SUCCESS_MESSAGE};
		$this->context->cookie->__unset(self::TEXTMASTER_SUCCESS_MESSAGE);
		return $message ? $message : '';
	}

	public function getErrorMessage()
	{
		$message = $this->context->cookie->{self::TEXTMASTER_ERROR_MESSAGE};
		$this->context->cookie->__unset(self::TEXTMASTER_ERROR_MESSAGE);
		return $message ? array($message) : '';
	}

	public function setProjectField($key, $value)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		$project_data[$key] = $value;

		$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($project_data));
	}

	public function setSelectedProductsIds($ids)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		$project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS] = array();

		if (!is_array($ids))
			$ids = array($ids);

		foreach ($ids as $id => $value)
			if (!in_array($value, $project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS]))
				$project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS][] = $value;

		$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($project_data));
	}

	public function setProjectProjectData($data)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		if (!isset($project_data[self::TEXTMASTER_PROJECT_DATA]))
			$project_data[self::TEXTMASTER_PROJECT_DATA] = array();

		if (!is_array($data))
			$data = array($data);

		foreach ($data as $key => $value)
			if (!in_array($value, $project_data[self::TEXTMASTER_PROJECT_DATA]))
				$project_data[self::TEXTMASTER_PROJECT_DATA][] = $value;

		$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($project_data));
	}

	public function getProjectProjectData()
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return isset($project_data[self::TEXTMASTER_PROJECT_DATA]) ? $project_data[self::TEXTMASTER_PROJECT_DATA] : array();
	}

	public function setAllProject($data)
	{
		$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($data));
		$this->context->cookie->write();
	}

	public function getSelectedProductsIds()
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();
		if (!isset($project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS]))
			$project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS] = array();
		return $project_data[self::TEXTMASTER_SELECTED_PRODUCTS_IDS];
	}

	public function getAllProject()
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return $project_data;
	}

	public function getProjectData($key)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return isset($project_data[$key]) ? $project_data[$key] : '';
	}

	public function deleteAllProjectData()
	{
		$this->context->cookie->__unset(self::TEXTMASTER_PROJECT);
	}

	public function setProjectDocuments($data)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		$project_data[self::TEXTMASTER_DOCUMENTS] = $data;

		$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($project_data));
	}

	public function documentExists($id_document)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return isset($project_data[self::TEXTMASTER_DOCUMENTS][$id_document]);
	}

	public function deleteDocument($id_document)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		if (isset($project_data[self::TEXTMASTER_DOCUMENTS][$id_document]))
		{
			unset($project_data[self::TEXTMASTER_DOCUMENTS][$id_document]);
			$this->context->cookie->__set(self::TEXTMASTER_PROJECT, Tools::jsonEncode($project_data));
		}
	}

	public function projectExists()
	{
		return $this->context->cookie->__isset(self::TEXTMASTER_PROJECT);
		//$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		//return !empty($project_data);
	}

	public function getDocument($id_document)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return isset($project_data[self::TEXTMASTER_DOCUMENTS][$id_document]) ? $project_data[self::TEXTMASTER_DOCUMENTS][$id_document] : '';
	}

	public function projectDataExists($key)
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		return isset($project_data[$key]);
	}

	public function getSelectedAuthors()
	{
		$project_data = Tools::jsonDecode($this->context->cookie->{self::TEXTMASTER_PROJECT}, true);
		if (!$project_data)
			$project_data = array();

		if (!isset($project_data[self::TEXTMASTER_AUTHORS]))
			$project_data[self::TEXTMASTER_AUTHORS] = array();

		return $project_data[self::TEXTMASTER_AUTHORS];
	}

	public function deleteAllProjectDataFromCookie()
	{
		$this->context->cookie->__unset(self::TEXTMASTER_PROJECT);
	}

	public function checkCurrentShop()
	{
		if (!$this->context->cookie->__isset(self::TEXTMASTER_SHOP))
		{
			$this->context->cookie->{self::TEXTMASTER_SHOP} = (int)$this->context->shop->id;
			return true;
		}

		if ($this->context->cookie->{self::TEXTMASTER_SHOP} != (int)$this->context->shop->id)
		{
			$this->context->cookie->{self::TEXTMASTER_SHOP} = (int)$this->context->shop->id;
			$this->deleteAllProjectDataFromCookie();
			$this->context->cookie->__unset(self::TEXTMASTER_SUCCESS_MESSAGE);
			$this->context->cookie->__unset(self::TEXTMASTER_ERROR_MESSAGE);
			$this->context->cookie->__unset(self::TEXTMASTER_WARNING_MESSAGE);
			return false; //shop has been changed
		}
		return true;
	}
}
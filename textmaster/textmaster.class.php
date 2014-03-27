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
if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/config.api.php');

class TextMasterAPI
{
	private $api_key;

	private $api_secret;

	private $tracker = TEXTMASTER_TRACKER_ID;

	/* indicates wheather api key and secret codes are valid */
	private $connection = true;

	private $data = array(); // cached data

	/*
	 * Module class object, needed for translations
	 */
	private $module_instance = null;

    function __construct($module_instance = null, $api_key = null, $api_secret = null)
    {
		$this->api_key = (!$api_key) ? TextMasterConfiguration::get('api_key') : $api_key;
		$this->api_secret = (!$api_secret) ? TextMasterConfiguration::get('api_secret') : $api_secret;

		date_default_timezone_set('UTC'); // timezone must be UTC, otherwise API refuses connection
		$this->connection &= $this->testConnection();

		$this->module_instance  = (!$module_instance or !is_object($module_instance)) ? Module::getInstanceByName('textmaster') : $module_instance; // initiates module instance
		$this->getAuthors();
    }

	/* caches data */
	function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/* retrieves data from cache */
	function __get($name)
	{
		if (isset($this->data[$name]))
			return $this->data[$name];
		return array();
	}

	private function testConnection()
	{
		$result = $this->getUserInfo();
		return !empty($result) && !isset($result['errors']);
	}

	public function isConnected()
	{
		return $this->connection;
	}


	private function initConnection($name, $public, $clients, $version = TEXTMASTER_API_VERSION)
	{
		$date = date('Y-m-d H:i:s');
		$signature = sha1($this->api_secret . $date);

		$header = array("Content-Type: application/json",
						"Accept: application/json",
						"apikey: {$this->api_key}",
						"signature: $signature",
						"date: $date");

		if (function_exists('curl_init'))
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, TEXTMASTER_API_URI . ($version ? "/$version" : '') . '/' . ($clients ? 'clients/' : '') . ($public ? 'public/' : '') . $name);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($curl, CURLOPT_TIMEOUT, TEXTMASTER_API_TIMEOUT_IN_SECONDS);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			return $curl;
		}
		else
			return false;
	}

	/**
	* Connects to TextMaster API and requests for data
	*
	* @param  string  $name requested data
	* @return Array response from TextMaster API
	*/
    private function request($name, $public = false, $clients = false, $version = TEXTMASTER_API_VERSION)
    {
		if ($this->$name) return $this->$name; // return data from cache if exists

		$curl = $this->initConnection($name, $public, $clients, $version);
		$content = curl_exec($curl);
		curl_close($curl);
		$this->$name = Tools::jsonDecode($content, true); // append data to cache
		return $this->$name;
    }

	private function post($name, $data, $method = 'post')
	{
		$curl = $this->initConnection($name, false, true);

		if ($method == 'put')
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		elseif ($method == 'delete')
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		elseif ($method == 'get')
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		else
			curl_setopt($curl, CURLOPT_POST, 1);

		if ($data)
			curl_setopt($curl, CURLOPT_POSTFIELDS, Tools::jsonEncode($data));

		$result = Tools::jsonDecode(curl_exec($curl), true);

		$info = curl_getinfo($curl);

		if(curl_errno($curl) || $info['http_code'] >= 300)
		{
			if (isset($result['message']))
				$result = (is_array($result['message'])) ? implode(' ', $result['message']) : $result['message'];
			elseif (isset($result['error']))
				$result = (is_array($result['error'])) ? implode(' ', $result['error']) : $result['error'];
			elseif (isset($result['base']))
				$result = (is_array($result['base'])) ? implode(' ', $result['base']) : $result['base'];
			else
			{
				$error_msg = '';
				if (is_array($result))
				{
					if (isset($result['errors']) && isset($result['errors']['base']))
						$result = reset($result['errors']['base']);
					elseif (isset($result['errors']))
					{
						$count = count($result);
						foreach ($result['errors'] as $fieldname => $message)
							$error_msg .= $fieldname.': ' . reset($message) . ((--$count) ? ', ' : '');
						$result = $error_msg;
					}
				}
			}
		}

		curl_close($curl);
		return $result;
	}

	public function addProject($parameters, $quatation = false)
	{
		foreach ($parameters as $field => $value)
			if ($value == '')
				unset($parameters[$field]);

		$default_project_data = array(
			'same_author_must_do_entire_project' => 'true',
			'language_level' => 'regular',
			'quality' => 'false',
			'expertise' => 'false',
			'vocabulary_type' => 'not_specified',
			'grammatical_person' => 'not_specified',
			'target_reader_groups' => 'not_specified');

		$parameters = array_merge($default_project_data, $parameters); // values, sent to function overides the default values

		$options = array('language_level' => $parameters['language_level'],
						 'quality' 		=> $parameters['quality'],
						 'expertise' 		=> $parameters['expertise']);

		unset($parameters['language_level'], $parameters['quality'], $parameters['expertise']);

		$parameters['options'] = $options;

		$data = array('project' => $parameters,
					  'tracker' => $this->tracker);
		if ($quatation)
			return $this->post('projects/quotation', $data, 'get');
		return $this->post('projects', $data);
	}

	public function getLocales()
	{
		if ($this->locales) return $this->locales;
		$this->locales = $this->request("locales", true);
		return $this->locales;
	}

	public function updateProject($parameters)
	{
		$data = array('project' => $parameters,
					  'tracker' => $this->tracker);

		return $this->post("projects/{$parameters['id']}", $data, 'put');
	}

	public function launchProject($id_project_api)
	{
		return $this->post("projects/$id_project_api/launch", null, 'put');
	}

	public function deleteProject($id_project_api)
	{
		return $this->post("projects/$id_project_api/cancel", null, 'put');
	}

	public function addDocument($id_project_api, $parameters)
	{
		$data = array('document' => $parameters);
		return $this->post("projects/$id_project_api/documents", $data);
	}

	public function updateDocument($id_project_api, $parameters)
	{
		$data = array('document' => $parameters);
		return $this->post("projects/$id_project_api/documents/{$parameters['id']}", $data, 'put');
	}

	public function deleteDocument($id_project_api, $id_document_api)
	{
		return $this->post("projects/$id_project_api/documents/$id_document_api", null, 'delete');
	}

	public function approveDocument($id_project_api, $id_document_api)
	{
		return $this->post("projects/$id_project_api/documents/$id_document_api/complete", null, 'put');
	}

	public function commentDocument($id_project_api, $id_document_api, $message)
	{
		return $this->post("projects/$id_project_api/documents/$id_document_api/support_messages", array('support_message' => array('message' => $message)), 'post');
	}

	public function getDocumentComments($id_project_api, $id_document_api)
	{
		$comments = $this->request("projects/$id_project_api/documents/$id_document_api/support_messages", false, true);
		return $comments['support_messages'];
	}

	public function getProjects($type, $filter = array())
	{
		if ($this->{'projects_'.$type}) return $this->{'projects_'.$type}; // return projects from cache if exists
		$all_projects = $this->request('projects?per_page=5000', false, true); // all projects data

		//$all_projects['projects'] = $this->module_instance->getProjectsListDataByType($type);

		/* divides all projects into specific arrays of translation, copywriting and proofreading */

		foreach ($all_projects['projects'] as $project)
		{
			$project_local_data = TextMasterProject::getProjectByApiId($project['id']);
			if(!$project_local_data->id) continue; // lets go through only if project has been created from within PrestaShop
			$project['id_project'] 		   = $project_local_data->id;
			$project['date_add'] 		   = $project_local_data->date_add;
			$project['date_upd'] 		   = $project_local_data->date_upd;
			$project['show_launch_button'] = 1; //always show button but displayLaunchButton function does the final decision according status. Kinda workaraund here.

			$projectsData = $this->{'projects_'.$project['ctype']}; // copy of specific projects array
			$projectsData[] = $project; // appends project data into copy of specific projects array
			$this->{'projects_'.$project['ctype']} = $projectsData; // updated array is assigned back to original specific projects array
		}

		return $this->{'projects_'.$type};
	}

	public function getDocuments($id_project_api)
	{
		return $this->request("projects/$id_project_api/documents?per_page=5000", false, true);
	}

	public function getDocument($id_project_api, $id_document_api)
	{
		return $this->request("projects/$id_project_api/documents/$id_document_api", false, true);
	}

	public function getProject($id_project_api)
	{
		return $this->request("projects/$id_project_api", false, true);
	}

	public function getAuthors()
	{
		if ($this->authors) return $this->authors;
		$this->authors = $this->request("my_authors", false, true);
		return $this->authors;
	}

	public function getUserInfo()
	{
		if ($this->user_info) return $this->user_info;
		$this->user_info = $this->request("/users/me", false, true);
		return $this->user_info;
	}

	public function getPricings($word_count = 1)
	{
		if ($this->prices) return $this->prices;

		$user = $this->getUserInfo();

		$prices = $this->request("reference_pricings?word_count=$word_count", true);

		foreach ($prices['reference_pricings'] as $pricings)
		{
			if ($pricings['locale'] == $user['locale'])
			{
				foreach ($pricings['types'] as $type => $params)
				{
					foreach ($params as $key => $param)
					{
						$pricings['types'][$type][$param['name']] = $param['value'];
						unset($pricings['types'][$type][$key]);
					}
				}
				$this->prices = $pricings;
				return $this->prices;
			}
		}
		$this->prices = array();
		return $this->prices;
	}

	public function getDocumentation($locale = null)
	{
		if (!$locale)
			$locale = $this->module_instance->getFullLocale();

		return $this->request('clients/localized_contents/pretashop_help/'.$locale);
	}

	public function getLanguages()
	{
		if($this->languages) return $this->languages;

		$context = Context::getContext();
		$data = $this->request('languages?locale='.$context->language->iso_code.'-'.Tools::strtoupper($context->language->language_code), true);
		$textmaster_languages =  $data['languages'];
		$languages = array();
		$installed_languages = Language::getLanguages(false);
		foreach ($installed_languages as $language)
			for ($i=0; $i<count($textmaster_languages); $i++)
				if ($textmaster_languages[$i]['code'] == $language['iso_code'])
					$languages[$language['iso_code']] = $textmaster_languages[$i];

		$this->languages = $languages;
		return $languages;
	}

	public function getCategories()
	{
		if($this->categories) return $this->categories;
		$context = Context::getContext();
		$data = $this->request('categories?locale='.$context->language->iso_code.'-'.Tools::strtoupper($context->language->language_code), true);
		$this->categories = $data['categories'];
		return $this->categories;
	}

	/**
	* Puts available service levels into array
	* @return Array service levels
	*/

	public function getServiceLevels()
	{
		if ($this->service_levels) return $this->service_levels; // return vocabulary levels from cache if exists

		/* put service levels into array */
		$this->service_levels = array('regular' => $this->module_instance->l('Regular', 'textmaster.class'),
									  'premium' => $this->module_instance->l('Premium', 'textmaster.class'));
		return $this->service_levels;
	}

	/**
	* Puts available vocabulary levels into array
	* @return Array vocabulary levels
	*/

	public function getVocabularyLevels()
	{
		if ($this->vocabulary_levels) return $this->vocabulary_levels; // return vocabulary levels from cache if exists

		/* put vocabulary levels into cache */
		$this->vocabulary_levels = array('not_specified' => $this->module_instance->l('Not specified', 'textmaster.class'),
										 'popular' => $this->module_instance->l('Popular', 'textmaster.class'),
										 'technical' => $this->module_instance->l('Technique', 'textmaster.class'),
										 'fictional' => $this->module_instance->l('Fictional', 'textmaster.class'));
		return $this->vocabulary_levels;
	}

	/**
	* Puts available grammatical persons into array
	* @return Array grammatical persons
	*/

	public function getGrammaticalPersons()
	{
		if ($this->grammatical_persons) return $this->grammatical_persons; // return grammatical persons from cache if exists

		/* put grammatical persons into cache */
		$this->grammatical_persons = array('not_specified' => $this->module_instance->l('Not specified', 'textmaster.class'),
										   'first_person_singular' => $this->module_instance->l('I', 'textmaster.class'),
										   'second_person_singular' => $this->module_instance->l('You', 'textmaster.class'),
										   'third_person_singular_masculine' => $this->module_instance->l('He', 'textmaster.class'),
										   'third_person_singular_feminine' => $this->module_instance->l('She', 'textmaster.class'),
										   'third_person_singular_neuter' => $this->module_instance->l('One', 'textmaster.class'),
										   'first_person_plural' => $this->module_instance->l('We', 'textmaster.class'),
										   'second_person_plural' => $this->module_instance->l('You', 'textmaster.class'),
										   'third_person_plural' => $this->module_instance->l('They', 'textmaster.class'));

		return $this->grammatical_persons;
	}

	/**
	* Puts available target audiences into array
	* @return Array audiences
	*/

	public function getAudiences()
	{
		if ($this->audiences) return $this->audiences; // return audiences from cache if exists

		/* put audieces into cache */
		$this->audiences = array('not_specified' => $this->module_instance->l('Not specified', 'textmaster.class'),
								 'children' => $this->module_instance->l('Children under 14 years old', 'textmaster.class'),
								 'teenager' => $this->module_instance->l('Teenagers > between 14 and 18 years old', 'textmaster.class'),
								 'young_adults' => $this->module_instance->l('Young adults > between 19 and 29 years old', 'textmaster.class'),
								 'adults' => $this->module_instance->l('Adults > between 30 and 59 years old', 'textmaster.class'),
								 'old_adults' => $this->module_instance->l('Seniors > 60 years old and beyond', 'textmaster.class'));
		return $this->audiences;
	}

	/**
	* Puts requested data in associative array
	*
	* @param  string  $item Type of data we want to retrieve
	* @return Array
	*/

	function getSelectOf($item)
	{
		switch ($item)
		{
			case 'vocabulary_levels':
				$data = $this->getVocabularyLevels();
				break;
			case 'service_levels':
				$data = $this->getServiceLevels();
				break;
			case 'grammatical_persons':
				$data = $this->getGrammaticalPersons();
				break;
			case 'audiences':
				$data = $this->getAudiences();
				break;
			default:
				$data = $this->request($item);
				break;
		}

		$items = array();

		/* prestashop needs this array to be in a sertain structure for displaying it in forms as HTML select element */
		foreach ($data as $name => $value)
			$items[] = array('name' => $name, 'value' => $value);

		return $items;
	}
}
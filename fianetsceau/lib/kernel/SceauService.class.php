<?php

/**
 * Implement a Fia-Net's service (Certissim, Kwixo or Sceau) *
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method void setName(string $name) sets the local var name
 * @method void setSiteid(string $siteid) sets the local var siteid
 * @method void setLogin(string $login) sets the local var login
 * @method void setPassword(string $password) sets the local var password
 * @method void setPasswordurlencoded(string $passwordurlencoded) sets the local var passwordurlencoded
 * @method void setAuthkey(string $authkey) sets the local var authkey
 * @method void setStatus(string $status) sets the local var status
 * @method string getName(string $name) returns the local var name value
 * @method string getSiteid(string $siteid) returns the local var siteid value
 * @method string getLogin(string $login) returns the local var login value
 * @method string getPassword(string $password) returns the local var password value
 * @method string getPasswordurlencoded(string $passwordurlencoded) returns the local var passwordurlencoded value
 * @method string getAuthkey(string $authkey) returns the local var authkey value
 * @method string getStatus(string $status) returns the local var status value
 * 
 * @method string getUrlScriptname() returns the URL of the script 'Scriptname' according to the status
 * Usage :
 * <code>
 * $service->getUrlStacking(); //returns the stacking.cgi URL
 * </code>
 */
abstract class SceauService extends SceauMother
{
	/* site params */

	protected $name; //product name
	protected $siteid;
	protected $login;
	protected $password;
	protected $passwordurlencoded;
	protected $authkey;
	protected $status;
	protected $url = array();
	private $_url = array(
		'sendrating' => array(
			'prod' => 'https://www.fia-net.com/engine/sendrating.cgi',
			'test' => 'https://www.fia-net.com/engine/preprod/sendrating.cgi',
		)
	);
	private $_param_names = array(
		'siteid',
		'login',
		'password',
		'authkey',
		'status',
	);
	private $_available_statuses = array(
		'test',
		'prod',
	);

	public function __construct($id_shop = null)
	{
		//for PS >= 1.5, sets the $id_shop
		$this->setIdshop($id_shop);

		//loads site params
		$this->loadParams();
		//loads webservices URL
		$this->loadURLs();
	}

	public function getProductname()
	{
		$name = $this->getName();
		if (empty($name))
			$this->setName(strtolower(get_class($this)));

		return $this->getName();
	}

	/**
	 * loads site params from the file given in param
	 * 
	 * @param string $filename
	 */
	private function loadParams()
	{

		foreach ($this->_param_names as $param_name)
		{
			$funcname = 'set'.$param_name;
			if (_PS_VERSION_ < '1.5')
				$this->$funcname(Configuration::get('FIANETSCEAU_'.strtoupper($param_name)));
			else
				$this->$funcname(Configuration::get('FIANETSCEAU_'.strtoupper($param_name), null, null, $this->getIdshop()));
		}
	}

	/**
	 * loads scripts URL according to the current status if status defined and active
	 */
	private function loadURLs()
	{

		$status = $this->statusIsAvailable($this->getStatus()) ? $this->getStatus() : 'test';

		foreach ($this->_url as $scriptname => $modes)
		{
			$this->url[$scriptname] = $modes[$status];
		}
	}

	/**
	 * returns the URL of the script given in param if it exists, false otherwise
	 *
	 * @param string $script
	 * @return string
	 */
	public function getUrl($script)
	{
		if (!array_key_exists($script, $this->url))
		{
			$msg = "L'url pour le script $script n'existe pas ou n'est pas chargée. Vérifiez le paramétrage.";
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $msg);
			return false;
		}

		return $this->url[$script];
	}

	/**
	 * switches status to $mode and reload URL if available, returns false otherwise
	 *
	 * @param string $mode test OR prod OR off
	 * @return bool
	 */
	public function switchMode($mode)
	{
		if (!$this->statusIsAvailable($mode))
		{
			SceauLogger::insertLogSceau(__FILE__, "Le mode '$mode' n'est pas reconnu.");
			$mode = 'test';
		}

		//switch the status to $mode
		$this->setStatus($mode);

		//reload URLs
		$this->loadURLs();
	}

	/**
	 * saves params into the param YAML file and returns true if save succeed, false otherwise
	 *
	 * @return bool
	 */
	public function saveParamInFile()
	{
		foreach ($this->_param_names as $param_name)
		{
			$funcname = 'get'.$param_name;
			if (_PS_VERSION_ < '1.5')
				Configuration::updateValue('FIANETSCEAU_'.strtoupper($param_name), $this->$funcname());
			else
				Configuration::updateValue('FIANETSCEAU_'.strtoupper($param_name), $this->$funcname(), false, null, $this->getIdshop());
		}
	}

	public function statusIsAvailable($status)
	{
		return in_array($status, $this->_available_statuses);
	}

	public function __call($name, array $params)
	{
		if (preg_match('#^getUrl.+$#', $name) > 0)
		{
			return $this->getUrl(preg_replace('#^getUrl(.+)$#', '$1', $name));
		}

		return parent::__call($name, $params);
	}

}
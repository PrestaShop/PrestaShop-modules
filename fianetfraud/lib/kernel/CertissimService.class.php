<?php

/**
 * Abstract class that represents a Fia-Net Service
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class CertissimService extends CertissimMother
{

	private $_url = array(
		'redirect' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/redirect.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/redirect.cgi',
		),
		'singet' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/singet.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/singet.cgi',
		),
		'stacking' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/stacking.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/stacking.cgi',
		),
		'stackfast' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/stackfast.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/stackfast.cgi',
		),
		'backoffice' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener',
			'test' => 'https://secure.FIA-NET.com/pprod',
		),
		'visucheck' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/BO/visucheck_detail.php',
			'test' => 'https://secure.FIA-NET.com/pprod/BO/visucheck_detail.php',
		),
		'getvalidation' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_validation.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/get_validation.cgi',
		),
		'redirectvalidation' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/redirect_validation.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/redirect_validation.cgi',
		),
		'defaultredirectvaildationurlback' => array(
			'prod' => 'http://localhost/API_PHP/urlbackprod.php',
			'test' => 'http://localhost/API_PHP/urlbacktest.php',
		),
		'getvalidstack' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_validstack.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/get_validstack.cgi',
		),
		'getalert' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_alert.cgi',
			'test' => 'https://secure.FIA-NET.com/pprod/engine/get_alert.cgi',
		),
		'visucheckdetail' => array(
			'prod' => 'https://secure.FIA-NET.com/fscreener/BO/visucheck_detail.php',
			'test' => 'https://secure.FIA-NET.com/pprod/BO/visucheck_detail.php'
		)
	);
	private $_param_names = array(
		'siteid',
		'login',
		'password',
		'passwordurlencoded',
		'status',
	);
	private $_available_statuses = array(
		'test',
		'prod',
	);
	protected $siteid;
	protected $login;
	protected $password;
	protected $passwordurlencoded;
	protected $authkey;
	protected $status;
	protected $url = array();
	protected $idshop = null; //for PS >= 1.5

	public function __construct($id_shop = null)
	{
		//for PS >= 1.5, sets the $id_shop
		$this->setIdshop($id_shop);

		//loads site params
		$this->loadParams();
		//loads webservices URL
		$this->loadURLs();
	}

	/**
	 * loads the params according to the global Configuration
	 */
	public function loadParams()
	{
		foreach ($this->_param_names as $param_name)
		{
			$funcname = 'set'.$param_name;
			if (_PS_VERSION_ < '1.5')
				$this->$funcname(Configuration::get('CERTISSIM_'.strtoupper($param_name)));
			else
				$this->$funcname(Configuration::get('CERTISSIM_'.strtoupper($param_name), null, null, $this->getIdshop()));
		}
	}

	/**
	 * loads webservices URL accordind to the status
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
	 * returns the URL of the asked webservice if exists localy, false otherwise
	 *
	 * @param string $script webservice name
	 * @return mixed URL if success, false otherwise
	 */
	public function getUrl($script)
	{
		if (!array_key_exists($script, $this->url))
		{
			$msg = "L'url pour le script $script n'existe pas ou n'est pas chargée. Vérifiez le paramétrage.";
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $msg);
			return false;
		}

		return $this->url[$script];
	}

	/**
	 * switch the status to $mode if available, to 'test' otherwise
	 *
	 * @version 3.1
	 * @param bool $mode
	 * @return bool vrai si la mise à jour est ok, faux sinon
	 */
	public function switchMode($mode)
	{
		if (!$this->statusIsAvailable($mode))
		{
			CertissimLogger::insertLog(__FILE__, "Le mode '$mode' n'est pas reconnu. 'test' défini à la place.");
			$mode = 'test';
		}

		//switch the status to $mode
		$this->setStatus($mode);

		//reload URLs
		$this->loadURLs();
	}

	/**
	 * update Configuration with local params
	 */
	public function saveParams()
	{
		foreach ($this->_param_names as $param_name)
		{
			$funcname = 'get'.$param_name;
			if (_PS_VERSION_ < '1.5')
				Configuration::updateValue('CERTISSIM_'.strtoupper($param_name), $this->$funcname());
			else
				Configuration::updateValue('CERTISSIM_'.strtoupper($param_name), $this->$funcname(), false, null, $this->getIdshop());
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
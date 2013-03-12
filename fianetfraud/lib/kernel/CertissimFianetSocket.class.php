<?php

/**
 * Connexion class using fsockopen
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimFianetSocket extends CertissimMother
{

	const TIMEOUT = 20;

	protected $host;
	protected $port;
	protected $is_ssl = false;
	protected $method = 'GET';
	protected $data;
	protected $path;
	protected $response = '';
	protected $errno;
	protected $errstr;

	/**
	 * initializes the connexion to a server
	 *
	 * @param string $url URL to reach
	 * @param string $method HTTP method
	 * @param array $data datas to send
	 */
	public function __construct($url, $method = 'GET', array $data = null)
	{
		if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST')
			$this->method = strtoupper($method);
		else
		{
			$msg = "La methode demandee ($method) n'est pas reconnue.";
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, $msg);
			throw new Exception($msg);
		}

		if (!is_null($data))
			$this->data = http_build_query($data);

		$this->parseUrl($url);
	}

	/**
	 * cleans the URL to reach in order to separate host and script
	 *
	 * @param string $url URL to reach
	 */
	public function parseUrl($url)
	{
		preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

		$components = parse_url($url);
		extract($components);
		if ($scheme == 'http')
		{
			$this->is_ssl = false;
			$this->port = 80;
		}
		if ($scheme == 'https')
		{
			$this->is_ssl = true;
			$this->port = 443;
		}
		$this->host = $host;
		$this->path = $path;

		if (isset($query))
			$this->data = $query;
	}

	/**
	 * builds the header to send
	 *
	 * @return string
	 */
	function build_header()
	{
		if ($this->method == 'POST')
		{
			$header = "POST ".$this->path." HTTP/1.0\r\n";
			$header .= "Host: ".$this->host."\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-length: ".strlen($this->data)."\r\n\r\n";
			$header .= $this->data;
		}
		elseif ($this->method == 'GET')
		{
			if (strlen($this->path.$this->data) > 2048)
				CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Maximum length in get method reached(".strlen($this->path.$this->data).")");
			$header = "GET ".$this->path.'?'.$this->data." HTTP/1.1\r\n";
			$header .= "Host: ".$this->host."\r\n";
			$header .= "Connection: close\r\n\r\n";
		}

		return ($header);
	}

	/**
	 * sends the request the the host and returns the response
	 * 
	 * @return resource 
	 */
	function send()
	{
		$header = $this->build_header();

		$this->response = $this->connect($header);

		return $this->getContent();
	}

	/**
	 * conects to a server, reach the script and returns the response
	 *
	 * @param string $header header to send to the server to reach the script
	 * @return type
	 */
	function connect($header)
	{
		$error = '';
		$errno = '';
		if ($this->is_ssl)
			$socket = fsockopen('ssl://'.$this->host, $this->port, $errno, $error, CertissimFianetSocket::TIMEOUT);
		else
			$socket = fsockopen($this->host, $this->port);
		
		if ($socket !== false)
		{
			$res = '';

			if (@fputs($socket, $header))
				while (!feof($socket))
					$res .= fgets($socket, 128);
			else
			{
				CertissimLogger::insertLog(__METHOD__.' - '.__LINE__, "Envoi des données impossible sur : ".$this->host);
				$res = false;
			}
			fclose($socket);
		}
		else
		{
			$msg = "Connexion socket impossible sur l'hôte ".$this->host.". Erreur ".$errno." : ".$error;
			CertissimLogger::insertLog(__METHOD__.' - '.__LINE__, $msg);
			$res = false;
		}
		return $res;
	}

	/**
	 * splits the header and the body of the response and returns the body
	 *
	 * @return string
	 */
	public function getContent()
	{
		$return = $this->response !== false ? preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response) : false;
		return $return;
	}

}
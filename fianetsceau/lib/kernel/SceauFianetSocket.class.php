<?php

/**
 * Connexion class using fsockopen
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauFianetSocket extends SceauMother
{
	const TIMEOUT = 10;

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
		//saving locally connexion method
		if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST')
		{
			$this->method = strtoupper($method);
		} else
		{
			$msg = "La méthode demandée ($method) n'est pas reconnue.";
			SceauTools::insertLog(get_class($this).'->__construct()', $msg);
			throw new Exception($msg);
		}

		if (!is_null($data))
		{
			$this->data = http_build_query($data);
		}

		$this->parseUrl($url);
	}

	/**
	 * cleans the URL to reach in order to separate host and script
	 *
	 * @param string $url URL to reach
	 */
	public function parseUrl($url)
	{
		//split
		preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

		$components = parse_url($url);
		extract($components);
		//if we found a no secured address
		if ($scheme == 'http')
		{
			//ssl mode fixed at false
			$this->is_ssl = false;
			//port 80 used
			$this->port = 80;
		}
		if ($scheme == 'https')
		{
			//ssl mode fixed at true
			$this->is_ssl = true;
			//port 443 used
			$this->port = 443;
		}
		//we fix host
		$this->host = $host;
		//we fix script
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
		} elseif ($this->method == 'GET')
		{
			if (strlen($this->path.$this->data) > 2048)
			{
				SceauTools::insertLog(get_class($this)." : __construct", "Maximum length in get method reached(".strlen($this->path.$this->data).")");
			}
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
		//header construction
		$header = $this->build_header();

		//server connexion
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
		//if connexion is secured
		if ($this->is_ssl)
		{
			//ssl connexion
			$socket = fsockopen('ssl://'.$this->host, $this->port, $this->errno, $this->errstr, self::TIMEOUT);

			//if connexion is not secured
		} else
		{
			//connexion without ssl
			$socket = fsockopen($this->host, $this->port);
		}

		if ($socket !== false)
		{
			$res = '';

			//sending data to the server
			if (@fputs($socket, $header))
			{

				//response reading
				while (!feof($socket))
				{
					$res .= fgets($socket, 128);
				}

				//if sending data failed
			} else
			{
				//we put error on log file
				SceauTools::insertLog(get_class($this)." - connect()", "Envoi des données impossible sur : ".$host);
				$res = false;
			}
			//connexion closed
			fclose($socket);
		} else
		{
			SceauTools::insertLog(get_class($this)." - connect()", "Connexion socket impossible sur l'hôte $host. Erreur ".$this->errno." : ".$this->errstr);
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
		return preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response);
	}

}
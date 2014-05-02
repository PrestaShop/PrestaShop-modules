<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

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
	protected $method = 'POST';
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
		if (Tools::strtoupper($method) == 'GET' || Tools::strtoupper($method) == 'POST')
			$this->method = Tools::strtoupper($method);
		else
		{
			$msg = "La methode demandee ($method) n'est pas reconnue.";
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $msg);
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
		$scheme = $components['scheme'];
		$host = $components['host'];
		$path = $components['path'];
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
	}

	/**
	 * builds the header to send
	 *
	 * @return string
	 */
	public function buildHeader()
	{
		if ($this->method == 'POST')
		{
			$header = 'POST '.$this->path." HTTP/1.0\r\n";
			$header .= 'Host: '.$this->host."\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= 'Content-length: '.Tools::strlen($this->data)."\r\n\r\n";
			$header .= $this->data;
		}
		elseif ($this->method == 'GET')
		{
			if (Tools::strlen($this->path.$this->data) > 2048)
				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Maximum length in get method reached('.Tools::strlen($this->path.$this->data).')');
			$header = 'GET '.$this->path.'?'.$this->data." HTTP/1.1\r\n";
			$header .= 'Host: '.$this->host."\r\n";
			$header .= "Connection: close\r\n\r\n";
		}

		return ($header);
	}

	/**
	 * sends the request the the host and returns the response
	 * 
	 * @return resource 
	 */
	public function send()
	{
		$header = $this->buildHeader();

		$this->response = $this->connect($header);

		return $this->getContent();
	}

	/**
	 * conects to a server, reach the script and returns the response
	 *
	 * @param string $header header to send to the server to reach the script
	 * @return type
	 */
	public function connect($header)
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

			if (fputs($socket, $header))
				while (!feof($socket))
					$res .= fgets($socket, 128);
			else
			{
				CertissimLogger::insertLog(__METHOD__.' - '.__LINE__, 'Envoi des données impossible sur : '.$this->host);
				$res = false;
			}
			fclose($socket);
		}
		else
		{
			$msg = 'Connexion socket impossible sur hôte '.$this->host.'. Erreur '.$errno.' : '.$error;
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
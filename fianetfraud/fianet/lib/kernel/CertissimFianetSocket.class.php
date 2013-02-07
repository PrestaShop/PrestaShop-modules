<?php

/**
 * Connection class, uses fsockopen PHP function
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimFianetSocket extends CertissimMother
{

  const TIMEOUT = 4;

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
   * init a connection to a server
   *
   * @param string $url URL to reach
   * @param string $method HTTP method to use
   * @param array $data vars to send
   */
  public function __construct($url, $method = 'GET', array $data = null)
  {
    //set the HTTP method
    if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST')
    {
      $this->method = strtoupper($method);
    }
    else
    {
      $msg = "La methode demandee ($method) n'est pas reconnue.";
      CertissimTools::insertLog(__METHOD__." : ".__LINE__, $msg);
      throw new Exception($msg);
    }

    //if datas are given in parameter, builds the data string
    if (!is_null($data))
    {
      $this->data = http_build_query($data);
    }

    //cleans the URL
    //Carefull: replace the current value of $this->data if datas are included in $url
    $this->parseUrl($url);
  }

  /**
   * split the URL $url into host and path
   *
   * @param string $url
   */
  public function parseUrl($url)
  {
    //split
    preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

    $components = parse_url($url);
    extract($components);
    //if protocol is not secured
    if ($scheme == 'http')
    {
      //ssl set to false
      $this->is_ssl = false;
      //port set to 80
      $this->port = 80;
    }
    //if protocol is secured
    if ($scheme == 'https')
    {
      //ssl set to true
      $this->is_ssl = true;
      //port set to 443
      $this->port = 443;
    }
    
    //registering host
    $this->host = $host;
    //registering path
    $this->path = $path;

    //if a query was contained into the URL, registration of the query
    if (isset($query))
      $this->data = $query;
  }

  /**
   * builds the header sent to the distant server
   *
   * @return type
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
      {
        CertissimTools::insertLog(__METHOD__." : ".__LINE__, "Maximum length in get method reached(".strlen($this->path.$this->data).")");
      }
      $header = "GET ".$this->path.'?'.$this->data." HTTP/1.1\r\n";
      $header .= "Host: ".$this->host."\r\n";
      $header .= "Connection: close\r\n\r\n";
    }

    return ($header);
  }

  /**
   * build the request sent to the distant host and returns the response
   * 
   * @return string 
   */
  function send()
  {
    //builds the header
    $header = $this->build_header();

    //connects to the distant server and send the datas
    $this->response = $this->connect($header);

    return $this->getContent();
  }

  /**
   * makes a connection to a distant server, reach the path, and returns the response
   *
   * @param string $header
   * @return type
   */
  function connect($header)
  {
    $error = '';
    $errno = '';
    //if secured
    if ($this->is_ssl)
    {
      //connection to the server with ssl
      $socket = fsockopen('ssl://'.$this->host, $this->port, $errno, $error, CertissimFianetSocket::TIMEOUT);
    }
    else //if not secured
    {
      //http connection
      $socket = fsockopen($this->host, $this->port);
    }

    //if connection ok
    if ($socket !== false)
    {
      $res = '';

      //sends the header
      if (@fputs($socket, $header))
      {

        //reads the response
        while (!feof($socket)) {
          $res .= fgets($socket, 128);
        }
      }
      else //if connection failed
      {
        //logs error
        CertissimTools::insertLog(__METHOD__.' - '.__LINE__, "Envoi des données impossible sur : ".$this->host);
        $res = false;
      }
      //close connection
      fclose($socket);
    }
    else//if connection not established
    {
      //logs error
      $msg = "Connexion socket impossible sur l'hôte ".$this->host.". Erreur ".$errno." : ".$error;
      CertissimTools::insertLog(__METHOD__.' - '.__LINE__, $msg);
      $res = false;
    }
    return $res;
  }

  /**
   * split the header and the body of the response and returns the body
   *
   * @return string
   */
  public function getContent()
  {
    $return = $this->response !== false ? preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response) : false;
    return $return;
  }

}
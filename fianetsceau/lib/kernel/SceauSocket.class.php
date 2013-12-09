<?php

/**
 * Connection class using fsockopen
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauSocket {

  const TIMEOUT = 5;

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
   * inits a connetion to a server
   *
   * @param string $url URL to reach
   * @param string $method HTTP method (GET or POST)
   * @param array $data data to send
   */
  public function __construct($url, $method = 'POST', array $data = null) {
    //sets the HTTP method if recognized, throw an error otherwise
    if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST')
      $this->method = strtoupper($method);
    else {
      $msg = "La méthode demandée ($method) n'est pas reconnue.";
      insertLogSceau(__METHOD__ . ' : ' . __LINE__, $msg);
      throw new Exception($msg);
    }

    //builds data string
    if (!is_null($data))
      $this->data = http_build_query($data);

    //pars the given URL
    //Watch out !! It will replace the actual value of $this->data if $url contains datas
    $this->parseUrl($url);
  }

  /**
   * cleans the URL $url to split scheme, host, path and query
   * nettoie l'url appelée pour séparer hôte et script
   *
   * @param string $url url du script appelé
   */
  public function parseUrl($url) {
    preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

    $components = parse_url($url);
    extract($components);
    //if non secured connexion asked, sets is_ssl to false and port to 80
    if ($scheme == 'http') {
      $this->is_ssl = false;
      $this->port = 80;
    }

    //if secured connexion asked, sets is_ssl to trueand port to 443
    if ($scheme == 'https') {
      $this->is_ssl = true;
      $this->port = 443;
    }

    //gets host
    $this->host = $host;
    //gets path
    $this->path = $path;

    //gets the query data
    if (isset($query))
      $this->data = $query;
  }

  /**
   * builds and returns header
   *
   * @return string
   */
  function build_header() {
    if ($this->method == 'POST') {
      $header = "POST " . $this->path . " HTTP/1.0\r\n";
      $header .= "Host: " . $this->host . "\r\n";
      $header .= "Content-type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-length: " . strlen($this->data) . "\r\n\r\n";
      $header .= $this->data;
    } elseif ($this->method == 'GET') {
      if (strlen($this->path . $this->data) > 2048) {
        insertLogSceau(get_class($this) . " : __construct", "Maximum length in get method reached(" . strlen($this->path . $this->data) . ")");
      }
      $header = "GET " . $this->path . '?' . $this->data . " HTTP/1.1\r\n";
      $header .= "Host: " . $this->host . "\r\n";
      $header .= "Connection: close\r\n\r\n";
    }

    return ($header);
  }

  /**
   * sends the request to host and returns the response
   * 
   * @return string
   */
  function send() {
    //builds header
    $header = $this->build_header();

    //connects to the server and send header and gets the response
    $this->response = $this->connect($header);

    //return the response without header
    return $this->getContent();
  }

  /**
   * connects to a server, reaches the path and returns the response if connexion succeed, false otherwise
   *
   * @param string $header request header
   * @return mixed
   */
  function connect($header) {
    //connects with SSL protocol if secure connection asked, HTTP protocol otherwise
    if ($this->is_ssl)
      $socket = fsockopen('ssl://' . $this->host, $this->port, $this->errno, $this->errstr, SceauSocket::TIMEOUT);
    else
      $socket = fsockopen($this->host, $this->port);

    //if connection established
    if ($socket !== false) {
      $res = '';

      //sends header and reads response
      if (@fputs($socket, $header))
        while (!feof($socket))
          $res .= fgets($socket, 128);
      //if header sending is impossible : log
      else {
        insertLogSceau(__METHOD__ . ' : ' . __LINE__, "Envoi des données impossible sur : " . $host);
        $res = false;
      }
      //closes the connexion
      fclose($socket);
    } else { //if connection failed, log
      insertLogSceau(__METHOD__ . ' : ' . __LINE__, "Connexion socket impossible sur l'hôte $host. Erreur " . $this->errno . " : " . $this->errstr);
      $res = false;
    }

    //return the response
    return $res;
  }

  /**
   * splits header and body response and returns header
   *
   * @return string
   */
  public function getContentHeader() {
    return preg_replace('#(.+)(\r\n){2}(.+)$#s', '$1', $this->response);
  }

  /**
   * splits header and body response and returns body
   *
   * @return string
   */
  public function getContent() {
    return preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response);
  }

}
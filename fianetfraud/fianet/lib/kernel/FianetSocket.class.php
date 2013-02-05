<?php

/**
 * classe de connection en fsockopen
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class FianetSocket extends Mother {
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
   * initialise une connexion à un serveur
   *
   * @param string $url adresse à atteindre, sans les paramètres (si méthode GET)
   * @param string $method méthode d'envoi de la requête
   * @param array $data variables envoyées (si méthode GET)
   */
  public function __construct($url, $method = 'GET', array $data = null) {
    //enregistrement en local de ma méthode de connexion
    if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST') {
      $this->method = strtoupper($method);
    } else {
      $msg = "La méthode demandée ($method) n'est pas reconnue.";
      insertLog(__METHOD__ . " : " . __LINE__, $msg);
      throw new Exception($msg);
    }

    //si les données entrées en paramètre sont sous forme de tableau on construit la chaine qui en découle
    if (!is_null($data)) {
      $this->data = http_build_query($data);
    }

    //nettoyage de l'url
    //Attention !! écrase la valeur actuelle de $this->data si des paramètres url sont compris dans $url
    $this->parseUrl($url);
  }

  /**
   * nettoie l'url appelée pour séparer hôte et script
   *
   * @param string $url url du script appelé
   */
  public function parseUrl($url) {
    //split
    preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

    $components = parse_url($url);
    extract($components);
    //si on trouve une adresse non sécurisée
    if ($scheme == 'http') {
      //le mode ssl est spécifié à faux
      $this->is_ssl = false;
      //on spécifie un port 80
      $this->port = 80;
    }
    if ($scheme == 'https') {
      //le mode ssl est spécifié à vrai
      $this->is_ssl = true;
      //on spécifie un port 443
      $this->port = 443;
    }
    //on spécifie l'hôte
    $this->host = $host;
    //on spécifie le script
    $this->path = $path;

    if (isset($query))
      $this->data = $query;
  }

  /**
   * construit l'entête du fichier envoyé au serveur permettant l'accès au script demandé
   *
   * @return type
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
        insertLog(__METHOD__ . " : " . __LINE__, "Maximum length in get method reached(" . strlen($this->path . $this->data) . ")");
      }
      $header = "GET " . $this->path . '?' . $this->data . " HTTP/1.1\r\n";
      $header .= "Host: " . $this->host . "\r\n";
      $header .= "Connection: close\r\n\r\n";
    }

    return ($header);
  }

  /**
   * envoi la requête à l'hôte et retourne la réponse
   * 
   * @return resource 
   */
  function send() {
    //construction du header à envoyer
    $header = $this->build_header();

    //connexion au serveur avec les données à atteinder et à envoyer
    $this->response = $this->connect($header);

    return $this->getContent();
  }

  /**
   * établit une connexion à un serveur, execute le script et retourne la réponse
   *
   * @param string $header header à envoyer au serveur pour atteindre le script voulu et passer les paramètres requis
   * @return type
   */
  function connect($header) {
    $error = '';
    $errno = '';
    //si la connexion est sécurisée
    if ($this->is_ssl) {
      //connexion au serveur en ssl
      $socket = fsockopen('ssl://' . $this->host, $this->port, $errno, $error, FianetSocket::TIMEOUT);

      //si la connexion n'est pas sécurisée
    } else {
      //connexion au serveur sans ssl
      $socket = fsockopen($this->host, $this->port);
    }

    //si la connexion a été établie
    if ($socket !== false) {
      $res = '';

      //envoi des données au serveur : script à appeler et paramètres
      if (@fputs($socket, $header)) {

        //lecture de la réponse
        while (!feof($socket)) {
          $res .= fgets($socket, 128);
        }

        //si l'envoi des données a échoué
      } else {
        //on log l'erreur
        insertLog(__METHOD__ . ' - ' . __LINE__, "Envoi des données impossible sur : " . $this->host);
        $res = false;
      }
      //fermeture de la connexion
      fclose($socket);
    } else {
      $msg = "Connexion socket impossible sur l'hôte " . $this->host . ". Erreur " . $errno . " : " . $error;
      insertLog(__METHOD__ . ' - ' . __LINE__, $msg);
      $res = false;
    }
    return $res;
  }

  /**
   * sépare l'entête et le corps de la réponse, et retourne le corps
   *
   * @return string
   */
  public function getContent() {
    $return = $this->response !== false ? preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response) : false;
    return $return;
  }

}
<?php

/**
 * Abstract service class, allowing the management of Fia-Net services (Certissim / Kwixo)
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class CertissimService extends CertissimMother
{

  protected $siteid;
  protected $login;
  protected $password;
  protected $passwordurlencoded;
  protected $authkey;
  protected $status;
  protected $url = array();

  public function __construct()
  {
    //service name initialization
    $name = CertissimSac::PRODUCT_NAME;

    //loads service params
    $siteparams = CertissimSpyc::YAMLLoad(SAC_ROOT_DIR.'/lib/'.$name.'/const/site_params.yml');
    foreach ($siteparams as $key => $value) {
      $funcname = "set$key";
      $this->$funcname($value);
    }

    //if service is off, log entry
    if ($this->getStatus() === false)
    {
      CertissimTools::insertLog(__METHOD__.' : '.__LINE__, 'le service '.$name.' n\'est pas activé. Vérifier le paramétrage.');
    }

    //loads service URLs
    $this->loadURLs();
  }

  /**
   * loads URLS according to the service status (prod/test)
   */
  private function loadURLs()
  {
    //service name initialization
    $name = CertissimSac::PRODUCT_NAME;
    //loads URLs if service is active
    if (!($this->getStatus() === false))
    {
      $url = CertissimSpyc::YAMLLoad(SAC_ROOT_DIR.'/lib/'.$name.'/const/url.yml');
      foreach ($url as $script => $modes) {
        $this->url[$script] = $modes[$this->getStatus()];
      }
    }
  }

  /**
   * returns the URL of the script $script according to the service status (prod or test) if exists, logs error otherwise
   *
   * @param string $script script name
   * @return string URL
   */
  public function getUrl($script)
  {
    if (!array_key_exists($script, $this->url))
    {
      $msg = "L'url pour le script $script n'existe pas ou n'est pas chargée. Vérifiez le paramétrage.";
      CertissimTools::insertLog(get_class($this).'->getUrl()', $msg);
      //throw new Exception($msg);
    }

    return $this->url[$script];
  }

  /**
   * switch the service status to the status given in parameter if it exists, logs error otherwise
   * returns trus if the switch has been done, false otherwise
   *
   * @version 3.1
   * @param bool $mode
   * @return bool success of the switch
   */
  public function switchMode($mode)
  {
    //if status asked does not exist
    if (!in_array($mode, array('test', 'prod', 'off')))
    {
      //logs error
      CertissimTools::insertLog(__FILE__, "Le mode '$mode' n'est pas reconnu.");
      //stop the process
      return false;
    }

    //update the status
    $this->setStatus($mode);

    //reload URLs according to the new service status
    $this->loadURLs();
  }

  /**
   * saves parameters in the conf file
   * returns true if saved with success, false otherwise
   *
   * @version 3.1
   * @return bool
   */
  public function saveParamInFile()
  {
    $name = CertissimSac::PRODUCT_NAME;
    //load site parameters
    $siteparams = CertissimSpyc::YAMLLoad(SAC_ROOT_DIR.'/lib/'.$name.'/const/site_params.yml');

    foreach ($siteparams as $param => $value) {
      $funcname = "get$param";
      $newparams[$param] = $this->$funcname();
    }

    $yaml_string = CertissimSpyc::YAMLDump($newparams);
    $handle = fopen(SAC_ROOT_DIR.'/lib/'.$name.'/const/site_params.yml', 'w');
    $written = @fwrite($handle, $yaml_string);
    fclose($handle);

    return $written;
  }

  public function __call($name, array $params)
  {
    //if method called begins with 'getUrl'
    if (preg_match('#^getUrl.+$#', $name) > 0)
    {
      //returns the URL asked getting the suffix of the called method
      return $this->getUrl(preg_replace('#^getUrl(.+)$#', '$1', $name));
    }

    return parent::__call($name, $params);
  }

}
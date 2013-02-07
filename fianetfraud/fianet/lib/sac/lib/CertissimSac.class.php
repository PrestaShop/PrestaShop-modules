<?php

/**
 * Certissim class, allow to access the scripts and webservices of the SAC
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimSac extends CertissimService
{

  const PRODUCT_NAME = 'sac';
  const INPUT_TYPE = 'text';
  const IDSEPARATOR = '^'; //refid separator for the service getvalidstack
  const CONSULT_MODE_MINI = 'mini';
  const CONSULT_MODE_FULL = 'full';

  /**
   * generates the submission form to the service redirect.cgi
   * 
   * @param CertissimXMLElement $controlcallback Order stream
   * @param type $urlcallback URL whereto redirect the customer
   * @param type $paracallback additionnal params
   * @param type $submittype submission type (auto, input submit, image)
   * @param type $imagepath path to the img for submission by click on image
   * @return CertissimForm
   */
  public function generateRedirectForm(CertissimXMLElement $controlcallback, $urlcallback, $paracallback, $submittype = Form::SUBMIT_STANDARD, $imagepath = null)
  {
    //if additionnal params given as a CertissimXMLElement object, gets the XML string
    if (isXMLElement($paracallback))
      $paracallback = $paracallback->getXML();

    //sets the form fields
    $fields = array(
      'siteid' => array('type' => Sac::INPUT_TYPE, 'name' => 'siteid', 'value' => $this->getSiteId()),
      'controlcallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'controlcallback', 'value' => preg_replace('#"#', "'", $controlcallback->getXML())),
      'urlcallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'urlcallback', 'value' => $urlcallback),
      'paracallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'paracallback', 'value' => $paracallback),
    );

    //form initialization
    $form = new CertissimForm($this->getUrlredirect(), 'submit_fianet_xml', 'POST', $fields);

    //adding the submit
    switch ($submittype)
    {
      case Form::SUBMIT_IMAGE:
        $form->addImageSubmit($imagepath, 'payer', 'Payer', 'Payer', 'image_sumbit');
        break;

      case Form::SUBMIT_STANDARD:
        $form->addSubmit();
        break;

      case Form::SUBMIT_AUTO:
        $form->setAutosubmit(true);
        break;

      default:
        $msg = "Type submit non reconnu.";
        CertissimTools::insertLog(__METHOD__." : ".__LINE__, $msg);
        break;
    }

    return $form;
  }

  /**
   * sends a transaction to Certissim using POST method and webservice singet.cgi
   *
   * @param XMLElement $xml order stream
   * @param mixed $paracallback additional params
   * @return string response from the webservice
   */
  public function sendSinget(CertissimXMLElement $xml)
  {
    $data = array(
      'siteid' => $this->getSiteId(),
      'controlcallback' => $xml->getXML(),
    );
    $con = new CertissimFianetSocket($this->getUrlsinget(), 'POST', $data);
    $res = $con->send();
    return $res;
  }

  /**
   * send a transactions stack using stacking.cgi
   *
   * @param XMLElement $stack transactions stack
   * @return string response of the webservice
   */
  public function sendStacking(CertissimXMLElement $stack)
  {
    $data = array(
      'siteid' => $this->getSiteId(),
      'controlcallback' => $stack->getXML(),
    );
    $con = new CertissimFianetSocket($this->getUrlstacking(), 'POST', $data);
    $result = $con->send();

    $xmlresult = ($result !== false ? new CertissimXMLElement($result) : false);

    return $xmlresult;
  }

  /**
   * send a transactions stack using stackfast.cgi
   *
   * @param XMLElement $stack transactions stack
   * @return string response of the webservice
   */
  public function sendStackfast(CertissimXMLElement $stack)
  {
    $data = array(
      'siteid' => $this->getSiteId(),
      'controlcallback' => $stack->getXML(),
    );
    $con = new CertissimFianetSocket($this->getUrlstacking(), 'POST', $data);
    return new CertissimXMLElement($con->send());
  }

  /**
   * calls the service getvalidation to get the score of an order
   *
   * @param string $refid order ref
   * @param string $mode answer type
   * @param bool $repFT displya or not the FT answer
   * 
   * @return string reponse of the webservice
   */
  public function getValidation($refid, $mode = 'mini', $repFT = '0')
  {
    $data = array(
      'SiteID' => $this->getSiteId(),
      'Pwd' => $this->getPassword(),
      'RefID' => $refid,
      'Mode' => $mode,
      'RepFT' => $repFT
    );
    $con = new CertissimFianetSocket($this->getUrlgetvalidation(), 'POST', $data);
    return new CertissimXMLElement($con->send());
  }

  /**
   * calls the validation webservice to get the score of an order and send the response to $urlback
   *
   * @param string $refid order ref
   * @param string $mode answer mode
   * @param bool $repFT display or not FT answer
   * @param string $urlback URL whereto send the response of the webservice
   * @return string response of the webservice
   */
  public function getRedirectValidation($refid, $mode = Sac::CONSULT_MODE_MINI, $urlback = null, $repFT = '0')
  {
    $data = array(
      'SiteID' => $this->getSiteId(),
      'Pwd' => $this->getPassword(),
      'RefID' => $refid,
      'Mode' => $mode,
      'RepFT' => $repFT,
      'urlBack' => (!is_null($urlback) ? $urlback : $this->getUrldefaultredirectvaildationurlback()),
    );
    $con = new CertissimFianetSocket($this->getUrlredirectvalidation(), 'POST', $data);
    return new CertissimXMLElement($con->send());
  }

  /**
   * returns the scores list of the transactions given in param
   *
   * @param array $listId orders ref
   * @param string $mode answer type
   * @param bool $repFT display or not FT answer
   * @return string response of the webservice
   */
  public function getValidstackByReflist(array $listId, $mode = Sac::CONSULT_MODE_MINI, $repFT = '0')
  {
    //builds the refid list
    $list = implode(CertissimSac::IDSEPARATOR, $listId);

    $data = array(
      'SiteID' => $this->getSiteId(),
      'Pwd' => $this->getPassword(),
      'Mode' => $mode,
      'RepFT' => $repFT,
      'ListID' => $list,
      'Separ' => Sac::IDSEPARATOR
    );
    return $this->getValidstack($data);
  }

  /**
   * returns the scores list of every order made at the date $date
   *
   * @param array $date date
   * @param int $numpage number of the page to read
   * @param string $mode answer type
   * @param bool $repFT display or not FT answer
   * @return string response of the webservice
   */
  public function getValidstackByDate($date, $numpage, $mode = Sac::CONSULT_MODE_MINI, $repFT = '0')
  {
    //checks the date format
    if (!preg_match('#^[0-9]{2}/[0-1][0-9]/[0-9]{4}$#', $date))
    {
      $msg = "La date '$date' n'est pas au bon format. Format attendu : dd/mm/YYYY";
      CertissimTools::insertLog(__METHOD__." : ".__LINE__, $msg);
      throw new Exception($msg);
    }

    $data = array(
      'SiteID' => $this->getSiteId(),
      'Pwd' => $this->getPassword(),
      'Mode' => $mode,
      'RepFT' => $repFT,
      'DtStack' => $date,
      'Ind' => $numpage
    );
    return $this->getValidstack($data);
  }

  /**
   * calls get_validstack.cgi webservice
   *
   * @param array $param call parameters
   * @return string response of the webservice
   */
  private function getValidstack($param)
  {
    $con = new CertissimFianetSocket($this->getUrlgetvalidstack(), 'POST', $param);
    return new CertissimXMLElement($con->send());
  }

  /**
   * gets the list of the reevaluated transactions
   * 
   * @param type $mode call mode (all, new, old)
   * @param type $output answer type
   * @param type $repFT display or not the FT answer
   * @return CertissimXMLElement reevaluations list
   */
  public function getAlert($mode = 'all', $output = 'mini', $repFT = '0')
  {
    $data = array(
      'SiteID' => $this->getSiteId(),
      'Pwd' => $this->getPassword(),
      'Mode' => $mode,
      'Output' => $output,
      'RepFT' => $repFT,
    );
    $con = new CertissimFianetSocket($this->getUrlgetalert(), 'POST', $data);
    return new CertissimXMLElement($con->send());
  }

  /**
   * returns the visu check detail URL for the order $rid
   *
   * @param string $rid order ref
   * @param string $txt text to display as a link
   * @param string $target target attribute
   * @return string HTML link
   */
  public function getVisuCheckUrl($rid, $txt = null, $target = '_blank')
  {
    $url = $this->getUrlvisucheckdetail();
    $url .= '?sid='.$this->getSiteid().'&log='.$this->getLogin().'&pwd='.$this->getPasswordurlencoded()."&rid=$rid";

    $link = "<a href='$url'".(!is_null($target) ? " target='$target'" : '').">$rid</a>";

    return $link;
  }

}
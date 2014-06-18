<?php
/**
 * Message.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class Message {
  private $Summary; // string
  private $Details; // string
  private $HelpLink; // string
  private $RefersTo; // string
  private $Severity; // SeverityLevel
  private $Source; // string
  private $Name; // string

  public function setSummary($value){$this->Summary=$value;} // string
  public function getSummary(){return $this->Summary;} // string

  public function setDetails($value){$this->Details=$value;} // string
  public function getDetails(){return $this->Details;} // string

  public function setHelpLink($value){$this->HelpLink=$value;} // string
  public function getHelpLink(){return $this->HelpLink;} // string

  public function setRefersTo($value){$this->RefersTo=$value;} // string
  public function getRefersTo(){return $this->RefersTo;} // string

  public function setSeverity($value){$this->Severity=$value;} // SeverityLevel
  public function getSeverity(){return $this->Severity;} // SeverityLevel

  public function setSource($value){$this->Source=$value;} // string
  public function getSource(){return $this->Source;} // string

  public function setName($value){$this->Name=$value;} // string
  public function getName(){return $this->Name;} // string

}

?>

<?php
/**
 * IsAuthorizedResult.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class IsAuthorizedResult {
  private $Operations; // string
  private $Expires; // dateTime

  public function setOperations($value){$this->Operations=$value;} // string
  public function getOperations(){return $this->Operations;} // string

  public function setExpires($value){$this->Expires=$value;} // dateTime
  public function getExpires(){return $this->Expires;} // dateTime

}

?>

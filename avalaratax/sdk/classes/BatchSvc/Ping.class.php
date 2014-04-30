<?php
/**
 * Ping.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class Ping {
  private $Message; // string

  public function setMessage($value){$this->Message=$value;} // string
  public function getMessage(){return $this->Message;} // string

}

?>

<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Unique exception class for Prediggo.
 *
 * @package prediggo4php
 * @subpackage errors
 *
 * @author Stef
 */
class PrediggoException extends  Exception
{

    /**
     * Code for prediggo related errors.
     */
    const ERR_PREDIGGO = 100;

    /**
     * Code for cURL related errors.
     */
    const ERR_CURL = 200;

    /**
     * Code for XML related errors.
     */
    const ERR_XML  = 300;

  /**
   * Constructor
   * @param string $message the error message
   * @param integer $code the  error code
   */
  public function __construct($message, $code = 0)
  {
       parent::__construct($message, $code );

  }

}

<?php
/**
 * CommunicationMode.class.php
 */

/**
 * CommunicationMode indicates the mode to use for communicating with the customer.
 * @see CertificateRequestInitiateRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CommunicationMode {

/**
 * The value has not been set. 
 */
  public static $NULL = 'NULL';

/**
 * Email address 
 */
  public static $EMAIL = 'EMAIL';

/**
 * Mail address 
 */
  public static $MAIL = 'MAIL';

/**
 * Fax number 
 */
  public static $FAX = 'FAX';
  
	public static function Values()
	{
		return array(
			CommunicationMode::$NULL,
			CommunicationMode::$EMAIL,
			CommunicationMode::$MAIL,
			CommunicationMode::$FAX
					
		);
	}

}

?>
<?php
/**
 * CertificateRequestStatus.class.php
 */

/**
 * CertificateStatus indicates the current status of the Request associated with a Request record to include in the response. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequestStatus extends Enum {

/**
 * Request of any status. 
 */
  public static $ALL = 'ALL';

/**
 * Request has been initiated and is currently open. 
 */
  public static $OPEN = 'OPEN';

/**
 * Request has been closed, either manually or automatically. 
 */
  public static $CLOSED = 'CLOSED';
  
	public static function Values()
	{
		return array(
			CertificateRequestStatus::$ALL,
			CertificateRequestStatus::$OPEN,	
			CertificateRequestStatus::$CLOSED
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>

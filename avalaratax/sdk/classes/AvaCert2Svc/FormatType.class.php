<?php
/**
 * FormatType.class.php
 */

/**
 * FormatType is the format in which the image needs to be exported.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class FormatType extends Enum {

/**
 * The value has not been set. 
 */
  public static $NULL = 'NULL';

/**
 * PNG is a bitmapped binary image in the 1-bit PNG format (default value). 
 */
  public static $PNG = 'PNG';

/**
 * PDF is a document in Portable document format with images of every page in the certificate. 
 */
  public static $PDF = 'PDF';

	public static function Values()
	{
		return array(
			FormatType::$NULL,
			FormatType::$PNG,
			FormatType::$PDF	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>

<?php
/**
 * CancelCode.class.php
 */

/**
 * A cancel code is set on a {@link CancelTaxRequest} and specifies the reason the
 * tax calculation is being canceled (or in the case of posting, returned to its prior state).
 * @see CancelTaxRequest
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
*/
class CancelCode extends Enum
{


	 /**
     * The operation failed for an unknown reason.
     */

	public static $Unspecified			= 'Unspecified';

	 /**
     * Specifies the post operation failed when attempting to post an invoice within
     * a client's application, for example, to the client's General Ledger; The
     * document's status will be changed to <b>Saved</b>.
     */

    public static $PostFailed			= 'PostFailed';

    /**
     * Specifies the document was deleted within the client's application and
     * should be removed from the AvaTax records; If the document within AvaTax
     * is already committed, the document status will be changed to <b>Cancelled</b>
     * and retained for historical records;  If the document was not committed,
     * (was <b>Saved</b> or <b>Posted</b>) the document will be deleted within AvaTax.
     */

    public static $DocDeleted			= 'DocDeleted';
    
    /**
     * Specifies the document was voided within the client's application and
     * should be removed from the AvaTax records; If the document within AvaTax
     * is already committed, the document status will be changed to <b>Cancelled</b>
     * and retained for historical records;  If the document was not committed,
     * (was <b>Saved</b> or <b>Posted</b>) the document will be deleted within AvaTax.
	 */
    public static $DocVoided			= 'DocVoided';
    public static $AdjustmentCancelled	= 'AdjustmentCancelled';

    
	public static function Values()
	{
		return array(
			CancelCode::$Unspecified,
			CancelCode::$PostFailed,
			CancelCode::$DocDeleted,
			CancelCode::$DocVoided,
			CancelCode::$AdjustmentCancelled
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }	
	
}

?>
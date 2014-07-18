<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Exception thrown when an EMS callback cannot be interpreted
 */
class Syspay_Merchant_EMSException extends RuntimeException
{
    const CODE_MISSING_HEADER   = 0;
    const CODE_INVALID_CHECKSUM = 1;
    const CODE_INVALID_CONTENT  = 2;
    const CODE_UNKNOWN          = 3;
    const CODE_UNKNOWN_MERCHANT = 5;

    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}

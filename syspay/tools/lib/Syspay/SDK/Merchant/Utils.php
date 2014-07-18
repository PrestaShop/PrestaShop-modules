<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Placeholder for various utility functions. All methods are static and this class cannot be instantiated.
 */
class Syspay_Merchant_Utils
{
    /**
     * Prevent instantiation
     */
    private final function __construct() {}

    /**
     * Generate a checksum
     * @param  string $data       The data to get the checksum for
     * @param  string $passphrase The passphrase
     * @return string Checksum
     */
    public static function getChecksum($data, $passphrase)
    {
        return sha1($data . $passphrase);
    }

    /**
     * Validate data against a given checksum
     * @param  string $data       The data to validate
     * @param  string $passphrase The passphrase
     * @param  string $checksum   The checksum received along with the data
     * @return boolean
     */
    public static function checkChecksum($data, $passphrase, $checksum)
    {
        return self::getChecksum($data, $passphrase) === $checksum;
    }

    /**
     * Convert a timestamp to a DateTime object
     * @param integer $timestamp Unix timestamp
     * @return DateTime
     */
    public static function tsToDateTime($timestamp)
    {
        $datetime = new DateTime('@' . $timestamp);
        return $datetime;
    }
}

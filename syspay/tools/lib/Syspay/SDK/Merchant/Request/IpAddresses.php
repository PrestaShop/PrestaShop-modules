<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get a list of the SysPay ip addresses
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-system-ip-addresses
 */
class Syspay_Merchant_IpAddressesRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/system-ip';

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return self::METHOD;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return self::PATH;
    }

    public function buildResponse(stdClass $response)
    {
        if (!isset($response->ip_addresses) || !is_array($response->ip_addresses)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "ip_addresses" data from response', $response);
        }

        return $response->ip_addresses;
    }
}

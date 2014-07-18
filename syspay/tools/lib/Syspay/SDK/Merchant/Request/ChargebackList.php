<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Export a list of chargebacks
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-chargebacks
 */
class Syspay_Merchant_ChargebackListRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/chargebacks/';

    /**
     * @var array
     */
    private $filters;

    public function __construct()
    {
        $this->filters = array();
    }

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
        return sprintf(self::PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->filters;
    }

    /**
     * Set a filter to use when querying the API
     * @param  string $key   Filter key
     * @param  string $value Filter value
     * @return self
     */
    public function addFilter($key, $value)
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Delete a filter
     * @param  string $key Filter key to delete
     * @return self
     */
    public function deleteFilter($key)
    {
        if (isset($this->filters[$key])) {
            unset($this->filters[$key]);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->chargebacks) || !is_array($response->chargebacks)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "chargebacks" data from response', $response);
        }

        $chargebacks = array();

        foreach ($response->chargebacks as $r) {
            $chargeback = Syspay_Merchant_Entity_Chargeback::buildFromResponse($r);
            array_push($chargebacks, $chargeback);
        }

        return $chargebacks;
    }

}


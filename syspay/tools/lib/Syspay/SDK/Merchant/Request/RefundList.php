<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Export a list of refunds
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-refunds
 */
class Syspay_Merchant_RefundListRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/refunds/';

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
        if (!isset($response->refunds) || !is_array($response->refunds)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "refunds" data from response', $response);
        }

        $refunds = array();

        foreach ($response->refunds as $r) {
            $refund = Syspay_Merchant_Entity_Refund::buildFromResponse($r);
            array_push($refunds, $refund);
        }

        return $refunds;
    }

}


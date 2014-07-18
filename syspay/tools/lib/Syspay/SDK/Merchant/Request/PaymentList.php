<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Export a list of payments
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-payments
 */
class Syspay_Merchant_PaymentListRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/payments/';

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
        if (!isset($response->payments) || !is_array($response->payments)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "payment" data from response', $response);
        }

        $payments = array();

        foreach ($response->payments as $p) {
            $payment = Syspay_Merchant_Entity_Payment::buildFromResponse($p);
            array_push($payments, $payment);
        }

        return $payments;
    }

}


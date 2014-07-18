<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get information about a plan
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_subscription.html#get-plan-information
 */
class Syspay_Merchant_PlanInfoRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/plan/%d';

    /**
     * @var integer
     */
    private $planId;

    public function __construct($planId = null)
    {
        if (null !== $planId) {
            $this->setPlanId($planId);
        }
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
        return sprintf(self::PATH, $this->planId);
    }

    /**
     * Gets the value of planId.
     *
     * @return integer
     */
    public function getPlanId()
    {
        return $this->planId;
    }

    /**
     * Sets the value of planId.
     *
     * @param integer $planId the planId
     *
     * @return self
     */
    public function setPlanId($planId)
    {
        $this->planId = $planId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->plan)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "plan" data from response', $response);
        }

        $plan = Syspay_Merchant_Entity_Plan::buildFromResponse($response->plan);

        return $plan;
    }

}

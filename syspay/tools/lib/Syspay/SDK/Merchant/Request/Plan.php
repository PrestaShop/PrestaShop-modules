<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Create a plan
 * @see https://app.syspay.com/docs/api/merchant_subscription.html#create-a-plan
 */
class Syspay_Merchant_PlanRequest extends Syspay_Merchant_Request
{
    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/plan';

    /**
     * @var Syspay_Merchant_Entity_Plan
     */
    private $plan;

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

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->plan)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrive "plan" data from response', $response);
        }

        $plan = Syspay_Merchant_Entity_Plan::buildFromResponse($response->plan);
        return $plan;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = array();
        if (false === empty($this->plan)) {
            $data = $this->plan->toArray();
        }
        return $data;
    }


    /**
     * Gets the value of plan.
     *
     * @return Syspay_Merchant_Entity_Plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Sets the value of plan.
     *
     * @param Syspay_Merchant_Entity_Plan $plan the plan
     *
     * @return self
     */
    public function setPlan(Syspay_Merchant_Entity_Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }
}

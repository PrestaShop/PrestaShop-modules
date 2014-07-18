<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A subscription object
 */
class Syspay_Merchant_Entity_Subscription extends Syspay_Merchant_Entity
{
    const TYPE = 'subscription';

    const STATUS_PENDING   = 'PENDING'; // First payment not yet successful
    const STATUS_ACTIVE    = 'ACTIVE'; // Active susbscription
    const STATUS_CANCELLED = 'CANCELLED'; // The first payment failed, the subscription is cancelled
    const STATUS_ENDED     = 'ENDED'; // Subscription has ended

    const PHASE_NEW       = 'NEW';
    const PHASE_TRIAL     = 'TRIAL';
    const PHASE_BILLING   = 'BILLING';
    const PHASE_RETRY     = 'RETRY';
    const PHASE_LAST      = 'LAST';
    const PHASE_CLOSED    = 'CLOSED';

    const END_REASON_UNSUBSCRIBED_MERCHANT = 'UNSUBSCRIBED_MERCHANT'; // Merchant stopped the subscription via his interface or via the API
    const END_REASON_UNSUBSCRIBED_ADMIN    = 'UNSUBSCRIBED_ADMIN'; // Admin stopped the subscription via the admin interface
    const END_REASON_SUSPENDED_ATTEMPTS    = 'SUSPENDED_ATTEMPTS'; // Max retry attempts reached for one payment
    const END_REASON_SUSPENDED_EXPIRED     = 'SUSPENDED_EXPIRED'; // Payment method has expired
    const END_REASON_SUSPENDED_CHARGEBACK  = 'SUSPENDED_CHARGEBACK'; // A chargeback was received on this subscription
    const END_REASON_COMPLETE              = 'COMPLETE'; // The subscription is complete

    /**
     * @var integer
     */
    private $id;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var DateTime
     */
    private $start_date;

    /**
     * @var DateTime
     */
    private $end_date;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $phase;

    /**
     * @var string
     */
    private $end_reason;

    /**
     * @var Syspay_Merchant_Entity_PaymentMethod
     */
    private $payment_method;

    /**
     * @var integer
     */
    private $website;

    /**
     * @var string
     */
    protected $ems_url;

    /**
     * @var string
     */
    protected $redirect_url;

    /**
     * @var Syspay_Merchant_Entity_Plan
     */
    protected $plan;

    /**
     * @var Syspay_Merchant_Entity_Customer
     */
    private $customer;

    /**
     * @var integer
     */
    protected $plan_id;

    /**
     * @var string
     */
    private $plan_type;

    /**
     * @var string
     */
    protected $extra;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    private $redirect;

    /**
     * Build a subscription entity based on a json-decoded subscription stdClass
     *
     * @param  stdClass $response The subscription data
     * @return Syspay_Merchant_Entity_Subscription The subscription object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $subscription = new self();
        $subscription->setId(isset($response->id)?$response->id:null);
        $subscription->setPlanId(isset($response->plan_id)?$response->plan_id:null);
        $subscription->setPlanType(isset($response->plan_type)?$response->plan_type:null);
        $subscription->setReference(isset($response->reference)?$response->reference:null);
        $subscription->setStatus(isset($response->status)?$response->status:null);
        $subscription->setPhase(isset($response->phase)?$response->phase:null);
        $subscription->setExtra(isset($response->extra)?$response->extra:null);
        $subscription->setCreated(isset($response->created)?Syspay_Merchant_Utils::tsToDateTime($response->created):null);
        $subscription->setStartDate(isset($response->start_date)?Syspay_Merchant_Utils::tsToDateTime($response->start_date):null);
        $subscription->setEndDate(isset($response->end_date)?Syspay_Merchant_Utils::tsToDateTime($response->end_date):null);
        $subscription->setEndReason(isset($response->end_reason)?$response->end_reason:null);

        if (isset($response->payment_method)
                && ($response->payment_method instanceof stdClass)) {
            $paymentMethod = Syspay_Merchant_Entity_PaymentMethod::buildFromResponse($response->payment_method);
            $subscription->setPaymentMethod($paymentMethod);
        }

        if (isset($response->customer)
                && ($response->customer instanceof stdClass)) {
            $customer = Syspay_Merchant_Entity_Customer::buildFromResponse($response->customer);
            $subscription->setCustomer($customer);
        }

        return $subscription;
    }

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of created.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the value of created.
     *
     * @param DateTime $created the created
     *
     * @return self
     */
    public function setCreated(DateTime $created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Gets the value of start_date.
     *
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Sets the value of start_date.
     *
     * @param DateTime $start_date the start_date
     *
     * @return self
     */
    public function setStartDate(DateTime $start_date = null)
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Gets the value of end_date.
     *
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Sets the value of end_date.
     *
     * @param DateTime $end_date the end_date
     *
     * @return self
     */
    public function setEndDate(DateTime $end_date = null)
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets the value of phase.
     *
     * @return string
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Sets the value of phase.
     *
     * @param string $phase the phase
     *
     * @return self
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Gets the value of end_reason.
     *
     * @return string
     */
    public function getEndReason()
    {
        return $this->end_reason;
    }

    /**
     * Sets the value of end_reason.
     *
     * @param string $end_reason the end_reason
     *
     * @return self
     */
    public function setEndReason($end_reason)
    {
        $this->end_reason = $end_reason;

        return $this;
    }

    /**
     * Gets the value of payment_method.
     *
     * @return Syspay_Merchant_Entity_PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Sets the value of payment_method.
     *
     * @param Syspay_Merchant_Entity_PaymentMethod $payment_method the payment_method
     *
     * @return self
     */
    public function setPaymentMethod(Syspay_Merchant_Entity_PaymentMethod $payment_method)
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    /**
     * Gets the value of website.
     *
     * @return integer
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the value of website.
     *
     * @param integer $website the website
     *
     * @return self
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Gets the value of ems_url.
     *
     * @return string
     */
    public function getEmsUrl()
    {
        return $this->ems_url;
    }

    /**
     * Sets the value of ems_url.
     *
     * @param string $ems_url the ems_url
     *
     * @return self
     */
    public function setEmsUrl($ems_url)
    {
        $this->ems_url = $ems_url;

        return $this;
    }

    /**
     * Gets the value of redirect_url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * Sets the value of redirect_url.
     *
     * @param string $redirect_url the redirect_url
     *
     * @return self
     */
    public function setRedirectUrl($redirect_url)
    {
        $this->redirect_url = $redirect_url;

        return $this;
    }

    /**
     * Gets the value of customer.
     *
     * @return Syspay_Merchant_Entity_Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets the value of customer.
     *
     * @param Syspay_Merchant_Entity_Customer $customer the customer
     *
     * @return self
     */
    public function setCustomer(Syspay_Merchant_Entity_Customer $customer)
    {
        $this->customer = $customer;

        return $this;
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

    /**
     * Gets the value of plan_id.
     *
     * @return integer
     */
    public function getPlanId()
    {
        return $this->plan_id;
    }

    /**
     * Sets the value of plan_id.
     *
     * @param integer $plan_id the plan_id
     *
     * @return self
     */
    public function setPlanId($plan_id)
    {
        $this->plan_id = $plan_id;

        return $this;
    }

    /**
     * Gets the value of plan_type.
     *
     * @return string
     */
    public function getPlanType()
    {
        return $this->plan_type;
    }

    /**
     * Sets the value of plan_type.
     *
     * @param integer $plan_type the plan_type
     *
     * @return self
     */
    public function setPlanType($plan_type)
    {
        $this->plan_type = $plan_type;

        return $this;
    }

    /**
     * Gets the value of extra.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets the value of extra.
     *
     * @param string $extra the extra
     *
     * @return self
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Gets the value of reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the value of reference.
     *
     * @param string $reference the reference
     *
     * @return self
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Gets the value of redirect.
     *
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Sets the value of redirect.
     *
     * @param string $redirect the redirect
     *
     * @return self
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        $data = parent::toArray();
        if (false === empty($this->plan)) {
            $data['plan'] = $this->plan->toArray();
        }
        return $data;
    }

}

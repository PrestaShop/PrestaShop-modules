<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A plan object
 */
class Syspay_Merchant_Entity_Plan extends Syspay_Merchant_Entity
{
    const TYPE = 'plan';

    const UNIT_MINUTE = 'minute';
    const UNIT_HOUR   = 'hour';
    const UNIT_DAY    = 'day';
    const UNIT_WEEK   = 'week';
    const UNIT_MONTH  = 'month';
    const UNIT_YEAR   = 'year';

    const TYPE_SUBSCRIPTION = 'SUBSCRIPTION';
    const TYPE_INSTALMENT   = 'INSTALMENT';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var integer
     */
    protected $trial_amount;

    /**
     * @var integer
     */
    protected $trial_period;

    /**
     * @var string
     */
    protected $trial_period_unit;

    /**
     * @var integer
     */
    protected $trial_cycles;

    /**
     * @var integer
     */
    protected $initial_amount;

    /**
     * @var integer
     */
    protected $billing_amount;

    /**
     * @var integer
     */
    protected $billing_period;

    /**
     * @var string
     */
    protected $billing_period_unit;

    /**
     * @var integer
     */
    protected $billing_cycles;

    /**
     * @var integer
     */
    protected $retry_map_id;

    /**
     * @var integer
     */
    protected $total_amount;

    /**
     * Build a plan entity based on a json-decoded plan stdClass
     *
     * @param  stdClass $response The plan data
     * @return Syspay_Merchant_Entity_Plan The plan object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $plan = new self();
        $plan->setId(isset($response->id)?$response->id:null);
        $plan->setStatus(isset($response->status)?$response->status:null);
        $plan->setName(isset($response->name)?$response->name:null);
        $plan->setDescription(isset($response->description)?$response->description:null);
        $plan->setCurrency(isset($response->currency)?$response->currency:null);
        $plan->setTrialAmount(isset($response->trial_amount)?$response->trial_amount:null);
        $plan->setTrialPeriod(isset($response->trial_period)?$response->trial_period:null);
        $plan->setTrialPeriodUnit(isset($response->trial_period_unit)?$response->trial_period_unit:null);
        $plan->setTrialCycles(isset($response->trial_cycles)?$response->trial_cycles:null);
        $plan->setBillingAmount(isset($response->billing_amount)?$response->billing_amount:null);
        $plan->setBillingPeriod(isset($response->billing_period)?$response->billing_period:null);
        $plan->setBillingPeriodUnit(isset($response->billing_period_unit)?$response->billing_period_unit:null);
        $plan->setBillingCycles(isset($response->billing_cycles)?$response->billing_cycles:null);
        $plan->setInitialAmount(isset($response->initial_amount)?$response->initial_amount:null);
        $plan->setRetryMapId(isset($response->retry_map_id)?$response->retry_map_id:null);
        $plan->setType(isset($response->type)?$response->type:null);

        if (isset($response->created)
                && !is_null($response->created)) {
            $plan->setCreated(Syspay_Merchant_Utils::tsToDateTime($response->created));
        }

        return $plan;
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
    public function setCreated(DateTime $created)
    {
        $this->created = $created;

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
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the value of currency.
     *
     * @param string $currency the currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Gets the value of trial_amount.
     *
     * @return integer
     */
    public function getTrialAmount()
    {
        return $this->trial_amount;
    }

    /**
     * Sets the value of trial_amount.
     *
     * @param integer $trial_amount the trial_amount
     *
     * @return self
     */
    public function setTrialAmount($trial_amount)
    {
        $this->trial_amount = $trial_amount;

        return $this;
    }

    /**
     * Gets the value of trial_period.
     *
     * @return integer
     */
    public function getTrialPeriod()
    {
        return $this->trial_period;
    }

    /**
     * Sets the value of trial_period.
     *
     * @param integer $trial_period the trial_period
     *
     * @return self
     */
    public function setTrialPeriod($trial_period)
    {
        $this->trial_period = $trial_period;

        return $this;
    }

    /**
     * Gets the value of trial_period_unit.
     *
     * @return string
     */
    public function getTrialPeriodUnit()
    {
        return $this->trial_period_unit;
    }

    /**
     * Sets the value of trial_period_unit.
     *
     * @param string $trial_period_unit the trial_period_unit
     *
     * @return self
     */
    public function setTrialPeriodUnit($trial_period_unit)
    {
        $this->trial_period_unit = $trial_period_unit;

        return $this;
    }

    /**
     * Gets the value of trial_cycles.
     *
     * @return integer
     */
    public function getTrialCycles()
    {
        return $this->trial_cycles;
    }

    /**
     * Sets the value of trial_cycles.
     *
     * @param integer $trial_cycles the trial_cycles
     *
     * @return self
     */
    public function setTrialCycles($trial_cycles)
    {
        $this->trial_cycles = $trial_cycles;

        return $this;
    }

    /**
     * Gets the value of initial_amount.
     *
     * @return integer
     */
    public function getInitialAmount()
    {
        return $this->initial_amount;
    }

    /**
     * Sets the value of initial_amount.
     *
     * @param integer $initial_amount the initial_amount
     *
     * @return self
     */
    public function setInitialAmount($initial_amount)
    {
        $this->initial_amount = $initial_amount;

        return $this;
    }

    /**
     * Gets the value of billing_amount.
     *
     * @return integer
     */
    public function getBillingAmount()
    {
        return $this->billing_amount;
    }

    /**
     * Sets the value of billing_amount.
     *
     * @param integer $billing_amount the billing_amount
     *
     * @return self
     */
    public function setBillingAmount($billing_amount)
    {
        $this->billing_amount = $billing_amount;

        return $this;
    }

    /**
     * Gets the value of billing_period.
     *
     * @return integer
     */
    public function getBillingPeriod()
    {
        return $this->billing_period;
    }

    /**
     * Sets the value of billing_period.
     *
     * @param integer $billing_period the billing_period
     *
     * @return self
     */
    public function setBillingPeriod($billing_period)
    {
        $this->billing_period = $billing_period;

        return $this;
    }

    /**
     * Gets the value of billing_period_unit.
     *
     * @return string
     */
    public function getBillingPeriodUnit()
    {
        return $this->billing_period_unit;
    }

    /**
     * Sets the value of billing_period_unit.
     *
     * @param string $billing_period_unit the billing_period_unit
     *
     * @return self
     */
    public function setBillingPeriodUnit($billing_period_unit)
    {
        $this->billing_period_unit = $billing_period_unit;

        return $this;
    }

    /**
     * Gets the value of billing_cycles.
     *
     * @return integer
     */
    public function getBillingCycles()
    {
        return $this->billing_cycles;
    }

    /**
     * Sets the value of billing_cycles.
     *
     * @param integer $billing_cycles the billing_cycles
     *
     * @return self
     */
    public function setBillingCycles($billing_cycles)
    {
        $this->billing_cycles = $billing_cycles;

        return $this;
    }

    /**
     * Gets the value of retry_map_id.
     *
     * @return integer
     */
    public function getRetryMapId()
    {
        return $this->retry_map_id;
    }

    /**
     * Sets the value of retry_map_id.
     *
     * @param integer $retry_map_id the retry_map_id
     *
     * @return self
     */
    public function setRetryMapId($retry_map_id)
    {
        $this->retry_map_id = $retry_map_id;

        return $this;
    }

    /**
     * Gets the value of type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param string $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of total_amount.
     *
     * @return integer
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * Sets the value of total_amount.
     *
     * @param integer $total_amount the total_amount
     *
     * @return self
     */
    public function setTotalAmount($total_amount)
    {
        $this->total_amount = $total_amount;

        return $this;
    }

}

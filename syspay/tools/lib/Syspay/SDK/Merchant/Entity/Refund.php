<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A refund object
 */
class Syspay_Merchant_Entity_Refund extends Syspay_Merchant_Entity
{
    const TYPE = 'refund';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $extra;

    /**
     * @var Syspay_Merchant_Entity_Payment
     */
    private $payment;

    /**
     * @var DateTime
     */
    private $processingTime;

    /**
     * Build a payment entity based on a json-decoded payment stdClass
     *
     * @param  stdClass $response The payment data
     * @return Syspay_Merchant_Entity_Payment The payment object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $refund = new self();
        $refund->setId(isset($response->id)?$response->id:null);
        $refund->setReference(isset($response->reference)?$response->reference:null);
        $refund->setAmount(isset($response->amount)?$response->amount:null);
        $refund->setCurrency(isset($response->currency)?$response->currency:null);
        $refund->setStatus(isset($response->status)?$response->status:null);
        $refund->setExtra(isset($response->extra)?$response->extra:null);
        $refund->setDescription(isset($response->description)?$response->description:null);

        if (isset($response->processing_time)
                && !is_null($response->processing_time)) {
            $refund->setProcessingTime(Syspay_Merchant_Utils::tsToDateTime($response->processing_time));
        }

        if (isset($response->payment)) {
            $refund->setPayment(Syspay_Merchant_Entity_Payment::buildFromResponse($response->payment));
        }

        return $refund;
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
     * Gets the value of amount.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the value of amount.
     *
     * @param integer $amount the amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     * Gets the value of payment.
     *
     * @return Syspay_Merchant_Entity_Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Sets the value of payment.
     *
     * @param Syspay_Merchant_Entity_Payment $payment the payment
     *
     * @return self
     */
    public function setPayment(Syspay_Merchant_Entity_Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Gets the value of processingTime.
     *
     * @return DateTime
     */
    public function getProcessingTime()
    {
        return $this->processingTime;
    }

    /**
     * Sets the value of processingTime.
     *
     * @param DateTime $processingTime the processingTime
     *
     * @return self
     */
    public function setProcessingTime(DateTime $processingTime)
    {
        $this->processingTime = $processingTime;

        return $this;
    }
}

<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A chargeback object
 */
class Syspay_Merchant_Entity_Chargeback extends Syspay_Merchant_Entity
{
    const TYPE = 'chargeback';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $status;

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
    protected $reasonCode;

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
        $chargeback = new self();
        $chargeback->setId(isset($response->id)?$response->id:null);
        $chargeback->setStatus(isset($response->status)?$response->status:null);
        $chargeback->setAmount(isset($response->amount)?$response->amount:null);
        $chargeback->setCurrency(isset($response->currency)?$response->currency:null);
        $chargeback->setReasonCode(isset($response->reason_code)?$response->reason_code:null);

        if (isset($response->processing_time)
                && !is_null($response->processing_time)) {
            $chargeback->setProcessingTime(Syspay_Merchant_Utils::tsToDateTime($response->processing_time));
        }

        if (isset($response->payment)) {
            $chargeback->setPayment(Syspay_Merchant_Entity_Payment::buildFromResponse($response->payment));
        }

        return $chargeback;
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
     * Gets the value of reasonCode.
     *
     * @return string
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Sets the value of reasonCode.
     *
     * @param string $reasonCode the reasonCode
     *
     * @return self
     */
    public function setReasonCode($reasonCode)
    {
        $this->reasonCode = $reasonCode;

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

<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A payment recipient object (to use for recipient_map)
 */
class Syspay_Merchant_Entity_PaymentRecipient extends Syspay_Merchant_Entity
{
    const TYPE = 'payment_recipient';

    /**
     * The exact amount is given (the 'value' property is an amount in CENTs and the 'currency' must be supplied)
     */
    const CALC_TYPE_FIXED = 'fixed';

    /**
     * The 'value' is a percentage * 100 (100 == 1%) of the total payment amount
     */
    const CALC_TYPE_PERCENT = 'percent';

    /**
     * The SysPay user id of the recipient.
     * This is used as verification to make sure the recipient's account_id is not bogus
     * @var integer
     */
    protected $user_id;

    /**
     * The SysPay account id of the recipient
     * @var integer
     */
    protected $account_id;

    /**
     * The way the amount to transfer to the recipient is calculated. (one of the CALC_TYPE constants)
     * - CALC_TYPE_PERCENT: The `value` property is a (percentage*100) of the total payment amount
     * - CALC_TYPE_FIXED: The 'value' property is a fixed amount. In this case the 'currency' must be given
     * @var integer
     */
    protected $calc_type;

    /**
     * Value used to compute the amount
     * @var integer
     */
    protected $value;

    /**
     * The currency (mandatory in case of a FIXED calc_type)
     * This is a safety check and it must match the payment currency.
     * @var string
     */
    protected $currency;

    /**
     * Number of seconds to delay the transfer's settlement (added to the actual time of settlement of the payment)
     * @var integer
     */
    protected $settlement_delay;

    /**
     * Gets the The SysPay user id of the recipient.
     * This is used as verification to make sure the recipient's account_id is not bogus.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Sets the The SysPay user id of the recipient.
     * This is used as verification to make sure the recipient's account_id is not bogus.
     *
     * @param integer $user_id the user_id
     *
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Gets the The SysPay account id of the recipient.
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Sets the The SysPay account id of the recipient.
     *
     * @param integer $account_id the account_id
     *
     * @return self
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;

        return $this;
    }

    /**
     * Gets the The way the amount to transfer to the recipient is calculated. (one of the CALC_TYPE constants)
     * - CALC_TYPE_PERCENT: The `value` property is a (percentage*100) of the total payment amount
     * - CALC_TYPE_FIXED: The 'value' property is a fixed amount. In this case the 'currency' must be given.
     *
     * @return integer
     */
    public function getCalcType()
    {
        return $this->calc_type;
    }

    /**
     * Sets the The way the amount to transfer to the recipient is calculated. (one of the CALC_TYPE constants)
     * - CALC_TYPE_PERCENT: The `value` property is a (percentage*100) of the total payment amount
     * - CALC_TYPE_FIXED: The 'value' property is a fixed amount. In this case the 'currency' must be given.
     *
     * @param integer $calc_type the calc_type
     *
     * @return self
     */
    public function setCalcType($calc_type)
    {
        $this->calc_type = $calc_type;

        return $this;
    }

    /**
     * Gets the Value used to compute the amount.
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the Value used to compute the amount.
     *
     * @param integer $value the value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the The currency (mandatory in case of a FIXED calc_type)
     * This is a safety check and it must match the payment currency..
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the The currency (mandatory in case of a FIXED calc_type)
     * This is a safety check and it must match the payment currency..
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
     * Gets the Number of seconds to delay the transfer's settlement (added to the actual time of settlement of the payment).
     *
     * @return integer
     */
    public function getSettlementDelay()
    {
        return $this->settlement_delay;
    }

    /**
     * Sets the Number of seconds to delay the transfer's settlement (added to the actual time of settlement of the payment).
     *
     * @param integer $settlement_delay the settlement_delay
     *
     * @return self
     */
    public function setSettlementDelay($settlement_delay)
    {
        $this->settlement_delay = $settlement_delay;

        return $this;
    }
}

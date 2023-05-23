<?php

declare(strict_types=1);

namespace Calculation\CommissionTask\Service;

use Calculation\CommissionTask\Service\Conversion;

class User
{
    const BASE_CURRENCY = 'EUR';
    const BUSINESS_TYPE = 'business';
    const PERCENTAGE = 0.01;
    const DEPOSIT_FEE = 0.03 * self::PERCENTAGE;
    const COMMISSION_FEE_PRIVATE_CLIENT = 0.3 * self::PERCENTAGE;
    const COMMISSION_FEE_BUSINESS_CLIENT = 0.5 * self::PERCENTAGE;
    const FREE_WITHDRAW_OPERATION_PER_WEEK = 3;
    const FREE_WITHDRAW_AMOUNT_PER_WEEK = 1000;

    public $id;
    public $type;
    public $commissionedAmount;
    public $withdrawnWeeks;
    public $decimalPlaces;

    public function __construct(int $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
        $this->commissionedAmount = 0;
        $this->withdrawnWeeks = [];
    }

    public function withdraw(string $amount, string $currency, string $date)
    {
        $this->decimalPlaces = (int) strpos(strrev($amount), ".");

        $amount = floatval($amount);

        if ($this->type === self::BUSINESS_TYPE) {
            return $this->roundValue($amount * self::COMMISSION_FEE_BUSINESS_CLIENT);
        }

        if ($currency !== self::BASE_CURRENCY) {
            // Base currency converted to specified currency
            $amount = (float) $this->roundValue($amount / Conversion::getRates($currency));
        }

        if ($this->hasFreeCommission($date, $amount)) {
            return $this->roundValue(0);
        }

        $finalAmount = $this->commissionedAmount * self::COMMISSION_FEE_PRIVATE_CLIENT;

        if ($currency === self::BASE_CURRENCY) {
            return $this->roundValue($finalAmount);
        }

        // Base currency reverted back to match with input currency
        return $this->roundValue($finalAmount * Conversion::getRates($currency));
    }

    public function deposit($amount)
    {
        $this->decimalPlaces = (int) strpos(strrev($amount), ".");

        return $this->roundValue(floatval($amount) * self::DEPOSIT_FEE);
    }

    public function hasFreeCommission(string $date, float $amount): bool
    {
        // Getting 1st week day based on date
        $weekStartDate = $this->getFirstDateOfWeek($date);

        /**
         * To check whether the user has withdrawn 
         * Already in the current week or not
         * 
         * Complexity: O(1)
         */
        if (isset($this->withdrawnWeeks[$weekStartDate])) {
            if (! $this->withdrawnWeeks[$weekStartDate]['freeCommission']) {
                $this->commissionedAmount = $amount;

                return false;
            }

            // Same week withdraw operation count
            $this->withdrawnWeeks[$weekStartDate]['countWithdrawOperation']++;

            // Same week total withdrawal amount
            $this->withdrawnWeeks[$weekStartDate]['totalAmount'] += $amount;

            // Assuming we've a freeCommission initially; which will be updated in the next statements
            $this->withdrawnWeeks[$weekStartDate]['freeCommission'] = true;

            $freeAmountExceeded = $this->withdrawnWeeks[$weekStartDate]['totalAmount'] - self::FREE_WITHDRAW_AMOUNT_PER_WEEK;

            if ($freeAmountExceeded > 0) {
                $this->commissionedAmount = $freeAmountExceeded;
                $this->withdrawnWeeks[$weekStartDate]['freeCommission'] = false;

                return false;
            }

            if ($this->withdrawnWeeks[$weekStartDate]['countWithdrawOperation'] > self::FREE_WITHDRAW_OPERATION_PER_WEEK) {
                $this->commissionedAmount = $amount;
                $this->withdrawnWeeks[$weekStartDate]['freeCommission'] = false;

                return false;
            }

            return true;

        } else {
            $remainingAmount = $amount - self::FREE_WITHDRAW_AMOUNT_PER_WEEK;
            $this->commissionedAmount = $remainingAmount > 0 ? $remainingAmount : 0;

            /**
             * Creating a hashmap based on 1st week day of given date and storing some meta data
             * To calculate same week total withdrawal amount, withdraw opearations
             */
            $this->withdrawnWeeks[$weekStartDate] = [
                'countWithdrawOperation' => 1,
                'totalAmount' => $amount,
                'freeCommission' => $this->commissionedAmount > 0 ? false : true
            ];

            return $this->withdrawnWeeks[$weekStartDate]['freeCommission'];
        }
    }

    public function roundValue(float $val)
    {
        return number_format($val, $this->decimalPlaces ?? 0, '.', '');
    }

    public function getFirstDateOfWeek($date)
    {
        return date("Y-m-d", strtotime('monday this week', strtotime($date)));
    }
}

<?php

namespace App;

use App\Aggregator;
use App\Converter;

/**
 * Instantiates transactions and provides methods
 * for transaction fee calculation and output
 */
class Transaction
{
    /**
     * @var mixed Properties of the transaction
     */
    protected $date;
    protected $user_id;
    protected $user_type;
    protected $operation;
    protected $amount;
    protected $currency;


    /**
     * @var array Fee rules configuration
     */
    protected static $fee_rules = [
        'natural' => [
            'cash_in' => [
                'standard_percent'     => 0.03,
                'max_free_amount_week' => null,
                'max_free_count_week'  => null,
                'min_fee'              => null,
                'max_fee'              => 5.00,
            ],
            'cash_out' => [
                'standard_percent'     => 0.3,
                'max_free_amount_week' => 1000.00,
                'max_free_count_week'  => 3,
                'min_fee'              => null,
                'max_fee'              => null,
            ],
        ],
        'legal' => [
            'cash_in' => [
                'standard_percent'     => 0.03,
                'max_free_amount_week' => null,
                'max_free_count_week'  => null,
                'min_fee'              => null,
                'max_fee'              => 5.00,
            ],
            'cash_out' => [
                'standard_percent'     => 0.3,
                'max_free_amount_week' => null,
                'max_free_count_week'  => null,
                'min_fee'              => 0.50,
                'max_fee'              => null,
            ],
        ],
    ];


    /**
     * Transaction constructor - initializes properties
     *
     * @param array $row Transaction properties as parsed from CVS file
     */
    public function __construct(array $row)
    {
        $this->date      = $row[0];
        $this->user_id   = $row[1];
        $this->user_type = $row[2];
        $this->operation = $row[3];
        $this->amount    = $row[4];
        $this->currency  = $row[5];
    }


    /**
     * This is a generator function.
     * Reads transactions from CVS file, registers them to aggregator
     * and yields transactions one by one for processing.
     *
     * @param string $file File name to read from
     *
     * @return Transaction iterable collection
     */
    public static function readByOneFromCSV($file)
    {
        $f = fopen($file, 'r');
        try {
            while ($line = fgets($f)) {
                $row = str_getcsv($line);
                $transaction = new self($row);
                Aggregator::register($transaction);
                yield $transaction;
            }
        } finally {
            fclose($f);
        }
    }


    /**
     * Calculates the fee of the transaction according to the rules
     *
     * @return float Fee in transaction currency
     */
    public function getFee()
    {
        $count_this_week  = Aggregator::getCount($this->user_id, $this->operation);
        $amount_this_week = Aggregator::getAmount($this->user_id, $this->operation);

        $max_free_count_week =
            static::$fee_rules[$this->user_type][$this->operation]['max_free_count_week'] ?? 0;
        $max_free_amount_week =
            static::$fee_rules[$this->user_type][$this->operation]['max_free_amount_week'] ?? 0;

        if ($count_this_week > $max_free_count_week) {
            $fee_rate = static::$fee_rules[$this->user_type][$this->operation]['standard_percent'];
            $fee_amount = Converter::convert($this->amount, $this->currency, 'EUR');
        } else {
            if ($amount_this_week < $max_free_amount_week) {
                $fee_rate = 0;
                $fee_amount = 0;
            } else {
                $fee_rate = static::$fee_rules[$this->user_type][$this->operation]['standard_percent'];
                $fee_amount = min(
                    $amount_this_week - $max_free_amount_week,
                    Converter::convert($this->amount, $this->currency, 'EUR')
                );
            }
        }

        # Preliminary fee in EUR
        $fee = $fee_amount * $fee_rate / 100;

        # Adjust for minimum fee, if applicable
        if (isset(static::$fee_rules[$this->user_type][$this->operation]['min_fee']))
        {
            $fee = max($fee, static::$fee_rules[$this->user_type][$this->operation]['min_fee']);
        }

        # Adjust for maximum fee, if applicable
        if (isset(static::$fee_rules[$this->user_type][$this->operation]['max_fee']))
        {
            $fee = min($fee, static::$fee_rules[$this->user_type][$this->operation]['max_fee']);
        }

        # Converto to transaction's currency
        $fee = Converter::convert($fee, 'EUR', $this->currency);

        # Hack for calculations and conversion rounding error
        # 3.00000000001 would round up (ceil) to 3.01 without that
        $fee -= 0.000001;

        # Round up to the lowest currency denomination
        $denomination = Converter::getDenomination($this->currency);
        $fee = ceil($fee / $denomination) * $denomination;

        return $fee;
    }


    /**
     * Prints the transaction fee to stdout
     * with the correct decimal points formating
     */
    public function printFee()
    {
        $decimals = log10(1 / Converter::getDenomination($this->currency));
        echo number_format($this->getFee(), $decimals, '.', '') . "\n";
    }


    /**
     * Getters for the properties
     */

    public function getDate()
    {
        return $this->date;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUserType()
    {
        return $this->user_type;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}

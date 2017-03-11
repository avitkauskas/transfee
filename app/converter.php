<?php

namespace App;

use Exception;

/**
 * Currency converter
 */
class Converter
{
    /**
     * @var array Preset exchange rates
     */
    protected static $rates = [
        'EUR' => 1,
        'USD' => 1.1497,
        'JPY' => 129.53,
    ];

    /**
     * @var array The lowest currency denominations
     */
    protected static $denominations = [
        'default' => 0.01,
        'JPY' => 1,
    ];

    /**
     * Converts amounts from one currenct=y to another
     *
     * @param float  $amount        Amount in convert from currency
     * @param string $from_currency Currency to convert from
     * @param string $to_currency   Currency to convert to
     *
     * @throws Exception if gets unsupported currency
     *
     * @return float Amount in convert to currency
     */
    public static function convert($amount, $from_currency, $to_currency)
    {
        if (! isset(static::$rates[$from_currency])) {
            throw new Exception("Unsupported currency '$from_currency'");
        }
        if (! isset(static::$rates[$to_currency])) {
            throw new Exception("Unsupported currency '$to_currency'");
        }

        return $amount / static::$rates[$from_currency] * static::$rates[$to_currency];
    }

    /**
     * Provides the lowest denominations of currencies
     *
     * @param string $currency Currency code
     *
     * @return float Lowest denomination for this currency
     */
    public static function getDenomination($currency)
    {
        if (isset(static::$denominations[$currency])) {
            return static::$denominations[$currency];
        }
        return static::$denominations['default'];
    }
}

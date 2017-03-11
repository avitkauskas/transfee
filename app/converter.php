<?php

namespace App;

class Converter
{
    protected static $rates = [
        'EUR' => 1,
        'USD' => 1.1497,
        'JPY' => 129.53,
    ];

    protected static $denominations = [
        'EUR' => 0.01,
        'USD' => 0.01,
        'JPY' => 1,
    ];

    public static function convert($amount, $from_currency, $to_currency)
    {
        if (! isset(static::$rates[$from_currency])) {
            die("Unsupported currency: " . $from_currency . "\n");
        }
        if (! isset(static::$rates[$to_currency])) {
            die("Unsupported currency: " . $to_currency . "\n");
        }

        return $amount / static::$rates[$from_currency] * static::$rates[$to_currency];
    }

    public static function getDenomination($currency)
    {
        return static::$denominations[$currency];
    }
}

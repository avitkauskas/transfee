<?php

namespace App;

use App\Transaction;
use App\Converter;

/**
 * Aggregates transaction counts and amounts in EUR
 * per user and operation for the current week
 */
class Aggregator
{
    protected static $current_week = 0;

    protected static $weekly_stats = [];

    public static function register(Transaction $transaction)
    {
        $transaction_week = date("W", strtotime($transaction->getDate()));
        if (static::$current_week != $transaction_week) {
            static::$current_week = $transaction_week;
            static::$weekly_stats = [];
        }

        $user_id = $transaction->getUserId();
        $operation = $transaction->getOperation();
        $currency = $transaction->getCurrency();
        $amount = Converter::convert($transaction->getAmount(), $currency, 'EUR');

        if (key_exists($user_id, static::$weekly_stats) &&
            key_exists($operation, static::$weekly_stats[$user_id])
        ) {
            static::$weekly_stats[$user_id][$operation]['count'] += 1;
            static::$weekly_stats[$user_id][$operation]['amount'] += $amount;
        } else {
            static::$weekly_stats[$user_id][$operation]['count'] = 1;
            static::$weekly_stats[$user_id][$operation]['amount'] = $amount;
        }
    }

    public static function getCount($user_id, $operation)
    {
        if (isset(static::$weekly_stats[$user_id][$operation])) {
            return static::$weekly_stats[$user_id][$operation]['count'];
        } else {
            return 0;
        }
    }

    public static function getAmount($user_id, $operation)
    {
        if (isset(static::$weekly_stats[$user_id][$operation])) {
            return static::$weekly_stats[$user_id][$operation]['amount'];
        } else {
            return 0;
        }
    }
}

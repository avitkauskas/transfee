<?php

namespace App;

use App\Transaction;
use App\Converter;

/**
 * Aggregates transaction counts and amounts in EUR
 * per user and operation type for the current week
 */
class Aggregator
{
    /**
     * @var int The number of the week that is aggregated
     */
    protected static $current_week = 0;


    /**
     * @var array Keeps aggregations of counts and amounts
     *     per user and operation type for the current week
     */
    protected static $weekly_stats = [];


    /**
     * Registers the transaction and aggregates operation types and amounts
     * converting all currencies to EUR
     *
     * @param Transaction $transaction Transaction to aggregate
     *
     * @return void
     */
    public static function register(Transaction $transaction)
    {
        $transaction_week = date("Y-W", strtotime($transaction->getDate()));
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


    /**
     * Provides the number of operations in the current week
     *
     * @param int    $user_id   ID of the user
     * @param string $operation Type of the operation
     *
     * @return int   Number of operations this week
     */
    public static function getCount($user_id, $operation)
    {
        if (isset(static::$weekly_stats[$user_id][$operation])) {
            return static::$weekly_stats[$user_id][$operation]['count'];
        } else {
            return 0;
        }
    }


    /**
     * Provides the total amount of operations
     * in the current week converted to EUR
     *
     * @param int    $user_id   ID of the user
     * @param string $operation Type of the operation
     *
     * @return float Amount of operations this week in EUR
     */
    public static function getAmount($user_id, $operation)
    {
        if (isset(static::$weekly_stats[$user_id][$operation])) {
            return static::$weekly_stats[$user_id][$operation]['amount'];
        } else {
            return 0;
        }
    }
}

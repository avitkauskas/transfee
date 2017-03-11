<?php

namespace App;

class Transaction
{

    protected static $items = array();

    protected $date;
    protected $user_id;
    protected $user_type;
    protected $type;
    protected $amount;
    protected $currency;

    public function __construct(array $row)
    {
        $this->date      = $row[0];
        $this->user_id   = $row[1];
        $this->user_type = $row[2];
        $this->type      = $row[3];
        $this->amount    = $row[4];
        $this->currency  = $row[5];
    }

    public static function init($path)
    {
        self::$items = array_map('str_getcsv', file($path));
    }

    public static function all()
    {
        return array_map(function($row) {
            return new self($row);
        }, self::$items);
    }

    public function countThisWeek()
    {
        $count = 0;
        array_map(function($row) use (&$count) {
            if ($this->user_id == $row[1] &&
                date("W", strtotime($this->date)) == date("W", strtotime($row[0]))
            ) $count++;
        }, self::$items);
        return $count;
    }

    public function sumThisWeek()
    {

    }

    public function getFee()
    {
        return $this->countThisWeek();
    }

    public function printFee()
    {
        echo $this->getFee() . "\n";
    }
}

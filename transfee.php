<?php

require __DIR__ . '/vendor/autoload.php';

use App\Transaction;

if ($argc < 2)
{
    die("Usage: php " . $argv[0] . " file.csv\n");
}

Transaction::init($argv[1]);

foreach (Transaction::all() as $transaction) {
    $transaction->printFee();
}

<?php

require __DIR__ . '/vendor/autoload.php';

use App\Transaction;

if ($argc < 2) {
    die("Usage: php " . $argv[0] . " file.csv\n");
}

if (! file_exists($csv = $argv[1])) {
    die("File not found: " . $csv . "\n");
}

foreach (Transaction::readByOneFromCSV($csv) as $transaction) {
    $transaction->printFee();
}

<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Transaction;
use App\Aggregator;

/**
 * Tests for Transaction class
 */
class TransactionTest extends TestCase
{

    # Use different week dates for every test function
    # otherwise Aggregator would not reset counts and amounts

    public function testLegalCashInFeeCalculation()
    {
        $transaction = new Transaction(['2017-02-07',1,'legal','cash_in',1000.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(1000 * 0.03 / 100, $transaction->getFee());

        # check for maximum 5 EUR
        $transaction = new Transaction(['2017-02-07',1,'legal','cash_in',100000.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(5, $transaction->getFee());
    }

    public function testNaturalCashInFeeCalculation()
    {
        $transaction = new Transaction(['2017-02-14',1,'natural','cash_in',1000.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(1000 * 0.03 / 100, $transaction->getFee());

        # check for maximum 5 EUR
        $transaction = new Transaction(['2017-02-07',1,'natural','cash_in',100000.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(5, $transaction->getFee());
    }

    public function testLegalCashOutFeeCalculation()
    {
        $transaction = new Transaction(['2017-02-21',1,'legal','cash_out',1000.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(1000 * 0.3 / 100, $transaction->getFee());

        # check for minimum 0.50 EUR
        $transaction = new Transaction(['2017-02-21',1,'legal','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(0.50, $transaction->getFee());
    }

    public function testNaturalCashOutFeeCalculation()
    {
        $transaction = new Transaction(['2017-02-27',1,'natural','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        # free until 1000 EUR per week
        $this->assertEquals(0, $transaction->getFee());

        $transaction = new Transaction(['2017-02-27',1,'natural','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        # still free until 1000 EUR per week
        $this->assertEquals(0, $transaction->getFee());

        $transaction = new Transaction(['2017-02-27',1,'natural','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        # still free until 1000 EUR per week - 3rd operation
        $this->assertEquals(0, $transaction->getFee());

        $transaction = new Transaction(['2017-02-27',1,'natural','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        # not free - 4th operation, even if limit of 1000 EUR not exceeded
        $this->assertEquals(100 * 0.3 / 100, $transaction->getFee());

        # next week
        $transaction = new Transaction(['2017-03-06',1,'natural','cash_out',100.00,'EUR']);
        Aggregator::register($transaction);
        # free - limit of 1000 EUR not exceeded
        $this->assertEquals(0, $transaction->getFee());

        $transaction = new Transaction(['2017-03-06',1,'natural','cash_out',1000.00,'EUR']);
        Aggregator::register($transaction);
        # not free - limit of 1000 EUR exceeded - 100 EUR excess is chargable
        $this->assertEquals(100 * 0.3 / 100, $transaction->getFee());

        $transaction = new Transaction(['2017-03-06',1,'natural','cash_out',1000.00,'EUR']);
        Aggregator::register($transaction);
        # not free - limit of 1000 EUR exceeded - full 1000 EUR is now chargable
        $this->assertEquals(1000 * 0.3 / 100, $transaction->getFee());
    }

    public function testForSameWeekInDifferentYears()
    {
        $transaction = new Transaction(['2010-01-03',1,'natural','cash_out',900.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(0, $transaction->getFee());
        $transaction = new Transaction(['2011-01-03',1,'natural','cash_out',900.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(0, $transaction->getFee());
        $transaction = new Transaction(['2012-01-03',1,'natural','cash_out',900.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(0, $transaction->getFee());
        $transaction = new Transaction(['2013-01-03',1,'natural','cash_out',900.00,'EUR']);
        Aggregator::register($transaction);
        $this->assertEquals(0, $transaction->getFee());
    }

    public function testFeeCalculationAndRounding()
    {
        # test calculation and rounding for other currencies, ex. JPY
        $transaction = new Transaction(['2017-03-06',2,'natural','cash_out',1000000,'JPY']);
        Aggregator::register($transaction);

        $this->assertEquals(2612, $transaction->getFee());
    }

    public function testOutputFormat()
    {
        # test that correct decimals are printed
        $this->expectOutputString("3000\n3.00\n3.00\n");

        $transaction = new Transaction(['2017-03-06',3,'legal','cash_out',1000000,'JPY']);
        Aggregator::register($transaction);
        $transaction->printFee();

        $transaction = new Transaction(['2017-03-06',3,'legal','cash_out',1000,'EUR']);
        Aggregator::register($transaction);
        $transaction->printFee();

        $transaction = new Transaction(['2017-03-06',3,'legal','cash_out',1000,'USD']);
        Aggregator::register($transaction);
        $transaction->printFee();
    }
}

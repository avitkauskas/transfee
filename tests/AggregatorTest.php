<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Aggregator;
use App\Transaction;
use App\Converter;

/**
 * Tests for Aggregator class
 */
class AggregatorTest extends TestCase
{
    public function testRegisterAndGet()
    {
        $transactions = [
            1 => new Transaction(['2017-03-06',1,'natural','cash_out',100.00,'EUR']),
            2 => new Transaction(['2017-03-07',1,'natural','cash_out',200.00,'EUR']),
            3 => new Transaction(['2017-03-07',2,'natural','cash_out',300.00,'EUR']),
            4 => new Transaction(['2017-03-07',2,'natural','cash_in', 300.00,'EUR']),
            5 => new Transaction(['2017-03-08',3,  'legal','cash_out',400.00,'EUR']),
            6 => new Transaction(['2017-03-08',3,  'legal','cash_out',500.00,'EUR']),
            7 => new Transaction(['2017-03-13',1,'natural','cash_out',600.00,'EUR']),
            8 => new Transaction(['2017-03-13',1,'natural','cash_out',700.00,'EUR']),
            9 => new Transaction(['2017-03-14',1,'natural','cash_out',100.00,'USD']),
           10 => new Transaction(['2017-03-15',2,'natural','cash_out',100000,'JPY']),
        ];

        Aggregator::register($transactions[1]);
        $this->assertEquals(1, Aggregator::getCount(1, 'cash_out'));
        $this->assertEquals(100, Aggregator::getAmount(1, 'cash_out'));

        Aggregator::register($transactions[2]);
        $this->assertEquals(2, Aggregator::getCount(1, 'cash_out'));
        $this->assertEquals(300, Aggregator::getAmount(1, 'cash_out'));

        Aggregator::register($transactions[3]);
        $this->assertEquals(2, Aggregator::getCount(1, 'cash_out'));
        $this->assertEquals(300, Aggregator::getAmount(1, 'cash_out'));
        $this->assertEquals(1, Aggregator::getCount(2, 'cash_out'));
        $this->assertEquals(300, Aggregator::getAmount(2, 'cash_out'));

        Aggregator::register($transactions[4]);
        $this->assertEquals(1, Aggregator::getCount(2, 'cash_out'));
        $this->assertEquals(1, Aggregator::getCount(2, 'cash_in'));

        Aggregator::register($transactions[5]);
        Aggregator::register($transactions[6]);
        $this->assertEquals(1, Aggregator::getCount(2, 'cash_out'));
        $this->assertEquals(2, Aggregator::getCount(3, 'cash_out'));

        # Next week - should reset aggregations
        Aggregator::register($transactions[7]);
        $this->assertEquals(1, Aggregator::getCount(1, 'cash_out'));
        $this->assertEquals(600, Aggregator::getAmount(1, 'cash_out'));
        $this->assertEquals(0, Aggregator::getCount(2, 'cash_out'));
        $this->assertEquals(0, Aggregator::getCount(3, 'cash_out'));

        Aggregator::register($transactions[8]);
        $this->assertEquals(2, Aggregator::getCount(1, 'cash_out'));
        $this->assertEquals(1300, Aggregator::getAmount(1, 'cash_out'));

        # Should convert other currencies to EUR
        Aggregator::register($transactions[9]);
        $expected = 1300 + Converter::convert(100, 'USD', 'EUR');
        $this->assertEquals($expected, Aggregator::getAmount(1, 'cash_out'));

        Aggregator::register($transactions[10]);
        $expected = Converter::convert(100000, 'JPY', 'EUR');
        $this->assertEquals($expected, Aggregator::getAmount(2, 'cash_out'));
    }
}

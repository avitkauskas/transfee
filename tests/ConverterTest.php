<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Converter;
use Exception;

/**
 * Tests for Converter class
 */
class ConverterTest extends TestCase
{
    public function testConvert()
    {
        $rates = [
            'EUR' => 1,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ];

        # EUR to EUR conversion
        $this->assertEquals(100, Converter::convert(100, 'EUR', 'EUR'));

        # EUR to USD conversion
        $expected = 100 * $rates['USD'];
        $this->assertEquals($expected, Converter::convert(100, 'EUR', 'USD'));

        # USD to EUR conversion
        $expected = 100 / $rates['USD'];
        $this->assertEquals($expected, Converter::convert(100, 'USD', 'EUR'));

        # USD to JPY conversion
        $expected = 100 / $rates['USD'] * $rates['JPY'];
        $this->assertEquals($expected, Converter::convert(100, 'USD', 'JPY'));

        # Negative EUR to USD conversion
        $expected = -100 * $rates['USD'];
        $this->assertEquals($expected, Converter::convert(-100, 'EUR', 'USD'));

        # Unsupported currency
        $this->expectException(Exception::class);
        Converter::convert(100, 'EUR', 'AUD');
    }

    public function testGetDenomination()
    {
        $this->assertEquals(1, Converter::getDenomination('JPY'));
        $this->assertEquals(0.01, Converter::getDenomination('EUR'));
        $this->assertEquals(0.01, Converter::getDenomination('AUD'));
    }
}

<?php

declare(strict_types=1);

namespace Calculation\CommissionTask\Service;

class Conversion
{
    const API = "https://developers.paysera.com/tasks/api/currency-exchange-rates";

    protected static $currencyResource;

    public static function getRates(string $currency): float
    {
        if (self::$currencyResource) {
            return self::$currencyResource[$currency];
        }

        $currencyRatesEUR = json_decode(file_get_contents(self::API));

        // $currencyRatesEUR->rates->JPY = 129.53;
        // $currencyRatesEUR->rates->USD = 1.1497;

        self::$currencyResource = (array) $currencyRatesEUR->rates;

        return self::$currencyResource[$currency];
    }
}

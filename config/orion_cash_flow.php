<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cash & Cash Equivalents
    |--------------------------------------------------------------------------
    | Orion currently posts treasury/cash movements to 1101/1102/1103 and
    | bank movements to the GL control account 1110.
    */
    'cash_accounts' => ['1101', '1102', '1103', '1110'],

    // Old bank GL accounts kept only for data-quality warnings. They are NOT
    // included in the new Cash Flow calculation.
    'legacy_cash_accounts' => ['1111', '1112', '1113'],

    'tolerance' => 0.01,

    /*
    |--------------------------------------------------------------------------
    | Exact account classification
    |--------------------------------------------------------------------------
    | Values: operating, investing, financing, none
    */
    'account_categories' => [
        // Current assets / working capital
        '1200' => 'operating',
        '1201' => 'operating',
        '1202' => 'operating',
        '1203' => 'operating',
        '1300' => 'operating',
        '1301' => 'operating',
        '1302' => 'operating',
        '1303' => 'operating',
        '1400' => 'operating',
        '1401' => 'operating',
        '1402' => 'operating',

        // Fixed assets
        '1500' => 'investing',
        '1501' => 'investing',
        '1502' => 'investing',
        '1503' => 'investing',
        '1504' => 'investing',
        '1505' => 'investing',
        '1510' => 'none',
        '1511' => 'none',
        '1512' => 'none',
        '1513' => 'none',

        // Current liabilities
        '2100' => 'operating',
        '2101' => 'operating',
        '2102' => 'operating',
        '2103' => 'operating',
        '2104' => 'operating',
        '2105' => 'operating',
        '2106' => 'operating',
        '2107' => 'operating',

        // Financing liabilities
        '2200' => 'financing',
        '2201' => 'financing',
        '2202' => 'financing',

        // Equity
        '3000' => 'none',
        '3001' => 'financing',
        '3002' => 'none',
        '3003' => 'none',
        '3004' => 'none',

        // Revenue
        '4000' => 'operating',
        '4100' => 'operating',
        '4101' => 'operating',
        '4102' => 'operating',
        '4103' => 'operating',
        '4104' => 'operating',
        '4200' => 'operating',
        // Policy choice: bank interest received is Operating by default.
        '4201' => 'operating',
        '4202' => 'operating',
        '4203' => 'operating',

        // Costs / expenses
        '5000' => 'operating',
        '5100' => 'operating',
        '5101' => 'operating',
        '5102' => 'operating',
        '5103' => 'operating',
        '5200' => 'operating',
        '5201' => 'operating',
        '5202' => 'operating',
        '5203' => 'operating',
        '5204' => 'operating',
        '5205' => 'operating',
        '5206' => 'operating',
        '5207' => 'operating',
        '5208' => 'operating',
        '5209' => 'operating',
        '5210' => 'operating',
        '5300' => 'operating',
        // Policy choice: interest paid is Operating by default.
        '5301' => 'operating',
        '5302' => 'operating',
        '5303' => 'operating',

        // Non-cash depreciation
        '5400' => 'none',
        '5401' => 'none',
        '5402' => 'none',
        '5403' => 'none',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefix fallback
    |--------------------------------------------------------------------------
    | New accounts added later can still receive a sensible default. Exact
    | classifications above always take precedence.
    */
    'prefix_fallbacks' => [
        '12' => 'operating',
        '13' => 'operating',
        '14' => 'operating',
        '15' => 'investing',
        '21' => 'operating',
        '22' => 'financing',
        '4'  => 'operating',
        '5'  => 'operating',
    ],
];

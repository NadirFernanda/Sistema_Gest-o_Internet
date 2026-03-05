<?php

return [
    // Valor base por código em Kz (ajuste conforme a política comercial real)
    'code_unit_price_aoa' => env('RESELLER_CODE_UNIT_PRICE', 1000),

    // Escalões de desconto por valor bruto (em Kz)
    // chave = valor mínimo em Kz, valor = percentagem de desconto
    'discount_tiers' => [
        10000 => 10,
        20000 => 15,
        30000 => 20,
        40000 => 25,
        100000 => 40,
    ],
];

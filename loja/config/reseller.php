<?php

return [
    // Valor base por código em Kz (plano diário = 200 Kz)
    'code_unit_price_aoa' => env('RESELLER_CODE_UNIT_PRICE', 200),

    // Compra mínima obrigatória em Kz (regra de negócio)
    'min_purchase_aoa' => 10000,

    // ─── Modo 1: Revendedor com Internet Própria ──────────────────────────────
    // Desconto fixo de 70% — o revendedor paga 30% do valor de face.
    'mode_own_discount_percent' => 70,

    // Taxa de manutenção anual (Kz) — cobrada em Março
    'mode_own_maintenance_aoa' => 50000,
    'mode_own_maintenance_month' => 3,  // Março

    // Bónus de início: % da taxa de instalação convertida em vouchers
    'bonus_install_percent' => 50,

    // Meta mensal: % da taxa de instalação
    'monthly_target_percent' => 50,

    // ─── Modo 2: Revendedor com Internet AngolaWiFi ───────────────────────────
    // Escalões de desconto por volume de compra mensal (chave = mínimo em Kz)
    'mode_angolawifi_discount_tiers' => [
        10000  => 10,
        50000  => 20,
        100000 => 30,
        200000 => 40,
    ],

    // Taxa de manutenção anual (Kz) — cobrada em Outubro
    'mode_angolawifi_maintenance_aoa' => 100000,
    'mode_angolawifi_maintenance_month' => 10, // Outubro
];

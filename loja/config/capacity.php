<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carga máxima do servidor (load average de 1 minuto)
    |--------------------------------------------------------------------------
    | Quando o load average de 1 minuto ultrapassar este valor, o sistema
    | rejeita novos pedidos com 503 em vez de ficar lento ou bugar.
    | Regra de ouro: núm. de CPUs × 2  (VPS com 2 CPUs → 4.0)
    */
    'max_load_average' => env('CAPACITY_MAX_LOAD', 4.0),

    /*
    |--------------------------------------------------------------------------
    | Janela deslizante de pedidos globais (todos os IPs juntos)
    |--------------------------------------------------------------------------
    | Máximo de pedidos HTTP aceites em qualquer janela de 10 segundos.
    | 200 req/10s ≈ 20 req/s — adequado para VPS de 2-4 núcleos.
    | Ajuste para cima se o servidor for mais potente.
    */
    'max_requests_per_window' => env('CAPACITY_MAX_REQUESTS_WINDOW', 200),

    /*
    |--------------------------------------------------------------------------
    | Rate limit por IP — rotas normais
    |--------------------------------------------------------------------------
    | Máximo de pedidos que um único IP pode fazer por minuto.
    | Protege contra clientes que fazem refresh em loop ou scrapers.
    */
    'rate_limit_per_minute' => env('CAPACITY_RATE_LIMIT', 120),

    /*
    |--------------------------------------------------------------------------
    | Rate limit por IP — rotas sensíveis (login, OTP, pagamentos)
    |--------------------------------------------------------------------------
    */
    'rate_limit_sensitive' => env('CAPACITY_RATE_LIMIT_SENSITIVE', 20),

];

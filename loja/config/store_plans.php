<?php

return [
    // Planos para autovenda (cliente final, 1 dispositivo por voucher)
    'individual' => [
        // Plano Hora removido conforme atualização, agora apenas Dia, Semana e Mês
        [
            'id' => 'diario',
            'name' => 'Plano Diário',
            'duration_label' => '24 horas',
            'duration_minutes' => 24 * 60,
            'speed' => '', // removido Ilimitado
            'max_speed' => 'até 10MBPS',
            'download' => 'Download Ilimitado',
            'price_kwanza' => 200,
            'description' => 'Internet para o dia todo, ideal para quem precisa de conectividade contínua sem interrupçõs.',
            'image' => '/img/foto.jpg',
        ],
        [
            'id' => 'semanal',
            'name' => 'Plano Semanal',
            'duration_label' => '7 dias',
            'duration_minutes' => 7 * 24 * 60,
            'speed' => 'Ilimitado',
            'max_speed' => 'até 10MBPS',
            'download' => 'Download Ilimitado',
            'price_kwanza' => 500,
            'description' => 'Ideal para estudantes e profissionais que precisam de internet contínua e confiável durante a semana.',
            'image' => '/img/foto.jpg',
        ],
        [
            'id' => 'mensal',
            'name' => 'Plano Mensal',
            'duration_label' => '30 dias',
            'duration_minutes' => 30 * 24 * 60,
            'speed' => 'Ilimitado',
            'max_speed' => 'até 10MBPS',
            'download' => 'Download Ilimitado',
            'price_kwanza' => 1000,
            'description' => 'Internet ideal para streaming, Netflix, TV online e navegação contínua, sem interrupções.',
            'image' => '/img/foto.jpg',
        ],
    ],
];

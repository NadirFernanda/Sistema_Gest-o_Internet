<?php

return [
    // Planos para autovenda (cliente final, 1 dispositivo por voucher)
    'individual' => [
        [
            'id' => 'hora',
            'name' => 'Plano Hora',
            'duration_label' => '60 minutos',
            'duration_minutes' => 60,
            'speed' => 'Ilimitado*',
            'price_kwanza' => 200,
            'description' => 'Acesso rápido por 1 hora, ideal para tarefas pontuais, pagamentos e navegações curtas.',
            'image' => '/img/foto.jpg',
        ],
        [
            'id' => 'diario',
            'name' => 'Plano Diário',
            'duration_label' => '24 horas',
            'duration_minutes' => 24 * 60,
            'speed' => 'Ilimitado*',
            'price_kwanza' => 200,
            'description' => 'Internet para o dia inteiro, perfeito para quem precisa de conectividade contínua durante 24h.',
            'image' => '/img/foto.jpg',
        ],
        [
            'id' => 'semanal',
            'name' => 'Plano Semanal',
            'duration_label' => '7 dias',
            'duration_minutes' => 7 * 24 * 60,
            'speed' => 'Ilimitado*',
            'price_kwanza' => 500,
            'description' => 'Plano de 7 dias para utilização recorrente, ideal para estudantes e profissionais.',
            'image' => '/img/foto.jpg',
        ],
        [
            'id' => 'mensal',
            'name' => 'Plano Mensal',
            'duration_label' => '30 dias',
            'duration_minutes' => 30 * 24 * 60,
            'speed' => 'Ilimitado*',
            'price_kwanza' => 1000,
            'description' => 'Plano de 30 dias com acesso estável e previsível, para uso contínuo em casa ou no escritório.',
            'image' => '/img/foto.jpg',
        ],
    ],
];

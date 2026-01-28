<?php
return [
    'required' => 'O campo :attribute é obrigatório.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'min' => [
        'numeric' => 'O valor de :attribute deve ser no mínimo :min.',
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
    ],
    'max' => [
        'numeric' => 'O valor de :attribute não pode ser maior que :max.',
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'exists' => 'O :attribute selecionado é inválido.',
    'string' => 'O campo :attribute deve ser um texto.',
    // Adicione outras mensagens conforme necessário
    'attributes' => [
        'estoque_equipamento_id' => 'equipamento',
        'quantidade' => 'quantidade',
        'morada' => 'morada',
        'ponto_referencia' => 'ponto de referência',
        'nome' => 'nome',
        'descricao' => 'descrição',
        'modelo' => 'modelo',
        'numero_serie' => 'número de série',
    ],
];

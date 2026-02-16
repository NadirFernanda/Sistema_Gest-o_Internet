<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CompensacoesExport implements FromView
{
    protected $compensacoes;
    protected $users;
    protected $planoMap;

    public function __construct($compensacoes, $users, $planoMap)
    {
        $this->compensacoes = $compensacoes;
        $this->users = $users;
        $this->planoMap = $planoMap;
    }

    public function view(): View
    {
        return view('clientes.compensacoes_export', [
            'compensacoes' => $this->compensacoes,
            'users' => $this->users,
            'planoMap' => $this->planoMap,
        ]);
    }
}

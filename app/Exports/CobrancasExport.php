<?php

namespace App\Exports;

use App\Models\Cobranca;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CobrancasExport implements FromView
{
    protected $cobrancas;

    public function __construct($cobrancas)
    {
        $this->cobrancas = $cobrancas;
    }

    public function view(): View
    {
        return view('cobrancas.export', [
            'cobrancas' => $this->cobrancas
        ]);
    }
}

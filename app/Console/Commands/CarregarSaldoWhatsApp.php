<?php

namespace App\Console\Commands;

use App\Models\WhatsappLedger;
use Illuminate\Console\Command;

class CarregarSaldoWhatsApp extends Command
{
    protected $signature = 'saldo-whatsapp:carregar
        {valor : Valor a carregar, em Kz}
        {--descricao= : Nota sobre o pagamento, ex.: "Transferência BAI 12/08"}';

    protected $description = 'Regista um carregamento de saldo de mensagens WhatsApp directamente no servidor, sem precisar do painel web.';

    public function handle(): int
    {
        $valor = (float) $this->argument('valor');

        if ($valor <= 0) {
            $this->error('O valor tem de ser maior que zero.');
            return self::FAILURE;
        }

        $descricao = $this->option('descricao') ?: 'Carregamento manual via servidor';

        $antes = WhatsappLedger::saldoAtual();

        WhatsappLedger::carregar($valor, $descricao);

        $depois = WhatsappLedger::saldoAtual();

        $this->info("Saldo carregado: +" . number_format($valor, 2, ',', '.') . " Kz");
        $this->line("Descrição: {$descricao}");
        $this->line('Saldo anterior: ' . number_format($antes, 2, ',', '.') . ' Kz');
        $this->line('Saldo actual:   ' . number_format($depois, 2, ',', '.') . ' Kz');

        return self::SUCCESS;
    }
}

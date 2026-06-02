<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;

class TestWhatsAppSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {numero? : Número destino em formato internacional} {--mensagem= : Mensagem a enviar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia uma mensagem de teste via WhatsApp usando o WhatsAppService';

    public function handle()
    {
        $numero   = $this->argument('numero') ?: env('WHATSAPP_TEST_TO');
        $mensagem = $this->option('mensagem') ?: 'Mensagem de teste — Angola WiFi SGA.';

        if (! $numero) {
            $this->error('Número destino não informado. Passe como argumento ou defina WHATSAPP_TEST_TO no .env');
            return 1;
        }

        try {
            $service = new WhatsAppService();
            $this->info("A enviar para: {$numero}");
            $result = $service->enviarMensagem($numero, $mensagem);
            $this->info('Enviado com sucesso: ' . json_encode($result));
            return 0;
        } catch (\Throwable $e) {
            $this->error('Falha: ' . $e->getMessage());
            return 2;
        }
    }
}

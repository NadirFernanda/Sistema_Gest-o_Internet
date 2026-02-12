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
        $numero = $this->argument('numero') ?: env('TWILIO_TEST_TO');
        $mensagem = $this->option('mensagem') ?: env('TWILIO_TEST_MESSAGE', 'Mensagem de teste: alerta de vencimento de plano.');

        if (!$numero) {
            $this->error('Número destino não informado. Passe como argumento ou defina TWILIO_TEST_TO no .env');
            return 1;
        }

        $service = new WhatsAppService();
        $this->info("Enviando para: {$numero}");
        $result = $service->enviarMensagem($numero, $mensagem);

        if ($result) {
            $this->info('Mensagem enviada com sucesso. Resposta: ' . (is_array($result) ? json_encode($result) : (string)$result));
            return 0;
        }

        $this->error('Falha ao enviar mensagem. Verifique logs e configuração.');
        return 2;
    }
}

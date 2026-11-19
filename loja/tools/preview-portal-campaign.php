<?php

// Gera uma pré-visualização estática sem criar ou alterar campanhas na base de dados.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$campaign = new App\Models\AdCampaign([
    'campaign_type' => 'own',
    'advertiser_name' => 'AngolaWiFi',
    'title' => 'Internet para todos',
    'description' => 'Ligue-se à rede AngolaWiFi e descubra os planos disponíveis.',
    'image_path' => 'campaign-demos/internet-para-todos.webp',
    'button_text' => 'Saiba mais',
]);
$campaign->id = 1;

$plan = new App\Models\VoucherPlan([
    'slug' => 'diario',
    'name' => 'Plano Diário',
    'validity_label' => '24 horas',
    'speed_label' => 'Até 10 Mbps',
    'price_public_aoa' => 350,
]);

$html = view('store.portal-wifi-ad', [
    'campaign' => $campaign,
    'impressionUrl' => '#',
    'clickUrl' => '#',
    'dailyPlan' => $plan,
])->render();

$creative = base64_encode(file_get_contents(public_path('img/campaign-demos/internet-para-todos.webp')));
$logo = base64_encode(file_get_contents(public_path('img/logo2.jpeg')));
$html = preg_replace('~src="[^"]*/ads/sol/1/image\?[^\"]*"~', 'src="data:image/webp;base64,'.$creative.'"', $html);
$html = str_replace(asset('img/logo2.jpeg'), 'data:image/jpeg;base64,'.$logo, $html);

$output = dirname(__DIR__).'/screenshots/portal-campaign-preview.html';
file_put_contents($output, $html);
echo $output.PHP_EOL;

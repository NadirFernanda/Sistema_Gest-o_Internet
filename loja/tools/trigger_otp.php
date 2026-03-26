<?php
// Helper script to trigger OTP for reseller panel login (dev only)
$ch = curl_init();
$jar = tempnam(sys_get_temp_dir(), 'cookies_');

curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8001/painel-revendedor');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);

$html = curl_exec($ch);
preg_match('/name="_token" value="([^"]+)"/', $html, $m);
$csrf = $m[1] ?? '';
echo "CSRF: $csrf\n";

$data = http_build_query(['_token' => $csrf, 'email' => 'agente@teste.ao']);
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8001/painel-revendedor/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$r = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Login POST status: $status\n";
curl_close($ch);

echo "Cookie file: $jar\n";

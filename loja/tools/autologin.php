<?php
// Complete login flow: get CSRF, trigger OTP, verify OTP, return logged-in session cookie
$jar = tempnam(sys_get_temp_dir(), 'reseller_session_');

// Step 1: GET login page
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://127.0.0.1:8001/painel-revendedor',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $jar,
    CURLOPT_COOKIEJAR => $jar,
]);
$html = curl_exec($ch);
preg_match('/name="_token" value="([^"]+)"/', $html, $m);
$csrf1 = $m[1] ?? '';

// Step 2: POST login with email
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://127.0.0.1:8001/painel-revendedor/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['_token' => $csrf1, 'email' => 'agente@teste.ao']),
    CURLOPT_FOLLOWLOCATION => false,
]);
curl_exec($ch);

// Read log to get OTP
$log = file_get_contents(__DIR__ . '/../storage/logs/laravel.log');
preg_match_all('/<div class="otp-code">(\d{6})<\/div>/', $log, $otpMatches);
$otp = end($otpMatches[1]);
if (!$otp) {
    // fallback: last 6-digit sequence in log
    preg_match_all('/\b(\d{6})\b/', $log, $otpMatches2);
    $otp = end($otpMatches2[1]);
}
echo "OTP found: $otp\n";

// Step 3: GET verify page for fresh CSRF
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://127.0.0.1:8001/painel-revendedor',
    CURLOPT_POST => false,
    CURLOPT_HTTPGET => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$html2 = curl_exec($ch);
preg_match('/name="_token" value="([^"]+)"/', $html2, $m2);
$csrf2 = $m2[1] ?? $csrf1;

// Step 4: POST OTP verify
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://127.0.0.1:8001/painel-revendedor/verify',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['_token' => $csrf2, 'otp' => $otp]),
    CURLOPT_FOLLOWLOCATION => false,
]);
$verifyResp = curl_exec($ch);
$verifyStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
echo "Verify status: $verifyStatus\n";
echo "Redirect: $location\n";

curl_close($ch);

// Extract session cookie value
$cookies = file_get_contents($jar);
preg_match('/angolawifi_session\s+(\S+)/', $cookies, $sc);
$sessionValue = $sc[1] ?? '';
echo "Session cookie: " . ($sessionValue ? urldecode($sessionValue) : '(not found)') . "\n";
echo "\nCookie file: $jar\n";

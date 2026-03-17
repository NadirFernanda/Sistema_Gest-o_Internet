<?php

namespace App\Http\Controllers;

use App\Mail\AccountOtpMail;
use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class CustomerAccountController extends Controller
{
    // OTP valid duration in minutes
    private const OTP_TTL = 10;

    /**
     * Página "A minha conta" — mostra o estado correcto conforme a sessão:
     *   1. Sem sessão   → formulário de email
     *   2. OTP pendente → formulário de verificação de código
     *   3. Autenticado  → histórico de compras
     */
    public function index(Request $request)
    {
        $email = $request->session()->get('customer_email');

        // OTP pending state (email submitted, code not yet verified)
        $otpEmail   = $request->session()->get('account_otp_email');
        $otpPending = !$email && $otpEmail;

        $orders = collect();

        if ($email) {
            $orders = AutovendaOrder::where('customer_email', $email)
                ->orderByDesc('id')
                ->paginate(10);
        }

        return view('pages.account', [
            'currentEmail' => $email,
            'otpPending'   => $otpPending,
            'otpEmail'     => $otpEmail,
            'orders'       => $orders,
        ]);
    }

    /**
     * Passo 1 — recebe o email, gera OTP e envia por email.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
        ]);

        $email = strtolower(trim($data['email']));

        // Generate a 6-digit numeric OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP + expiry in session (never exposed in response)
        $request->session()->put('account_otp_email',      $email);
        $request->session()->put('account_otp_code',       $otp);
        $request->session()->put('account_otp_expires_at', now()->addMinutes(self::OTP_TTL)->toIso8601String());
        // Invalidate any previous authenticated session
        $request->session()->forget('customer_email');

        Mail::to($email)->send(new AccountOtpMail($otp));

        return redirect()->route('account.index')
            ->with('status', 'Enviámos um código de 6 dígitos para ' . $email . '. Verifique a sua caixa de entrada (e o spam).');
    }

    /**
     * Passo 2 — valida o OTP introduzido pelo cliente.
     */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $sessionEmail   = $request->session()->get('account_otp_email');
        $sessionCode    = $request->session()->get('account_otp_code');
        $sessionExpires = $request->session()->get('account_otp_expires_at');

        // Guard: OTP session must exist
        if (!$sessionEmail || !$sessionCode || !$sessionExpires) {
            return redirect()->route('account.index')
                ->with('error', 'Sessão expirada. Por favor introduza novamente o seu email.');
        }

        // Check expiry
        if (Carbon::parse($sessionExpires)->isPast()) {
            $request->session()->forget(['account_otp_email', 'account_otp_code', 'account_otp_expires_at']);
            return redirect()->route('account.index')
                ->with('error', 'O código expirou. Introduza o seu email de novo para receber um novo código.');
        }

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals($sessionCode, $data['otp'])) {
            return redirect()->route('account.index')
                ->with('error', 'Código incorreto. Verifique o email e tente novamente.');
        }

        // OTP valid — establish authenticated session and clean up OTP data
        $request->session()->forget(['account_otp_email', 'account_otp_code', 'account_otp_expires_at']);
        $request->session()->put('customer_email', $sessionEmail);

        return redirect()->route('account.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['customer_email', 'account_otp_email', 'account_otp_code', 'account_otp_expires_at']);

        return redirect()->route('account.index');
    }

    public function resendEmail(Request $request, AutovendaOrder $order)
    {
        $currentEmail = $request->session()->get('customer_email');

        if (!$currentEmail || $order->customer_email !== $currentEmail) {
            abort(403, 'Esta compra não pertence ao email atualmente selecionado.');
        }

        if (!$order->isPaid()) {
            return redirect()->route('account.index')->with('status', 'Só é possível reenviar o código de pedidos pagos.');
        }

        if (!$order->customer_email) {
            return redirect()->route('account.index')->with('status', 'Este pedido não tem email associado.');
        }

        Mail::to($order->customer_email)->send(new AutovendaWifiCodeMail($order));

        $order->delivered_via_email = true;
        $this->appendDeliveryLog($order, 'email', 'resend_from_customer_area');
        $order->save();

        return redirect()->route('account.index')->with('status', 'Reenvio do código por email efetuado com sucesso.');
    }

    public function openWhatsapp(Request $request, AutovendaOrder $order)
    {
        $currentEmail = $request->session()->get('customer_email');

        if (!$currentEmail || $order->customer_email !== $currentEmail) {
            abort(403, 'Esta compra não pertence ao email atualmente selecionado.');
        }

        if (!$order->isPaid() || !$order->wifi_code) {
            return redirect()->route('account.index')->with('status', 'Só é possível abrir o WhatsApp para pedidos pagos com código disponível.');
        }

        $waText = rawurlencode('Seu código AngolaWiFi: '.$order->wifi_code."\nPlano: ".($order->plan_name ?? $order->plan_id));
        $phoneDigits = $order->customer_phone ? preg_replace('/[^0-9]/', '', $order->customer_phone) : '';
        $waBase = 'https://wa.me/';
        $waUrl = $waBase.($phoneDigits ?: '').'?text='.$waText;

        $this->appendDeliveryLog($order, 'whatsapp', 'open_from_customer_area');
        $order->save();

        return redirect()->away($waUrl);
    }

    private function appendDeliveryLog(AutovendaOrder $order, string $channel, string $type): void
    {
        $meta = $order->meta ?? [];
        $entry = [
            'channel' => $channel,
            'type' => $type,
            'at' => Carbon::now()->toIso8601String(),
        ];

        if (!isset($meta['deliveries']) || !is_array($meta['deliveries'])) {
            $meta['deliveries'] = [];
        }

        $meta['deliveries'][] = $entry;
        $order->meta = $meta;
    }
}


    public function resendEmail(Request $request, AutovendaOrder $order)
    {
        $currentEmail = $request->session()->get('customer_email');

        if (!$currentEmail || $order->customer_email !== $currentEmail) {
            abort(403, 'Esta compra não pertence ao email atualmente selecionado.');
        }

        if (!$order->isPaid()) {
            return redirect()->route('account.index')->with('status', 'Só é possível reenviar o código de pedidos pagos.');
        }

        if (!$order->customer_email) {
            return redirect()->route('account.index')->with('status', 'Este pedido não tem email associado.');
        }

        Mail::to($order->customer_email)->send(new AutovendaWifiCodeMail($order));

        $order->delivered_via_email = true;
        $this->appendDeliveryLog($order, 'email', 'resend_from_customer_area');
        $order->save();

        return redirect()->route('account.index')->with('status', 'Reenvio do código por email efetuado com sucesso.');
    }

    public function openWhatsapp(Request $request, AutovendaOrder $order)
    {
        $currentEmail = $request->session()->get('customer_email');

        if (!$currentEmail || $order->customer_email !== $currentEmail) {
            abort(403, 'Esta compra não pertence ao email atualmente selecionado.');
        }

        if (!$order->isPaid() || !$order->wifi_code) {
            return redirect()->route('account.index')->with('status', 'Só é possível abrir o WhatsApp para pedidos pagos com código disponível.');
        }

        $waText = rawurlencode('Seu código AngolaWiFi: '.$order->wifi_code."\nPlano: ".($order->plan_name ?? $order->plan_id));
        $phoneDigits = $order->customer_phone ? preg_replace('/[^0-9]/', '', $order->customer_phone) : '';
        $waBase = 'https://wa.me/';
        $waUrl = $waBase.($phoneDigits ?: '').'?text='.$waText;

        $this->appendDeliveryLog($order, 'whatsapp', 'open_from_customer_area');
        $order->save();

        return redirect()->away($waUrl);
    }

    private function appendDeliveryLog(AutovendaOrder $order, string $channel, string $type): void
    {
        $meta = $order->meta ?? [];
        $entry = [
            'channel' => $channel,
            'type' => $type,
            'at' => Carbon::now()->toIso8601String(),
        ];

        if (!isset($meta['deliveries']) || !is_array($meta['deliveries'])) {
            $meta['deliveries'] = [];
        }

        $meta['deliveries'][] = $entry;
        $order->meta = $meta;
    }
}

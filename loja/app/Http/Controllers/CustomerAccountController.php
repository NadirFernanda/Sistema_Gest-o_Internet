<?php

namespace App\Http\Controllers;

use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class CustomerAccountController extends Controller
{
    /**
     * Página "A minha conta" baseada em e-mail, com histórico de compras.
     */
    public function index(Request $request)
    {
        $email = $request->session()->get('customer_email');

        $orders = collect();

        if ($email) {
            $orders = AutovendaOrder::where('customer_email', $email)
                ->orderByDesc('id')
                ->paginate(10);
        }

        return view('pages.account', [
            'currentEmail' => $email,
            'orders' => $orders,
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $data['email'];

        // Guarda o e-mail em sessão para as próximas visitas
        $request->session()->put('customer_email', $email);

        return redirect()->route('account.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('customer_email');

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

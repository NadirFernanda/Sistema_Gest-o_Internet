<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function simulateSuccess(Request $request, AutovendaOrder $order, AutovendaOrderService $service)
    {
        // Protótipo: em produção, isto seria chamado pelo gateway (webhook/return URL)
        $paymentReference = $request->input('ref');

        $service->confirmPaymentAndDeliver($order, $paymentReference);

        return view('store.payment-callback-simulated', [
            'order' => $order,
        ]);
    }
}

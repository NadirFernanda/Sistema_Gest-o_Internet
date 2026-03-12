<?php

namespace App\Http\Controllers;

use App\Models\EquipmentOrder;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EquipmentController extends Controller
{
    // ── SG sync ───────────────────────────────────────────────────────────────

    /**
     * Fetch the equipment catalog from the SG and upsert into the local products
     * table. Results are cached for 5 minutes so that each page load isn't slow.
     */
    private function syncFromSG(): void
    {
        if (Cache::has('sg_equipment_synced')) {
            return;
        }

        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')), '/');

        try {
            $http = new Client(['timeout' => 8]);
            $res  = $http->get($sg . '/api/equipment-catalog', ['http_errors' => false]);
            $json = json_decode((string) $res->getBody(), true);
            $items = $json['data'] ?? [];

            foreach ($items as $item) {
                if (empty($item['nome'])) {
                    continue;
                }

                $slug = Str::slug($item['nome']);
                if (empty($slug)) {
                    continue;
                }

                Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name'        => $item['nome'],
                        'description' => $item['descricao'] ?? null,
                        'price_aoa'   => max(0, (int) ($item['preco'] ?? 0)),
                        'stock'       => !empty($item['em_stock']) ? 1 : 0,
                        'image_path'  => $item['imagem_url'] ?? null,
                        'category'    => $item['categoria'] ?? null,
                        'active'      => true,
                    ]
                );
            }

            // Cache for 5 minutes so we don't hammer the SG on every request
            Cache::put('sg_equipment_synced', true, now()->addMinutes(5));
        } catch (\Exception $e) {
            // SG unreachable — the view will use whatever is already in the local DB
        }
    }

    // ── Public catalog ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->syncFromSG();

        $query = Product::active()->orderBy('name');

        if ($request->filled('categoria')) {
            $query->where('category', $request->input('categoria'));
        }

        $products   = $query->get();
        $categories = Product::active()->whereNotNull('category')
                         ->distinct()->pluck('category')->sort()->values();

        return view('equipment.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $this->syncFromSG();

        $product = Product::active()->where('slug', $slug)->firstOrFail();

        return view('equipment.show', compact('product'));
    }

    // ── Cart (session-based) ──────────────────────────────────────────────────

    public function cart()
    {
        $cart = session('equipment_cart', []);

        return view('equipment.cart', compact('cart'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'sometimes|integer|min:1|max:99',
            'order_type' => 'sometimes|string|in:immediate,backorder',
        ]);

        $product   = Product::active()->findOrFail($request->input('product_id'));
        $orderType = $request->input('order_type', $product->isInStock() ? 'immediate' : 'backorder');

        $qty  = max(1, (int) $request->input('quantity', 1));
        $cart = session('equipment_cart', []);
        $key  = (string) $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'unit_price_aoa' => $product->price_aoa,
                'quantity'       => $qty,
                'image_path'     => $product->image_path,
                'order_type'     => $orderType,
            ];
        }

        session(['equipment_cart' => $cart]);

        return redirect()->route('equipment.cart')
            ->with('success', "\"{$product->name}\" adicionado ao carrinho.");
    }

    public function removeFromCart(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);

        $cart = session('equipment_cart', []);
        unset($cart[(string) $request->input('product_id')]);
        session(['equipment_cart' => $cart]);

        return redirect()->route('equipment.cart')
            ->with('success', 'Item removido do carrinho.');
    }

    public function clearCart()
    {
        session()->forget('equipment_cart');

        return redirect()->route('equipment.cart');
    }

    // ── Checkout ──────────────────────────────────────────────────────────────

    public function checkout()
    {
        $cart = session('equipment_cart', []);

        if (empty($cart)) {
            return redirect()->route('equipment.index')
                ->withErrors(['cart' => 'O seu carrinho está vazio.']);
        }

        $total = collect($cart)->sum(fn($item) => $item['unit_price_aoa'] * $item['quantity']);

        return view('equipment.checkout', compact('cart', 'total'));
    }

    public function processCheckout(Request $request)
    {
        $cart = session('equipment_cart', []);

        if (empty($cart)) {
            return redirect()->route('equipment.index')
                ->withErrors(['cart' => 'O seu carrinho está vazio.']);
        }

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'required|string|max:50',
            'customer_address' => 'nullable|string|max:500',
            'payment_method'   => 'required|string|in:' . implode(',', [
                EquipmentOrder::METHOD_MULTICAIXA,
                EquipmentOrder::METHOD_PAYPAL,
                EquipmentOrder::METHOD_CASH,
            ]),
            'notes' => 'nullable|string|max:1000',
        ]);

        $items = collect($cart)->map(fn($item) => [
            'product_id'     => $item['product_id'],
            'product_name'   => $item['product_name'],
            'quantity'       => $item['quantity'],
            'unit_price_aoa' => $item['unit_price_aoa'],
            'order_type'     => $item['order_type'] ?? 'immediate',
        ])->values()->all();

        $total = collect($cart)->sum(fn($item) => $item['unit_price_aoa'] * $item['quantity']);

        $hasBackorder = collect($cart)->contains(
            fn($item) => ($item['order_type'] ?? 'immediate') === 'backorder'
        );
        $orderType             = $hasBackorder ? EquipmentOrder::TYPE_BACKORDER : EquipmentOrder::TYPE_IMMEDIATE;
        $estimatedDeliveryDate = $hasBackorder ? now()->addDays(30)->toDateString()
                                               : now()->addDays(2)->toDateString();

        $order = EquipmentOrder::create([
            'customer_name'           => $validated['customer_name'],
            'customer_email'          => $validated['customer_email'] ?? null,
            'customer_phone'          => $validated['customer_phone'],
            'customer_address'        => $validated['customer_address'] ?? null,
            'items'                   => $items,
            'total_aoa'               => $total,
            'status'                  => EquipmentOrder::STATUS_PENDING,
            'order_type'              => $orderType,
            'estimated_delivery_date' => $estimatedDeliveryDate,
            'payment_method'          => $validated['payment_method'],
            'notes'                   => $validated['notes'] ?? null,
        ]);

        session()->forget('equipment_cart');

        return redirect()->route('equipment.confirmation', $order->id);
    }

    public function confirmation(int $id)
    {
        $order = EquipmentOrder::findOrFail($id);

        return view('equipment.confirmation', compact('order'));
    }
}

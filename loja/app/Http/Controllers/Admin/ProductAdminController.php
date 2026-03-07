<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    public function index()
    {
        $products = Product::orderByDesc('id')->get();

        return view('admin.equipment.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.equipment.products.form', ['product' => new Product()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Product::create($data);

        return redirect()->route('admin.equipment.products.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function edit(int $id)
    {
        $product = Product::findOrFail($id);

        return view('admin.equipment.products.form', compact('product'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $data    = $this->validated($request, $product);

        $product->update($data);

        return redirect()->route('admin.equipment.products.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.equipment.products.index')
            ->with('success', 'Produto eliminado.');
    }

    private function validated(Request $request, ?Product $existing = null): array
    {
        $slugUnique = 'unique:products,slug' . ($existing ? ",{$existing->id}" : '');

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['nullable', 'string', 'max:255', 'alpha_dash', $slugUnique],
            'description' => 'nullable|string|max:5000',
            'price_aoa'   => 'required|integer|min:1',
            'stock'       => 'required|integer|min:0',
            'category'    => 'nullable|string|max:100',
            'active'      => 'sometimes|boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i    = 2;
            while (
                Product::where('slug', $slug)
                    ->when($existing, fn($q) => $q->where('id', '!=', $existing->id))
                    ->exists()
            ) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        $data['active'] = $request->boolean('active', true);

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('availability'), function ($query) use ($request): void {
                $query->where('is_available', $request->string('availability')->toString() === 'available');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
            'filters' => $request->only(['category_id', 'availability']),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_available' => true]),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Producto creado.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $this->deletePublicFile($product->image_path);
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deletePublicFile($product->image_path);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Producto eliminado.');
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}

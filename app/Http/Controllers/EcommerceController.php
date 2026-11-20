<?php

namespace App\Http\Controllers;

use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\RetailCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EcommerceController extends Controller
{
    public function index()
    {
        $total_orders = EcommerceOrder::count();
        $pending_orders = EcommerceOrder::where('status', 'pending')->count();
        $total_products = Product::where('is_published', true)->count();
        $total_customers = RetailCustomer::count();

        $recent_orders = EcommerceOrder::with('customer')->latest()->limit(10)->get();

        return view('ecommerce.index', compact('total_orders', 'pending_orders', 'total_products', 'total_customers', 'recent_orders'));
    }

    public function products(Request $request)
    {
        $products = Product::with('category')
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()->paginate(20);
        $categories = ProductCategory::orderBy('name')->get();

        return view('ecommerce.products', compact('products', 'categories'));
    }

    public function createProduct()
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('ecommerce.create-product', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:product_categories,id',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'vertical' => 'required|in:bags,furniture,accessories',
            'is_custom_order' => 'boolean',
            'is_published' => 'boolean',
            'featured' => 'boolean',
        ]);

        $data['slug'] = Str::slug($request->name).'-'.time();
        $data['is_custom_order'] = $request->boolean('is_custom_order');
        $data['is_published'] = $request->boolean('is_published');
        $data['featured'] = $request->boolean('featured');

        Product::create($data);

        return redirect()->route('ecommerce.products')->with('success', 'Product created.');
    }

    public function editProduct(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('ecommerce.edit-product', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'vertical' => 'required|in:bags,furniture,accessories',
            'is_custom_order' => 'boolean',
            'is_published' => 'boolean',
            'featured' => 'boolean',
        ]);

        $data['is_custom_order'] = $request->boolean('is_custom_order');
        $data['is_published'] = $request->boolean('is_published');
        $data['featured'] = $request->boolean('featured');

        $product->update($data);

        return redirect()->route('ecommerce.products')->with('success', 'Product updated.');
    }

    public function destroyProduct(Product $product)
    {
        $product->delete();

        return redirect()->route('ecommerce.products')->with('success', 'Product removed.');
    }

    public function orders()
    {
        $orders = EcommerceOrder::with('customer')->latest()->paginate(20);

        return view('ecommerce.orders', compact('orders'));
    }

    public function showOrder(EcommerceOrder $order)
    {
        $order->load(['customer', 'items.product']);

        return view('ecommerce.show-order', compact('order'));
    }

    public function updateOrderStatus(Request $request, EcommerceOrder $order)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,in_production,ready,shipped,delivered,cancelled,refunded']);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated.');
    }

    public function categories()
    {
        $categories = ProductCategory::withCount('products')->get();

        return view('ecommerce.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'vertical' => 'required|in:bags,furniture,accessories',
        ]);
        $data['slug'] = Str::slug($request->name).'-'.time();
        ProductCategory::create($data);

        return back()->with('success', 'Category added.');
    }

    public function customers()
    {
        $customers = RetailCustomer::withCount('orders')->latest()->paginate(20);

        return view('ecommerce.customers', compact('customers'));
    }

    public function searchProducts(Request $request)
    {
        $q = $request->get('q', '');
        $products = Product::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
        })->where('is_published', true)->limit(10)->get(['id', 'name', 'sku', 'price']);

        return response()->json($products);
    }
}

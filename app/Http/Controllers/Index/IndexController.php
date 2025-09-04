<?php
namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Mail\ContactSubmitted;
use App\Models\Cart;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\InstructionGuid;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductSizeItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IndexController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Fetch products related to "Boys" and "Girls" categories
            $products = Product::with(['images', 'sizes', 'categories'])
                ->whereHas('categories', function ($query) {
                    $query->whereIn('name', ['Boys', 'Girls']);
                })
                ->get();

            $sizes = ProductSizeItem::all();

            // Ensure persistent cart key (90 days). First visit pe cookie set ho jayegi.
            $cartKey = Cookie::get('cart_key');
            if (! $cartKey) {
                $cartKey = (string) Str::uuid();
                Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
            }

            // Header badge: total quantity
            $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');

            // Fetch only "Boys" and "Girls" categories for the menu
            $uniformCats = Category::whereIn('name', ['Winter Uniform', 'Summer Uniform'])
                ->with('children')
                ->orderBy('name')
                ->get()
                ->unique('name'); // Ensure only one category for each "Winter Uniform" and "Summer Uniform"

            // Return view
            return view('index.index', compact('products', 'sizes', 'cartCount', 'uniformCats'));
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Home page error: ' . $e->getMessage());

            // Return view with empty data, but show error toastr
            return view('index.index', [
                'products'    => collect(), // empty collection
                'sizes'       => collect(),
                'cartCount'   => 0,
                'uniformCats' => collect(),
            ])->with('error', 'Having trouble loading some data. Please try again later.');
        }
    }

    public function show(Request $request, int $id, string $slug)
    {
        // Find the category by ID (faster + unique)
        $category = Category::with('children')->findOrFail($id);

        // Agar slug match nahi karta to 404 return karo
        if (Str::slug($category->name) !== $slug) {
            abort(404);
        }

        // Get descendant IDs (child categories)
        $baseIds = $category->descendantIds();

        // Selected category from query string (optional)

        $selectedId = $request->integer('category');

        if ($selectedId) {
            $selectedCategory = Category::find($selectedId);

            // agar category exist karti hai to filter ids banao
            if ($selectedCategory) {
                $filterIds   = $selectedCategory->descendantIds();
                $filterIds[] = $selectedId;
            } else {
                $selectedId = null;
                $filterIds  = $baseIds; // default: current base
            }
        } else {
            // agar query string me koi category nahi
            $filterIds = $baseIds;
        }

        // Filter products by selected category OR base category
        $filterIds = $selectedId
        ? (Category::find($selectedId)?->descendantIds() ?? [$selectedId])
        : $baseIds;

        $products = Product::with(['images', 'sizes.sizeItem', 'categories'])
            ->whereHas('categories', function ($q) use ($filterIds) {
                $q->whereIn('categories.id', $filterIds);
            })
            ->paginate(12)
            ->appends($request->query());

        // All categories for dropdown
        $categories = Category::orderBy('created_at')->get();

        // Persistent Cart
        $cartKey = Cookie::get('cart_key') ?? tap(
            (string) Str::uuid(),
            fn($k) => Cookie::queue(cookie('cart_key', $k, 60 * 24 * 90, null, null, false, true, false, 'Lax'))
        );
        $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');

        return view('index.category.show-products', [
            'category'   => $category,
            'products'   => $products,
            'categories' => $categories,
            'selectedId' => $selectedId,
            'categoryId' => $selectedId, // 👈 ye add kar do
            'cartCount'  => $cartCount,
        ]);

    }

    public function accessories(Request $request)
    {
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }

        // Header badge: total quantity
        $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');

        // Get category ID from request
        $categoryId = $request->integer('category');

        // Get current category
        $category = Category::find($categoryId);

        // If category exists, get its parent category
        $parentCategory = $category ? $category->parent : null;

        // Query for products (with many-to-many categories relation)
        $query = Product::with(['images', 'sizes.sizeItem', 'categories']);

        if ($categoryId && $category) {
            // Collect IDs of selected category + its descendants
            $categoryIds   = $category->descendantIds();
            $categoryIds[] = $categoryId;

            // Filter products that have these categories
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        // Get products and preserve the filter with appends()
        $products = $query->paginate(10)->appends($request->query());

        // Get categories sorted by the order or creation date
        $categories = Category::orderBy('created_at')->get();

        return view('index.accessories', compact('products', 'categories', 'categoryId', 'cartCount', 'parentCategory', 'category'));
    }

    public function product_details(Request $request, $slug)
    {
        try {
            $product = Product::with(['sizes.sizeItem', 'categories.parent', 'categories.children'])
                ->get()
                ->first(function ($item) use ($slug) {
                    return Str::slug($item->name) === $slug;
                });
            // dd($product);
            if (! $product) {
                abort(404);
            }

            $categories = Category::all();

            // Pehle product ki categories collect karo
            $categoryIds = $product->categories->pluck('id')->toArray();

            $relatedProducts = $product->categories()
                ->with(['products' => function ($q) use ($product) {
                    $q->where('products.id', '!=', $product->id)
                        ->with(['images', 'sizes'])
                        ->inRandomOrder()
                        ->paginate(4);
                }])
                ->get()
                ->pluck('products')
                ->flatten();

            if (! empty($categoryIds)) {
                $relatedProducts = Product::whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds); // 👈 yahan prefix karo
                })
                    ->where('products.id', '!=', $product->id) // 👈 aur yahan bhi
                    ->with(['images', 'sizes'])
                    ->inRandomOrder()
                    ->paginate(4);
            }

            // Persistent Cart
            $cartKey = Cookie::get('cart_key');
            if (! $cartKey) {
                $cartKey = (string) Str::uuid();
                Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
            }
            $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');

            return view('index.product-details', compact('product', 'relatedProducts', 'categories', 'cartCount'));
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function size_chart(Request $request)
    {
        $image = InstructionGuid::where('type', 'size_guide')->first();
        // dd($image);
        // Ensure persistent cart key (90 days). First visit pe cookie set ho jayegi.
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }
        // Header badge: total quantity
        $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');
        return view('index.size-chart', compact('cartCount', 'image'));
    }

    public function contactus(Request $request)
    {
        // Ensure persistent cart key (90 days). First visit pe cookie set ho jayegi.
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }
        // Header badge: total quantity
        $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');
        return view('index.contactus', compact('cartCount'));
    }

    public function washing_instructions(Request $request)
    {
        $image = InstructionGuid::where('type', 'washing_instructions')->first();
        // Ensure persistent cart key (90 days). First visit pe cookie set ho jayegi.
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }
        // Header badge: total quantity
        $cartCount = Cart::where('session_id', $cartKey)->sum('quantity');
        return view('index.washing-instructions', compact('cartCount', 'image'));
    }

    public function bulkAdd(Request $request)
    {
        $items = $request->input('items', []); // items[product_id] = ['size_id'=>x, 'qty'=>y]
        if (empty($items)) {
            return back()->with('error', 'Please select at least one product.');
        }

        $cartKey = Cookie::get('cart_key') ?? (function () {
            $k = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $k, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
            return $k;
        })();

        DB::beginTransaction();
        try {
            foreach ($items as $productId => $row) {
                $sizeId = (int) ($row['size_id'] ?? 0);
                $qty    = max(1, (int) ($row['qty'] ?? 1));

                // validate variant exists
                $variant = ProductSize::where('product_id', $productId)
                    ->where('size_id', $sizeId)->first();
                if (! $variant || $variant->stock < $qty) {
                    continue; // skip invalid/out-of-stock lines
                }

                $line = Cart::where('session_id', $cartKey)
                    ->where('product_id', $productId)
                    ->where('size_id', $sizeId)
                    ->first();

                $price = (float) $variant->price;
                if ($line) {
                    $line->quantity += $qty;
                    $line->subtotal = $line->quantity * $price;
                    $line->save();
                } else {
                    Cart::create([
                        'session_id'      => $cartKey,
                        'product_id'      => $productId,
                        'size_id'         => $sizeId,
                        'product_size_id' => $variant->id,
                        'quantity'        => $qty,
                        'subtotal'        => $price * $qty,
                    ]);
                }
            }
            DB::commit();
            return back()->with('success', 'Selected products added to cart.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Could not add items. Try again.');
        }
    }

    public function cartdetails(Request $request)
    {
        // persistent key
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }

        // items + relations (image/name/size dikhane ke liye)
        $cartItems = Cart::with(['product.images', 'sizeItem', 'variant'])
            ->where('session_id', $cartKey)
            ->get();

        // badge
        $cartCount = (int) $cartItems->sum('quantity');

        // ✅ totals: stored subtotal se
        $subtotal   = (float) $cartItems->sum('subtotal');
        $shipping   = 0.0; // yahan apni shipping logic lagao
        $grandTotal = $subtotal + $shipping;

        return view('index.cart', compact('cartItems', 'cartCount', 'subtotal', 'shipping', 'grandTotal'));
    }

    public function ajaxUpdateQuantity(Request $request, \App\Models\Cart $cart)
    {
        $cartKey = Cookie::get('cart_key');
        abort_unless($cart->session_id === $cartKey, 403);

        $qty   = max(1, (int) $request->input('quantity', 1));
        $price = (float) optional($cart->variant)->price ?? 0;
        $stock = (int) optional($cart->variant)->stock ?? 0;
        $qty   = min($qty, max(1, $stock));

        $cart->update([
            'quantity' => $qty,
            'subtotal' => round($price * $qty, 2),
        ]);

        // ✅ totals from stored subtotals (most reliable)
        $items    = \App\Models\Cart::where('session_id', $cartKey)->get(['quantity', 'subtotal']);
        $subtotal = (float) $items->sum('subtotal');
        $shipping = 0.0;
        $grand    = $subtotal + $shipping;
        $badge    = (int) $items->sum('quantity');

        return response()->json([
            'line'   => [
                'id'         => $cart->id,
                'quantity'   => $cart->quantity,
                'unit_price' => number_format($price, 2),
                'line_total' => number_format($cart->subtotal, 2),
                'stock'      => $stock,
            ],
            'totals' => [
                'subtotal'   => number_format($subtotal, 2),
                'shipping'   => number_format($shipping, 2),
                'grand'      => number_format($grand, 2),
                'badge'      => $badge,
                'badge_text' => $badge > 99 ? '99+' : (string) $badge,
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        // persistent cart key
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }

        // items to show in summary (image/name/size/qty/price)
        $cartItems = Cart::with(['product.images', 'sizeItem', 'variant'])
            ->where('session_id', $cartKey)
            ->get();

        // badge count
        $cartCount = (int) $cartItems->sum('quantity');

        // totals (stored per-line subtotal is most reliable)
        $subtotal   = (float) $cartItems->sum('subtotal');
        $shipping   = 0.0; // adjust if you have shipping rules
        $grandTotal = $subtotal + $shipping;

        // (optional) empty cart -> back to cart
        if ($cartItems->isEmpty()) {
            return redirect()->route('cartdetails')->with('error', 'Your cart is empty.');
        }

        return view('index.checkout', compact('cartItems', 'cartCount', 'subtotal', 'shipping', 'grandTotal'));
    }

    private function cartKey(): string
    {
        $k = Cookie::get('cart_key');
        if (! $k) {
            $k = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $k, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }
        return $k;
    }

    // 4-digit unique order code generator
    private function genOrderCode4(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function place(Request $request)
    {
        // cookie MUST exist at checkout
        $cartKey = Cookie::get('cart_key');
        if (! $cartKey) {
            return redirect()->route('cartdetails')
                ->with('error', 'Your cart session expired. Please add items again.');
        }

        $fields = $request->validate([
            'campus'       => 'required|string|max:100',
            'parent_name'  => 'required|string|max:120',
            'student_name' => 'required|string|max:120',
            'class'        => 'nullable|string|max:50',
            'section'      => 'nullable|string|max:50',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:150',
        ]);

        try {
            $order = DB::transaction(function () use ($fields, $cartKey) {
                // snapshot + lock
                $cartItems = Cart::with(['product', 'sizeItem', 'variant'])
                    ->where('session_id', $cartKey)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('Cart is empty.');
                }

                $subtotal = (float) $cartItems->sum('subtotal');
                $total    = $subtotal; // no shipping/tax for now

                // generate unique 4-digit order number
                $order = null;
                for ($i = 0; $i < 25; $i++) {
                    $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    try {
                        $order = Order::create([
                            'order_number'   => $code, // ← only 4 digits
                            'cart_key'       => $cartKey,
                            'campus'         => $fields['campus'],
                            'parent_name'    => $fields['parent_name'],
                            'student_name'   => $fields['student_name'],
                            'class'          => $fields['class'] ?? null,
                            'section'        => $fields['section'] ?? null,
                            'phone'          => $fields['phone'] ?? null,
                            'email'          => $fields['email'] ?? null,
                            'subtotal'       => round($subtotal, 2),
                            'total'          => round($total, 2),
                            'status'         => 'pending',
                            'payment_status' => 'unpaid',
                        ]);
                        break;
                    } catch (QueryException $e) {
                        // 23000 = unique violation → retry
                        if ($e->getCode() !== '23000') {
                            throw $e;
                        }

                    }
                }
                if (! $order) {
                    throw new \RuntimeException('Could not allocate order number, please retry.');
                }

                // items + optional stock decrement
                foreach ($cartItems as $line) {
                    $p = $line->product;
                    $v = $line->variant;

                    if ($v && $v->stock !== null) {
                        if ((int) $line->quantity > (int) $v->stock) {
                            throw new \RuntimeException("Stock changed for {$p->name}, please update cart.");
                        }
                        $v->decrement('stock', (int) $line->quantity);
                    }

                    $unit = (float) ($v->price ?? 0);

                    OrderItem::create([
                        'order_id'        => $order->id,
                        'product_id'      => $line->product_id,
                        'size_id'         => $line->size_id,
                        'product_size_id' => $line->product_size_id,
                        'product_name'    => $p->name ?? 'Product',
                        'unit_price'      => $unit,
                        'quantity'        => (int) $line->quantity,
                        'line_total'      => round($unit * (int) $line->quantity, 2),
                    ]);
                }

                // clear cart for this key
                Cart::where('session_id', $cartKey)->delete();

                return $order;
            });

            return redirect()
                ->route('order.thankyou', $order)
                ->with('success', 'Order placed successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('cartdetails')
                ->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    public function thankyou(Order $order)
    {
        if (Cookie::get('cart_key') !== $order->cart_key) {
            abort(404);
        }

        // items + product image + size label
        $order->load(['items.product.images', 'items.sizeItem']);

        return view('index.thank-you', compact('order'));
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            // 'website' => ['prohibited'], // optional honeypot
        ]);

        $contact = ContactMessage::create($data);

        // Email admin
        Mail::to(config('mail.contact_admin'))->send(new ContactSubmitted($contact));

        return back()->with('status', 'Thank you! Your message has been sent.');
    }
}

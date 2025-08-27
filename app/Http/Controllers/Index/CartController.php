<?php
// app/Http/Controllers/CartController.php
namespace App\Http\Controllers\Index;

use App\Models\Cart;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cookie;
use RealRashid\SweetAlert\Facades\Alert;



class CartController extends Controller
{
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id'    => 'required|exists:product_size_items,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // ✅ Stable cart key (90 days) — isi ko carts.session_id me store / query karenge
        $cartKey = Cookie::get('cart_key');
        if (!$cartKey) {
            $cartKey = (string) Str::uuid();
            Cookie::queue(cookie('cart_key', $cartKey, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }

        try {
            DB::transaction(function () use ($data, $cartKey) {
                // Variant lock (consistent stock/price)
                $variant = ProductSize::where('product_id', $data['product_id'])
                    ->where('size_id', $data['size_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Existing cart line (lock too)
                $line = Cart::where('session_id', $cartKey)
                    ->where('product_id', $data['product_id'])
                    ->where('size_id', $data['size_id'])
                    ->lockForUpdate()
                    ->first();

                $price      = (float) $variant->price;
                $incoming   = (int) $data['quantity'];
                $currentQty = $line ? (int) $line->quantity : 0;
                $newQty     = $currentQty + $incoming;

                // ✅ Stock check on combined qty
                if ($newQty > (int) $variant->stock) {
                    throw ValidationException::withMessages([
                        'quantity' => "Selected size is short in stock. Available: {$variant->stock}",
                    ]);
                }

                if ($line) {
                    $line->quantity = $newQty;
                    $line->subtotal = round($price * $newQty, 2);
                    $line->product_size_id = $variant->id;
                    $line->save();
                } else {
                    Cart::create([
                        'session_id'      => $cartKey,          // 👈 cookie key yahan save
                        'product_id'      => $data['product_id'],
                        'size_id'         => $data['size_id'],
                        'product_size_id' => $variant->id,
                        'quantity'        => $incoming,
                        'subtotal'        => round($price * $incoming, 2),
                    ]);
                }
            });

            Alert::success('Success', 'Product added to cart successfully!');
            return back();
        } catch (ValidationException $e) {
            // stock short — same page pe error dikhao
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Add to Cart Error: ' . $e->getMessage());
            Alert::error('Error', 'Failed to add product to cart.');
            return back();
        }
    }


    public function update(Request $r)
    {
        $data = $r->validate([
            'id'       => 'required|exists:cart,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $line = Cart::where('id', $data['id'])
            ->where('session_id', $r->session()->getId())
            ->firstOrFail();

        $variant = $line->variant()->firstOrFail(); // product_sizes row
        if ($variant->stock < $data['quantity']) {
            return back()->with('error', 'Not enough stock for this size.');
        }

        $line->quantity = (int) $data['quantity'];
        $line->subtotal = $variant->price * $line->quantity; // recompute
        $line->save();

        return back()->with('success', 'Cart updated.');
    }

    public function ajaxRemove(Request $request, $id)
    {
        $cartKey = Cookie::get('cart_key') ?: $request->session()->getId();

        $item = Cart::where('id', $id)
            ->where('session_id', $cartKey)
            ->first();

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart successfully!'
        ], 200);
    }
}

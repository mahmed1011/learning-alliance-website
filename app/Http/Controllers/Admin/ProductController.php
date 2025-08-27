<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductSize;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductSizeItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{


    public function renderCategories($categories, $selected = [])
    {
        $html = '<ul style="list-style:none; padding-left:15px;">';
        foreach ($categories as $category) {
            $isChecked = in_array($category->id, $selected) ? 'checked' : '';
            $html .= '<li>';
            $html .= '<label style="cursor:pointer;">';
            $html .= '<input type="checkbox" class="category-checkbox" name="categories[]" value="' . $category->id . '" ' . $isChecked . '> ';
            $html .= '<span class="category-label" data-id="' . $category->id . '">' . $category->name . '</span>';
            $html .= '</label>';

            if ($category->children->count()) {
                $html .= $this->renderCategories($category->children, $selected);  // Recursively render children
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function index()
    {
        $products   = Product::with(['images', 'category', 'sizes'])->get();

        // Sirf root categories + unke children recursive
        $categories = Category::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $sizes      = ProductSizeItem::all();
        $renderedCategories = $this->renderCategories($categories, []);
        return view('admin.products.show-product', compact('products', 'categories', 'sizes', 'renderedCategories'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'desc' => 'nullable|string',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sizes.*.size' => 'required|exists:product_size_items,id',
            'sizes.*.price' => 'required|numeric',
            'sizes.*.stock' => 'required|integer|min:0', // Validate stock
        ], [
            'name.unique' => 'Product already exists in the database.',
        ]);


        DB::beginTransaction();

        try {
            // Step 1: Save the product
            $product = Product::create([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);
            // multiple categories attach
            if ($request->has('categories')) {
                $product->categories()->attach($request->categories);
            }
            // Step 2: Save sizes for the product with stock and price
            if ($request->has('sizes')) {
                foreach ($request->sizes as $size) {
                    ProductSize::create([
                        'product_id' => $product->id,
                        'size_id' => $size['size'],
                        'price' => $size['price'],
                        'stock' => $size['stock'],  // Store stock for each size
                    ]);
                }
            }

            // Step 3: Save main image
            if ($request->hasFile('main_image')) {
                $main = $request->file('main_image');
                $mainName = time() . '_main.' . $main->extension();
                $main->storeAs('public/products', $mainName);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $mainName,
                    'main_image' => 'products/' . $mainName,
                    'is_primary' => true,
                ]);
            }

            // Step 4: Save additional images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $imgName = time() . '_' . uniqid() . '.' . $img->extension();
                    $img->storeAs('public/products', $imgName);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'products/' . $imgName,
                        'is_primary' => false,
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Product added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product.'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            // Fetch the product with its sizes, images, and category
            $product = Product::with(['images', 'sizes', 'categories'])->findOrFail($id);

            $categories = Category::with('children')->whereNull('parent_id')->get();
            $sizes      = ProductSizeItem::all();

            $selectedCategories = $product->categories->pluck('id')->toArray();
            // Pass selected sizes (including price and stock) to the view
            $selectedSizes = $product->sizes->map(function ($size) {
                return [
                    'size_id' => $size->size_id, // Size ID
                    'price' => $size->price,     // Price for this size
                    'stock' => $size->stock,     // Stock for this size
                ];
            });
            $renderedCategories = $this->renderCategories($categories, $selectedCategories);

            return view('admin.products.form', compact('product', 'categories', 'sizes', 'selectedSizes', 'renderedCategories'));
        } catch (\Exception $e) {
            Log::error('Product Edit Error: ' . $e->getMessage());
            Alert::error('Error', 'Product not found.');
            return redirect()->route('products');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // ✅ Validation
            $request->validate([
                'name'         => 'required|string|max:255',
                'desc'         => 'nullable|string',
                'categories'   => 'required|array',
                'categories.*' => 'exists:categories,id',
                'sizes'        => 'nullable|array',
                'sizes.*.size' => 'required|exists:product_size_items,id',
                'sizes.*.price' => 'required|numeric|min:0',
                'sizes.*.stock' => 'required|integer|min:0',
                'main_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'images.*'     => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // ✅ Find product
            $product = Product::with(['categories', 'sizes', 'images'])->findOrFail($id);

            // ✅ Update base fields
            $product->update([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);

            // ✅ Sync categories (belongsToMany)
            if ($request->has('categories')) {
                $product->categories()->sync($request->categories);
            }

            // ✅ Update sizes (hasMany style)
            // Update sizes (custom pivot handling)
            if ($request->has('sizes')) {
                // Clear old sizes
                $product->sizes()->delete();

                foreach ($request->sizes as $sizeData) {
                    if (!empty($sizeData['size'])) {
                        $product->sizes()->create([
                            'size_id'    => $sizeData['size'],   // 👈 yeh zaroori hai
                            'price'      => $sizeData['price'],
                            'stock'      => $sizeData['stock'],
                        ]);
                    }
                }
            }


            // ✅ Replace main image if uploaded
            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store('products', 'public');
                $product->images()->updateOrCreate(
                    ['is_primary' => true],
                    ['image_path' => $path, 'is_primary' => true]
                );
            }

            // ✅ Add additional images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }

            return redirect()->route('products')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            Log::error('Product update error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update product. Please try again later.');
        }
    }





    public function deleteImage($id)
    {
        try {
            $image = ProductImage::findOrFail($id);

            if (Storage::exists('public/' . $image->image_path)) {
                Storage::delete('public/' . $image->image_path);
            }

            $image->delete();

            return response()->json(['status' => 'success', 'message' => 'Image deleted']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error deleting image']);
        }
    }





    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            DB::commit();

            Alert::success('Success', 'Product deleted successfully!');
            return redirect()->route('products');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Product Delete Error: ' . $e->getMessage());
            Alert::error('Error', 'Failed to delete product.');
            return back();
        }
    }
}

@extends('index.layout')
@section('content')
    <div class="body-wrapper">
        <!-- breadcrumb start -->
        <div class="breadcrumb">
            <div class="container">
                <ul class="list-unstyled d-flex align-items-center m-0">
                    <li><a href="/">Home</a></li>
                    <li>
                        <svg class="icon icon-breadcrumb" width="64" height="64" viewBox="0 0 64 64" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g opacity="0.4">
                                <path
                                    d="M25.9375 8.5625L23.0625 11.4375L43.625 32L23.0625 52.5625L25.9375 55.4375L47.9375 33.4375L49.3125 32L47.9375 30.5625L25.9375 8.5625Z"
                                    fill="#000" />
                            </g>
                        </svg>
                    </li>
                    <li>Products</li>
                </ul>
            </div>
        </div>
        <!-- breadcrumb end -->
        @php use Illuminate\Support\Str; @endphp

        <main id="MainContent" class="content-for-layout">
            <div class="collection mt-100">
                <div class="container">
                    <div class="row">
                        <section class="col-lg-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="heading_24 mb-0">All products</h2>
                                    <small class="text-muted">
                                        ({{ method_exists($products, 'total') ? $products->total() : $products->count() }}
                                        items)
                                    </small>
                                </div>

                                {{-- Filter + Reset --}}
                                <form method="GET" class="d-flex align-items-center gap-2" id="filterForm">
                                    <label for="categorySelect" class="text_14 mb-0">Filter:</label>
                                    <select name="category" id="categorySelect" class="form-select form-select-sm"
                                        style="min-width:220px">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                data-slug="{{ \Illuminate\Support\Str::slug($cat->name) }}"
                                                @selected($categoryId == $cat->id)>
                                                {{ $cat->name }}
                                                @if ($cat->parent)
                                                    ({{ $cat->parent->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>



                                    {{-- Reset → default (all) --}}
                                    <a href="{{ $category ? route('category.show', [$category->id, Str::slug($category->name)]) : route('home') }}"
                                        class="btn btn-primary btn-sm">Reset</a>
                                </form>


                                {{-- Bulk actions --}}
                                <div class="d-flex gap-2">
                                    <button form="bulkForm" class="btn btn-primary btn-sm" id="btnAddSelected">
                                        Add selected to cart
                                    </button>
                                    <button class="btn btn-primary btn-sm" id="btnAddAll">
                                        Add all to cart
                                    </button>
                                </div>
                            </div>

                            {{-- BULK FORM + PRODUCT LIST --}}
                            <form id="bulkForm" method="POST" action="{{ route('cart.bulkAdd') }}">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width:36px;"><input type="checkbox" id="checkAll"></th>
                                                <th style="width:110px;">Thumbnail</th>
                                                <th>Name</th>
                                                <th style="width:140px;">Price</th>
                                                <th style="width:220px;">Size</th>
                                                <th style="width:160px;">Qty</th>
                                                <th style="width:140px;" class="text-end">Buy</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($products as $product)
                                                @php
                                                    $main =
                                                        $product->images->where('is_primary', true)->first() ??
                                                        $product->images->first();
                                                    $firstVariant =
                                                        $product->sizes->firstWhere('stock', '>', 0) ??
                                                        $product->sizes->first();
                                                    $priceForRow = $firstVariant?->price ?? ($product->price ?? 0);
                                                    $inStock = (int) ($firstVariant?->stock ?? 0);
                                                @endphp
                                                <tr data-row data-product="{{ $product->id }}">
                                                    <td><input type="checkbox" class="row-check"></td>
                                                    <td>
                                                        <img src="{{ asset('storage/' . ($main?->image_path ?? 'default.jpg')) }}"
                                                            alt="{{ $product->name }}"
                                                            style="width:96px;height:96px;object-fit:cover;border-radius:6px;">
                                                    </td>
                                                    <td>
                                                        <div class="product-title">
                                                            <a
                                                                href="{{ route('product.details', Str::slug($product->name)) }}">{{ $product->name }}</a>
                                                        </div>
                                                        <small
                                                            class="product-vendor">{{ $product->categories->pluck('name')->join(', ') }}
                                                        </small>
                                                    </td>
                                                    <td class="cart-item-price">
                                                        <div class="product-price">Rs. <span
                                                                data-line-total>{{ number_format($priceForRow, 2) }}</span>
                                                        </div>

                                                        @php $inStock = (int) ($firstVariant?->stock ?? 0); @endphp
                                                        <small data-stock-note
                                                            class="{{ $inStock > 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $inStock > 0 ? $inStock . ' in stock' : 'Out of stock' }}
                                                        </small>
                                                    </td>

                                                    <td>
                                                        <fieldset>
                                                            <select class="form-select form-select-sm row-size">
                                                                @foreach ($product->sizes as $s)
                                                                    <option value="{{ $s->sizeItem->id }}"
                                                                        data-price="{{ (float) $s->price }}"
                                                                        data-stock="{{ (int) $s->stock }}"
                                                                        @selected($firstVariant && $s->id === $firstVariant->id)
                                                                        @disabled($s->stock <= 0)>
                                                                        {{ $s->sizeItem->size }}
                                                                        {{ $s->stock <= 0 ? '(Out)' : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </fieldset>
                                                    </td>
                                                    <td class="cart-item-quantity d-none d-md-table-cell">
                                                        <div
                                                            class="quantity d-flex align-items-center justify-content-between">
                                                            <button type="button" class="qty-btn dec-qty">−</button>
                                                            <input type="number" class="qty-input"
                                                                value="{{ $inStock > 0 ? 1 : 0 }}"
                                                                min="{{ $inStock > 0 ? 1 : 0 }}"
                                                                @if ($inStock > 0) max="{{ (int) $inStock }}" @endif
                                                                {{ $inStock > 0 ? '' : 'disabled' }} style="width:64px;">

                                                            <button type="button" class="qty-btn inc-qty">+</button>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button"
                                                            class="add-to-cart-btn btn btn-primary btn-sm"
                                                            style="padding-left: 7px;"
                                                            {{ $inStock > 0 ? '' : 'disabled' }}>
                                                            Add to Cart
                                                        </button>

                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">No products found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>

                            <div class="d-flex justify-content-center mt-4">
                                {{ $products->links() }}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // ====== ELEMENTS ======
                const bulkForm = document.getElementById('bulkForm');
                const table = document.querySelector('.table.align-middle');
                const checkAll = document.getElementById('checkAll');
                const btnSel = document.getElementById('btnAddSelected');
                const btnAll = document.getElementById('btnAddAll');

                // (optional) category filter bits if present
                const catSelect = document.getElementById('categorySelect');
                const resetBtn = document.getElementById('resetBtn');

                if (!bulkForm || !table) return; // hard guard if layout changes

                // ====== HELPERS ======
                const clamp = (v, mn, mx) => Math.max(mn, Math.min(mx, v));
                const clearHidden = () =>
                    bulkForm.querySelectorAll('input[type="hidden"][data-dynamic="1"]').forEach(n => n.remove());

                function injectHiddenForRow(row) {
                    const pid = row.dataset.product;
                    const sel = row.querySelector('.row-size');
                    const opt = sel?.selectedOptions?.[0];
                    const stock = parseInt(opt?.dataset.stock || '0', 10);

                    // Skip out-of-stock rows
                    if (stock <= 0) {
                        alert('Selected size is out of stock for a product. Skipping.');
                        return;
                    }

                    const qtyI = row.querySelector('.qty-input');
                    const qty = clamp(parseInt(qtyI?.value || '1', 10), 1, stock);

                    const wrap = document.createElement('div');
                    wrap.innerHTML = `
                    <input type="hidden" data-dynamic="1" name="items[${pid}][size_id]" value="${sel.value}">
                    <input type="hidden" data-dynamic="1" name="items[${pid}][qty]"     value="${qty}">
                    `;
                    bulkForm.appendChild(wrap);
                }

                // ====== MASTER CHECKBOX ======
                checkAll?.addEventListener('change', e => {
                    table.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
                });

                // ====== SIZE CHANGE => price + stock note + qty/max + buttons ======
                table.addEventListener('change', e => {
                    const sel = e.target.closest('.row-size');
                    if (!sel) return;

                    const row = sel.closest('tr[data-row]');
                    const price = Number(sel.selectedOptions[0].dataset.price || 0);
                    const stock = parseInt(sel.selectedOptions[0].dataset.stock || '0', 10);

                    // price
                    const priceSpan = row.querySelector('[data-line-total]');
                    if (priceSpan) priceSpan.textContent = price.toFixed(2);

                    // stock note
                    const note = row.querySelector('[data-stock-note]');
                    if (note) {
                        if (stock > 0) {
                            note.textContent = `${stock} in stock`;
                            note.classList.remove('text-danger');
                            note.classList.add('text-success');
                        } else {
                            note.textContent = 'Out of stock';
                            note.classList.remove('text-success');
                            note.classList.add('text-danger');
                        }
                    }

                    // qty + controls + add button
                    const qty = row.querySelector('.qty-input');
                    const incBtn = row.querySelector('.inc-qty');
                    const decBtn = row.querySelector('.dec-qty');
                    const addBtn = row.querySelector('.add-to-cart-btn, button.btn.btn-success');

                    if (stock > 0) {
                        qty.disabled = false;
                        qty.min = 1;
                        qty.max = stock;
                        qty.value = clamp(parseInt(qty.value || '1', 10), 1, stock);
                        incBtn?.removeAttribute('disabled');
                        decBtn?.removeAttribute('disabled');
                        addBtn?.removeAttribute('disabled');
                    } else {
                        qty.disabled = true;
                        qty.min = 0;
                        qty.removeAttribute('max');
                        qty.value = 0;
                        incBtn?.setAttribute('disabled', 'disabled');
                        decBtn?.setAttribute('disabled', 'disabled');
                        addBtn?.setAttribute('disabled', 'disabled');
                    }
                });

                // ====== SINGLE "ADD TO CART" (per-row button) ======
                table.addEventListener('click', e => {
                    const btn = e.target.closest('.add-to-cart-btn, button.btn.btn-success');
                    if (!btn) return;
                    if (btn.disabled || !/add to cart/i.test(btn.textContent)) return;
                    e.preventDefault();
                    clearHidden();
                    injectHiddenForRow(btn.closest('tr[data-row]'));
                    // If nothing was injected (e.g., OOS), do not submit
                    if (!bulkForm.querySelector('input[data-dynamic="1"]')) return;
                    bulkForm.submit();
                });

                // ====== ADD SELECTED ======
                btnSel?.addEventListener('click', e => {
                    e.preventDefault();
                    clearHidden();
                    const rows = [...table.querySelectorAll('tr[data-row]')].filter(r => r.querySelector(
                        '.row-check')?.checked);
                    if (!rows.length) {
                        alert('Please select at least one product.');
                        return;
                    }
                    rows.forEach(injectHiddenForRow);
                    if (!bulkForm.querySelector('input[data-dynamic="1"]')) {
                        alert('No in-stock items were selected.');
                        return;
                    }
                    bulkForm.submit();
                });

                // ====== ADD ALL ======
                btnAll?.addEventListener('click', e => {
                    e.preventDefault();
                    clearHidden();
                    table.querySelectorAll('tr[data-row]').forEach(injectHiddenForRow);
                    if (!bulkForm.querySelector('input[data-dynamic="1"]')) {
                        alert('No in-stock items to add.');
                        return;
                    }
                    bulkForm.submit();
                });

                // ====== QTY INPUT UX ======
                document.querySelectorAll('.qty-input').forEach(i => {
                    i.setAttribute('step', '1');
                    i.addEventListener('wheel', e => {
                        e.preventDefault();
                    }, {
                        passive: false
                    });
                });

                // ====== QTY +/- (capture-phase to beat theme handlers) ======
                document.addEventListener('click', function(e) {
                    const incBtn = e.target.closest('.inc-qty');
                    const decBtn = e.target.closest('.dec-qty');
                    if (!incBtn && !decBtn) return;

                    const row = (incBtn || decBtn).closest('tr[data-row]');
                    const qty = row?.querySelector('.qty-input');
                    if (!qty || qty.disabled) return;

                    // stop theme handlers
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const min = parseInt(qty.min || '1', 10);
                    const max = parseInt(qty.max || '9999', 10);
                    let v = parseInt(qty.value || String(min), 10);
                    if (isNaN(v)) v = min;

                    if (incBtn && v < max) v += 1;
                    if (decBtn && v > min) v -= 1;

                    qty.value = v;
                }, true);

                // ====== OPTIONAL: category select auto-submit + reset visibility ======
                catSelect?.addEventListener('change', () => catSelect.form.submit());
                if (resetBtn) resetBtn.style.display = catSelect && catSelect.value ? '' : 'none';
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const catSelect = document.getElementById('categorySelect');

                catSelect?.addEventListener('change', () => {
                    const selectedOption = catSelect.options[catSelect.selectedIndex];
                    const categoryId = selectedOption.value;
                    const slug = selectedOption.dataset.slug;

                    if (categoryId && slug) {
                        // redirect with ID + slug
                        window.location.href = `/uniform/${categoryId}/${slug}?category=${categoryId}`;
                    }
                });
            });
        </script>

    </div>
@endsection

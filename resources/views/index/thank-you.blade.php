@extends('index.layout')
@section('content')
    <div class="body-wrapper">
        @php use Illuminate\Support\Str; @endphp

        <main id="MainContent" class="content-for-layout">
            <div class="contact-page">
                <div class="latest-blog-section overflow-hidden home-section">
                    <div class="container py-5">

                        {{-- existing top message --}}
                        <div class="text-center mb-5">
                            <h2>Thank you! 🎉</h2>
                            <p>Your order <strong>{{ $order->order_number }}</strong> has been placed.</p>
                            <p>Total Paid: <strong>Rs. {{ number_format($order->total, 2) }}</strong></p>
                            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Continue Shopping</a>
                        </div>
                        <div class="shipping-address-area billing-area">
                            <div class="form-checkbox d-flex align-items-center mt-4 mb-3">
                                <label class="form-check-label ms-2">
                                    To pay online through Credit/Debit card, kindly <strong><a class="btn btn-primary"
                                            href="https://www.learningalliance.edu.pk/payments/" style="height: 30px;"
                                            onclick="window.open(this.href, '_blank', 'noopener,noreferrer'); return false;">Click
                                            Here</a></strong>
                                    Please know that all orders will be processed after the total amount has been paid
                                    either online or deposit cash at the School’s Accounts Office.
                                </label>
                            </div>
                        </div>
                        <div class="row g-4">
                            {{-- Left: Items --}}
                            <div class="col-lg-8">
                                <div class="card p-4 h-100">
                                    <h5 class="mb-3 heading_24 mb-0">Order Items</h5>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->items as $item)
                                                    @php
                                                        $p = $item->product;
                                                        $size = $item->sizeItem;
                                                        $img =
                                                            optional($p?->images?->where('is_primary', true)->first())
                                                                ->image_path ??
                                                            (optional($p?->images?->first())->image_path ??
                                                                'default.jpg');
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="mini-img-wrapper me-3"
                                                                    style="width:56px;height:56px;overflow:hidden;border-radius:8px;">
                                                                    <img class="mini-img"
                                                                        src="{{ asset('storage/' . $img) }}"
                                                                        alt="{{ $item->product_name }}"
                                                                        style="width:56px;height:56px;object-fit:cover;">
                                                                </div>
                                                                <div>
                                                                    <div class="product-title">
                                                                        @if ($p)
                                                                            <a
                                                                                href="{{ route('product.details', Str::slug($p->name)) }}">{{ $item->product_name }}</a>
                                                                        @else
                                                                            {{ $item->product_name }}
                                                                        @endif
                                                                    </div>
                                                                    @if ($size?->size)
                                                                        <small class="product-vendor">Size:
                                                                            {{ $size->size }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}
                                                        </td>
                                                        <td class="text-end">Rs. {{ number_format($item->line_total, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-end">Subtotal</th>
                                                    <th class="text-end">Rs. {{ number_format($order->subtotal, 2) }}</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" class="text-end">Total</th>
                                                    <th class="text-end">Rs. {{ number_format($order->total, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Order summary / customer info --}}
                            <div class="col-lg-4">
                                <div class="card p-4 h-100">
                                    <h5 class="mb-3 heading_24 mb-0">Order Summary</h5>
                                    <ul class="list-unstyled mb-4">
                                        <li class="d-flex justify-content-between"><span>Order
                                                #</span><strong>{{ $order->order_number }}</strong></li>
                                        <li class="d-flex justify-content-between">
                                            <span>Placed</span><strong>{{ $order->created_at->format('d M Y, h:i A') }}</strong>
                                        </li>
                                        <li class="d-flex justify-content-between"><span>Status</span><strong
                                                class="text-capitalize">{{ $order->status }}</strong></li>
                                        <li class="d-flex justify-content-between"><span>Payment</span><strong
                                                class="text-capitalize">{{ $order->payment_status }}</strong></li>
                                    </ul>

                                    <h6 class="mb-2 heading_24 mb-0">Customer</h6>
                                    <div class="small text-muted">
                                        <div><strong>Campus:</strong> {{ $order->campus }}</div>
                                        <div><strong>Parent:</strong> {{ $order->parent_name }}</div>
                                        <div><strong>Student:</strong> {{ $order->student_name }}</div>
                                        @if ($order->class)
                                            <div><strong>Class:</strong> {{ $order->class }}</div>
                                        @endif
                                        @if ($order->section)
                                            <div><strong>Section:</strong> {{ $order->section }}</div>
                                        @endif
                                        @if ($order->phone)
                                            <div><strong>Phone:</strong> {{ $order->phone }}</div>
                                        @endif
                                        @if ($order->email)
                                            <div><strong>Email:</strong> {{ $order->email }}</div>
                                        @endif
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <a href="{{ route('home') }}" class="btn btn-primary">Continue Shopping</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- /.container -->
                </div>
            </div>
        </main>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    </div>
@endsection

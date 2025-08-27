@extends('index.layout')
@section('content')
    <div class="body-wrapper">
        <main id="MainContent" class="content-for-layout">
            <!-- Carousel -->
            <div id="demo" class="carousel slide" data-bs-ride="carousel" data-bs-interval="1200"
                style="background-color: whitesmoke;">
                <!-- Indicators/dots -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
                </div>

                <!-- The slideshow/carousel -->
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('index') }}/assets/img/bannerimg1.png" alt="Los Angeles" class="d-block"
                            style="width:100%">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('index') }}/assets/img/bannerimg2.png" alt="Chicago" class="d-block"
                            style="width:100%">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('index') }}/assets/img/bannerimg3.png" alt="Chicago" class="d-block"
                            style="width:100%">
                    </div>
                </div>

                <!-- Left and right controls/icons -->
                <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
            <!-- trusted badge start -->
            <div class="mt-60 home-section">
                <div class="section-category-slider aos-init aos-animate" data-aos="fade-up" data-aos-duration="700">
                    <div class="container">
                        <div class="row justify-content-center">
                            @foreach ($uniformCats as $cat)
                                @php
                                    // Ensure both id and slug are being passed correctly
                                    $url = route('category.show', [$cat->id, Str::slug($cat->name)]);
                                    $img = $cat->name == 'Summer Uniform'
                                        ? asset('index/assets/img/summeruniform.png')
                                        : asset('index/assets/img/winteruniform.png');
                                @endphp

                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <a href="{{ $url }}" class="category-block category-block-2 heading_18 medium text-center">
                                        <img src="{{ $img }}" alt="{{ $cat->name }}" class="img-fluid">
                                        <span class="collection-title">{{ $cat->name }}</span>
                                    </a>
                                </div>
                            @endforeach
                            {{-- Accessories static block --}}
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <a href="{{ route('accessories') }}"
                                    class="category-block category-block-2 heading_18 medium text-center">
                                    <img src="{{ asset('index/assets/img/accessories.png') }}" alt="Accessories"
                                        class="img-fluid">
                                    <span class="collection-title">Accessories</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- trusted badge end -->


            <!-- latest blog start -->
            <div class="latest-blog-section mt-100 overflow-hidden home-section">
                <div class="latest-blog-inner">
                    <div class="container">
                        <div class="section-header text-center">
                            <img src="{{ asset('index') }}/assets/img/bannerimg3.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <!-- latest blog end -->
        </main>



        <!-- scrollup start -->
        <button id="scrollup">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>
        <!-- scrollup end -->



        <!-- drawer menu end -->


    </div>

<script>
    $(document).ready(function() {
        // Trigger on Quickview button click
        $(".action-quickview").on('click', function() {
            var productId = $(this).data('id'); // Get product ID

            // Fetch product details using AJAX or any other method
            $.ajax({
                url: '/get-product-details', // Your endpoint to fetch product details
                method: 'GET',
                data: {
                    id: productId
                },
                success: function(response) {
                    // Populate the modal with product data
                    $('#product-title').text(response.title);
                    $('#product-price .regular-price').text('$' + response.price);
                    $('#product-image').attr('src', response.main_image);

                    // Populate Product Thumbnails
                    var thumbnailsHTML = '';
                    response.thumbnails.forEach(function(img) {
                        thumbnailsHTML +=
                            '<div><div class="img-thumb-wrapper"><img src="' + img +
                            '" alt="img" /></div></div>';
                    });
                    $('.qv-thumb-slider').html(thumbnailsHTML);

                    // Inject Product Variants (size, color)
                    var variantHTML = '';
                    response.variants.forEach(function(variant) {
                        variantHTML +=
                            '<li class="variant-item"><input type="radio" value="' +
                            variant.value + '" /><label class="variant-label">' +
                            variant.label + '</label></li>';
                    });
                    $('.product-variant-wrapper').html(variantHTML);
                }
            });
        });
    });
</script>
@endsection

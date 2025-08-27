@extends('index.layout')
@section('content')
    <div class="body-wrapper">
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
                                    fill="#000"></path>
                            </g>
                        </svg>
                    </li>
                    <li>Size Chart</li>
                </ul>
            </div>
        </div>
        <main id="MainContent" class="content-for-layout">
            <div class="contact-page">
                <div class="latest-blog-section mt-100 overflow-hidden home-section">
                    <div class="section-header text-center">
                        <h3 class=" primary-color">All sizes are based on generic UK size guidelines.</h3>
                    </div>
                    <div class="latest-blog-inner">
                        <div class="container">
                            <div class="section-header text-center">
                                @if (isset($image) && $image->image_path)
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="Size Chart">
                                @else
                                <img src="{{ asset('index') }}/assets/img/size-chart.png" alt="">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection

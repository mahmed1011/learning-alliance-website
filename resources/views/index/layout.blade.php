<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Alliance</title>
</head>
@include('index.layout.heads')
<body>
    @include('index.layout.header')
    @yield('content')
    @include('index.layout.footer')
    @include('index.layout.script')
</body>
</html>

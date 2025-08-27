<script src="{{ asset('index') }}/assets/js/vendor.js"></script>
<script src="{{ asset('index') }}/assets/js/main.js"></script>
<!-- JS (before </body>) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
</script>
<!-- Toastr Flash Messages -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        @endif

        @if (session('error'))
            toastr.error(@json(session('error')));
        @endif

        @if (session('success'))
            toastr.success(@json(session('success')));
        @endif
    });
</script>

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('admin') }}/assets/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('admin') }}/assets/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('admin') }}/assets/vendor/js/bootstrap.js"></script>
<script src="{{ asset('admin') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="{{ asset('admin') }}/assets/vendor/js/menu.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('admin') }}/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<!-- Add in your layout head section if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Main JS -->
<script src="{{ asset('admin') }}/assets/js/main.js"></script>

<!-- Page JS -->
<script src="{{ asset('admin') }}/assets/js/dashboards-analytics.js"></script>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<script>
    function confirmation(ev) {
        ev.preventDefault();
        var urlToRedirect = ev.currentTarget.getAttribute('href');
        console.log(urlToRedirect);
        swal({
                title: "Are you sure to cancel this product",
                text: "You will not be able to revert this!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willCancel) => {
                if (willCancel) {



                    window.location.href = urlToRedirect;

                }


            });


    }
</script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<!-- JS (before </body>) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
<script>
    $(document).ready(function() {
        $('#example').DataTable();
    });
</script>

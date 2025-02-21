<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Файлопомойка</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/7145/7145033.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    @yield('style')
</head>

<header>
    <div class="mt-3">
        @include('partials.header')
    </div>
</header>

<body>
    <div class="container mt-3">
        @include('partials.notification')

        @yield('content')

        @if(@\Illuminate\Support\Facades\Auth::user())
            @include('partials.footer')
        @endif
    </div>
</body>

@yield('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

</html>

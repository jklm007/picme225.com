<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Espace Agent - PicMe225</title>
    <!-- CSS / Tailwind ou Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            margin-bottom: 50px;
        }
        .navbar-agent {
            background-color: #1e1e1e;
            border-bottom: 1px solid #333;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-agent px-3 py-3 shadow-sm">
        <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="{{ route('agent.dashboard') }}">
            <span class="fs-4 me-2">🛡️</span> Espace Agent
        </a>
        <div class="ms-auto">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm fw-bold">Retour au site</a>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

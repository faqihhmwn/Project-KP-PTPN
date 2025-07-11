@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="alert alert-success" id="welcome-alert">
            Selamat datang, {{ Auth::user()->name }} dari unit {{ Auth::user()->unit->nama ?? '-' }}!
        </div>
    </div>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('welcome-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 500);
            }
        }, 2000);
    </script>
@endsection

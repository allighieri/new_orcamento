<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Orçamento')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS Customizado -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .auth-card {
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }
        
        .auth-card .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .auth-card .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        }
        
        .auth-card .card-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Alerts -->
            @if(session('success'))
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: '{{ session('success') }}',
                        timer: 5000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'bottom-start'
                    });
                </script>
            @endif

            @if(session('warning'))
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção!',
                        text: '{{ session('warning') }}',
                        timer: 5000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'bottom-start'
                    });
                </script>
            @endif

            @if(session('error'))
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: '{{ session('error') }}',
                        timer: 5000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'bottom-start'
                    });
                </script>
            @endif

            @if($errors->any())
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Validação!',
                        html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
                        showConfirmButton: true
                    });
                </script>
            @endif
            
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
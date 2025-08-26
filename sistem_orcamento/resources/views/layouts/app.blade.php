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
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-calculator"></i> Orça Fácil
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!--
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-building"></i> Cadastros
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('companies.index') }}"><i class="bi bi-building-add"></i> Empresas</a></li>
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categorias</a></li>
                                <li><a class="dropdown-item" href="{{ route('products.index') }}"><i class="bi bi-box-seam"></i> Produtos</a></li>
                                <li><a class="dropdown-item" href="{{ route('clients.index') }}"><i class="bi bi-people"></i> Clientes</a></li>
                                <li><a class="dropdown-item" href="{{ route('contacts.index') }}"><i class="bi bi-person-rolodex"></i> Contatos</a></li>
                            </ul>
                        </li>
                    -->
                        
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}">
                            <i class="bi bi-file-earmark-text"></i> Orçamentos
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->role === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                            <i class="bi bi-building-add"></i> Empresas</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            <i class="bi bi-tags"></i> Categorias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="bi bi-box-seam"></i> Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                            <i class="bi bi-people"></i> Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}" href="{{ route('contacts.index') }}">
                            <i class="bi bi-person-rolodex"></i> Contatos</a>
                    </li>
                    
                    @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people"></i> Usuários</a>
                    </li>
                    @endif
                </ul>
                
                <!-- User Dropdown -->
                @auth
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="bi bi-person"></i> Perfil
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item" href="{{ route('google.settings') }}">
                                    <i class="bi bi-envelope-at"></i> Config Gmail
                                </a>
                            </li>
                            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin']) && Auth::user()->company)
                            <li>
                                <a class="dropdown-item" href="{{ route('companies.show', Auth::user()->company) }}">
                                    <i class="bi bi-building-add"></i> Dados da Empresa
                                </a>
                            </li>
                            @endif
                            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin']))
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('email-templates.*') ? 'active' : '' }}" href="{{ route('email-templates.index') }}">
                                    <i class="bi bi-envelope-paper"></i> Templates Email</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('google.settings') }}">
                                    <i class="bi bi-envelope-paper"></i> Configurar Gmail</a>
                            </li>

                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid mt-4 flex-grow-1">
        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    timer: 5000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
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
                    position: 'top-end'
                });
            </script>
        @endif

        @if(session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    timer: 6000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            </script>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p>&copy; {{ date('Y') }} Sistema de Orçamento. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Modal para Alterar Status do Orçamento -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="bi bi-pencil-square"></i> Alterar Status do Orçamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">Novo Status:</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Selecione um status</option>
                                <option value="Pendente">Pendente</option>
                                <option value="Enviado">Enviado</option>
                                <option value="Em negociação">Em negociação</option>
                                <option value="Aprovado">Aprovado</option>
                                <option value="Expirado">Expirado</option>
                                <option value="Concluído">Concluído</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="saveStatusBtn">
                        <i class="bi bi-check-circle"></i> Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para gerenciar a modal de status
        let currentBudgetId = null;
        
        // Função para abrir a modal de status
        function openStatusModal(budgetId, currentStatus) {
            currentBudgetId = budgetId;
            $('#status').val(currentStatus);
            $('#statusModal').modal('show');
        }
        
        // Aguardar o documento estar pronto
        $(document).ready(function() {
            // Evento para salvar o novo status
            $('#saveStatusBtn').click(function() {
                const newStatus = $('#status').val();
                const budgetId = currentBudgetId;
                
                if (!newStatus) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção!',
                        text: 'Por favor, selecione um status.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    return;
                }
                
                // Criar formulário oculto para enviar via POST com _method PUT (igual ao perfil)
                const form = $('<form>', {
                    method: 'POST',
                    action: `{{ url('/') }}/budgets/${budgetId}/status`
                });
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'PUT'
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'status',
                    value: newStatus
                }));
                
                // Detectar a página atual para determinar o redirecionamento
                const currentPath = window.location.pathname;
                let redirectTo = 'index'; // padrão
                
                if (currentPath.includes('/budgets/') && currentPath.match(/\/budgets\/\d+$/)) {
                    redirectTo = 'show'; // estamos na página de detalhes
                }
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'redirect_to',
                    value: redirectTo
                }));
                
                // Adicionar o formulário ao body e submeter
                $('body').append(form);
                form.submit();
            });
        }); // Fim do document ready
    </script>
    
    @stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Orçamentos')</title>
    
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
        <div class="container mx-auto">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo-orca-ja.png') }}" alt="Orça Já!" style="height: 40px; margin-left: 8px;">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    
                        
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
                            <i class="bi bi-people"></i> Usuários</a> <!-- um comentário novo para teste futuro -->
                    </li>
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}" href="{{ route('subscriptions.index') }}">
                            <i class="bi bi-credit-card"></i> Planos</a>
                    </li>
                </ul>
                
                <!-- User Dropdown -->
                @auth
                <ul class="navbar-nav">
                    <!-- Theme Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Alterar Tema">
                            <i class="bi bi-palette"></i>&nbsp;Tema
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="themeDropdown" style="min-width: 280px;">
                            
                            <div class="theme-color-options d-flex flex-wrap gap-2 justify-content-center">
                                @php
                                    $currentTheme = App\Http\Controllers\SettingsController::getCurrentTheme();
                                @endphp
                                <div class="theme-color-option {{ $currentTheme == 'blue' ? 'active' : '' }}" data-theme="blue" data-color="#0d6efd" title="Azul">
                                    <div class="theme-color-circle" style="background-color: #0d6efd;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'green' ? 'active' : '' }}" data-theme="green" data-color="#198754" title="Verde">
                                    <div class="theme-color-circle" style="background-color: #198754;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'teal' ? 'active' : '' }}" data-theme="teal" data-color="#20c997" title="Verde-azulado">
                                    <div class="theme-color-circle" style="background-color: #20c997;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'cyan' ? 'active' : '' }}" data-theme="cyan" data-color="#0dcaf0" title="Ciano">
                                    <div class="theme-color-circle" style="background-color: #0dcaf0;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'purple' ? 'active' : '' }}" data-theme="purple" data-color="#6f42c1" title="Roxo">
                                    <div class="theme-color-circle" style="background-color: #6f42c1;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'indigo' ? 'active' : '' }}" data-theme="indigo" data-color="#6610f2" title="Índigo">
                                    <div class="theme-color-circle" style="background-color: #6610f2;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'pink' ? 'active' : '' }}" data-theme="pink" data-color="#e83e8c" title="Rosa">
                                    <div class="theme-color-circle" style="background-color: #e83e8c;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'red' ? 'active' : '' }}" data-theme="red" data-color="#dc3545" title="Vermelho">
                                    <div class="theme-color-circle" style="background-color: #dc3545;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'orange' ? 'active' : '' }}" data-theme="orange" data-color="#fd7e14" title="Laranja">
                                    <div class="theme-color-circle" style="background-color: #fd7e14;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'yellow' ? 'active' : '' }}" data-theme="yellow" data-color="#ffc107" title="Amarelo">
                                    <div class="theme-color-circle" style="background-color: #ffc107;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'lime' ? 'active' : '' }}" data-theme="lime" data-color="#32cd32" title="Lima">
                                    <div class="theme-color-circle" style="background-color: #32cd32;"></div>
                                </div>
                                <div class="theme-color-option {{ $currentTheme == 'dark' ? 'active' : '' }}" data-theme="dark" data-color="#495057" title="Escuro">
                                    <div class="theme-color-circle" style="background-color: #495057;"></div>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Clique em uma cor para alterar o tema</small>
                            </div>
                        </div>
                    </li>
                    
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-check"></i> {{ explode(' ', Auth::user()->name)[0] }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}">
                                    <i class="bi bi-person"></i> Perfil
                                </a>
                            </li>
                            
                            @if(Auth::check() && Auth::user()->role === 'super_admin')
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('email-templates.*') ? 'active' : '' }}" href="{{ route('bank-accounts.index') }}">
                                    <i class="bi bi-envelope-paper"></i> Contas</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}" href="{{ route('payment-methods.index') }}">
                                    <i class="bi bi-credit-card"></i> Pagamento</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('payment-option-methods.*') ? 'active' : '' }}" href="{{ route('payment-option-methods.index') }}">
                                    <i class="bi bi-gear"></i> Métodos de Pagamento</a>
                            </li>   
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('companies.index') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                                    <i class="bi bi-building-add"></i> Empresas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('contact-forms.*') ? 'active' : '' }}" href="{{ route('contact-forms.index') }}">
                                    <i class="bi bi-telephone"></i> Contatos
                                </a>
                            </li>
                            @elseif(Auth::check() && Auth::user()->role === 'admin' && Auth::user()->company)
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('email-templates.*') ? 'active' : '' }}" href="{{ route('bank-accounts.index') }}">
                                    <i class="bi bi-envelope-paper"></i> Contas</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}" href="{{ route('payment-methods.index') }}">
                                    <i class="bi bi-credit-card"></i> Pagamento</a>
                            </li>   
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('companies.show', Auth::user()->company) ? 'active' : '' }}" href="{{ route('companies.show', Auth::user()->company) }}">
                                    <i class="bi bi-building-add"></i> Dados da Empresa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('contact-forms.*') ? 'active' : '' }}" href="{{ route('contact-forms.index') }}">
                                    <i class="bi bi-telephone"></i> Contatos
                                </a>
                            </li>
                            @endif
                            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin']))
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('email-templates.*') ? 'active' : '' }}" href="{{ route('email-templates.index') }}">
                                    <i class="bi bi-envelope-paper"></i> Templates Email</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('google.settings') ? 'active' : '' }}" href="{{ route('google.settings') }}">
                                    <i class="bi bi-envelope-paper"></i> Config Gmail</a>
                            </li>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('settings.index') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                                    <i class="bi bi-gear"></i> Configurações</a>
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
                    timer: 3000,
                    timerProgressBar: true,
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
                    timer: 4000,
                    timerProgressBar: true,
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
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'bottom-start'
                });
            </script>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="my-0">&copy; {{ date('Y') }} Todos os direitos reservados - Orça Fácil | Sistema de Orçamento Digital.</p>
            <p class="my-0">By Agência OLHAR DIGITAL</p>
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
        let statusModalBudgetId = null;
        
        // Função para abrir a modal de status
        function openStatusModal(budgetId, currentStatus) {
            statusModalBudgetId = budgetId;
            $('#status').val(currentStatus);
            $('#statusModal').modal('show');
        }
        
        // Aguardar o documento estar pronto
        $(document).ready(function() {
            // Evento para salvar o novo status
            $('#saveStatusBtn').click(function() {
                const newStatus = $('#status').val();
                const budgetId = statusModalBudgetId;
                
                if (!newStatus) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção!',
                        text: 'Por favor, selecione um status.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'bottom-start'
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
                } else if (document.querySelector('.dashboard-page')) {
                    redirectTo = 'dashboard'; // estamos na dashboard
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
    
    <!-- Script global para aplicar tema -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aplicar tema salvo no banco de dados
        const savedTheme = '{{ App\Http\Controllers\SettingsController::getCurrentTheme() }}';
        if (savedTheme && savedTheme !== 'blue') {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
        
        // Funcionalidade do dropdown de tema no menu
        const themeColorOptions = document.querySelectorAll('.theme-color-option');
        
        themeColorOptions.forEach(option => {
            option.addEventListener('click', function() {
                const theme = this.getAttribute('data-theme');
                
                // Remover classe active de todas as opções
                themeColorOptions.forEach(opt => opt.classList.remove('active'));
                
                // Adicionar classe active à opção selecionada
                this.classList.add('active');
                
                // Aplicar tema imediatamente
                applyThemeFromDropdown(theme);
                
                // Fechar o dropdown após seleção
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('themeDropdown'));
                if (dropdown) {
                    dropdown.hide();
                }
            });
        });
        
        function applyThemeFromDropdown(theme) {
            // Fazer requisição AJAX para salvar o tema
            fetch('{{ route("settings.theme") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme: theme })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Aplicar tema imediatamente
                    if (theme === 'blue') {
                        document.documentElement.removeAttribute('data-theme');
                    } else {
                        document.documentElement.setAttribute('data-theme', theme);
                    }
                } else {
                    console.error('Erro ao aplicar tema');
                }
            })
            .catch(error => {
                console.error('Erro na requisição AJAX:', error);
            });
        }
        

    });
    </script>
    
    <!-- Live Search JavaScript -->
    <script src="{{ asset('js/live-search.js') }}"></script>
    
    <!-- SweetAlert Handler -->
    @if(session('sweetalert'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sweetalertData = @json(session('sweetalert'));
            Swal.fire({
                icon: sweetalertData.type,
                title: sweetalertData.title,
                text: sweetalertData.text,
                confirmButtonText: sweetalertData.confirmButtonText || 'OK',
                confirmButtonColor: '#0d6efd'
            });
        });
    </script>
    @endif
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>
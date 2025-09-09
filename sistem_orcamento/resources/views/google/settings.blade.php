@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        Configurações de Email - Google
                    </h4>
                </div>
                <div class="card-body">
                    <div id="google-status" class="mb-4">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>

                    <div id="not-connected" class="d-none">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-octagon"></i>
                            <strong>Integração não configurada</strong><br>
                            Para enviar emails através do sistema, você precisa conectar sua conta Google.
                        </div>
                        
                        <div class="text-center">
                            <a href="{{ route('google.auth') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-google"></i>
                                Conectar com Google
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Como configurar:</h5>
                            <ol>
                                <li>Clique em "Conectar com Google"</li>
                                <li>Faça login com sua conta Google</li>
                                <li>Autorize o acesso para envio de emails</li>
                                <li>Você será redirecionado de volta para o sistema</li>
                            </ol>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-exclamation-diamond-fill"></i>
                                <strong>Importante:</strong> Use uma conta Google dedicada para o sistema ou sua conta principal. 
                                Os emails serão enviados através desta conta.
                            </div>
                        </div>
                    </div>

                    <div id="connected" class="d-none">
                        <div class="alert alert-success">
                            <i class="bi bi-check2-circle"></i>
                            <strong>Integração ativa!</strong><br>
                            Sua conta Google está conectada e pronta para enviar emails.
                            <div class="mt-2">
                                <small class="text-muted">Email conectado: <strong><span id="connected-email">Carregando...</span></strong></small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="bi bi-envelope-at fs-5"></i>
                                        <h5>Pronto para usar</h5>
                                        <p class="text-muted">Você pode enviar emails com PDFs anexados diretamente dos orçamentos.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                       <i class="bi bi-key fs-5"></i>
                                        <h5>Seguro</h5>
                                        <p class="text-muted">Seus dados estão protegidos e a conexão é criptografada.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button id="disconnect-btn" class="btn btn-outline-danger" onclick="confirmDeleteTemplate()">
                                <i class="fas fa-unlink me-2"></i>
                                Desconectar Google
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    checkGoogleStatus();
    
    $('#disconnect-btn').click(function() {
        //if (confirm('Tem certeza que deseja desconectar a integração com Google?')) {
          //  disconnectGoogle();
        //}

        confirmDeleteTemplate();
    });
});

function confirmDeleteTemplate() {
    Swal.fire({
        title: 'Atenção!',
        html: 'Ao desconectar, não será mais possível enviar Orçamento via E-mail!<br /><br />' +
              'Tem certeza que deseja desconectar a integração com Google?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, desconectar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            disconnectGoogle();
        }
    });
}

function checkGoogleStatus() {
    fetch('{{ route('google.status') }}')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('google-status');
            const connectedDiv = document.getElementById('connected');
            const notConnectedDiv = document.getElementById('not-connected');
            
            if (data.authenticated) {
                statusDiv.classList.add('d-none');
                connectedDiv.classList.remove('d-none');
                notConnectedDiv.classList.add('d-none');
                
                // Exibir email se disponível
                const emailSpan = document.getElementById('connected-email');
                if (data.email) {
                    emailSpan.textContent = data.email;
                } else {
                    emailSpan.textContent = 'Email não disponível';
                }
            } else {
                statusDiv.classList.add('d-none');
                connectedDiv.classList.add('d-none');
                notConnectedDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Erro ao verificar status:', error);
            const statusDiv = document.getElementById('google-status');
            statusDiv.innerHTML = '<div class="alert alert-danger">Erro ao verificar status da integração.</div>';
        });
}

function disconnectGoogle() {
    $.post('{{ route("google.disconnect") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: response.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'bottom-start'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: response.message,
                confirmButtonText: 'OK'
            });
        }
    })
    .fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao desconectar integração.',
            confirmButtonText: 'OK'
        });
    });
}



</script>
@endpush
@endsection
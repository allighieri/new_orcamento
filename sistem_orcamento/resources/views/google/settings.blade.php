@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                            <i class="bi bi-check2-circle"></i></i>
                            <strong>Integração ativa!</strong><br>
                            Sua conta Google está conectada e pronta para enviar emails.
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
                            <button id="disconnect-btn" class="btn btn-outline-danger">
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
        if (confirm('Tem certeza que deseja desconectar a integração com Google?')) {
            disconnectGoogle();
        }
    });
});

function checkGoogleStatus() {
    $.get('{{ route("google.status") }}')
        .done(function(response) {
            $('#google-status').addClass('d-none');
            
            if (response.authenticated) {
                $('#connected').removeClass('d-none');
                $('#not-connected').addClass('d-none');
            } else {
                $('#not-connected').removeClass('d-none');
                $('#connected').addClass('d-none');
            }
        })
        .fail(function() {
            $('#google-status').addClass('d-none');
            $('#not-connected').removeClass('d-none');
        });
}

function disconnectGoogle() {
    $.post('{{ route("google.disconnect") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            checkGoogleStatus();
        } else {
            toastr.error(response.message);
        }
    })
    .fail(function() {
        toastr.error('Erro ao desconectar integração.');
    });
}
</script>
@endpush
@endsection
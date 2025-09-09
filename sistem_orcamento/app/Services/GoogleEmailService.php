<?php

namespace App\Services;

use App\Models\GoogleToken;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleEmailService
{
    private $client;
    private $gmail;
    private $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
        $this->initializeClient();
    }

    private function initializeClient()
    {
        // Verificar se as variáveis de ambiente estão configuradas
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET'));
        $redirectUri = config('services.google.redirect_uri', env('GOOGLE_REDIRECT_URI'));
        
        if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            throw new \Exception('Credenciais do Google não configuradas. Verifique as variáveis GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET e GOOGLE_REDIRECT_URI no arquivo .env');
        }
        
        $this->client = new Client();
        $this->client->setApplicationName('Sistema de Orçamento');
        $this->client->setScopes([
            Gmail::GMAIL_SEND,
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ]);
        $this->client->setAuthConfig([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uris' => [$redirectUri]
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        // Carregar token se existir
        $this->loadToken();

        $this->gmail = new Gmail($this->client);
    }

    private function loadToken()
    {
        $googleToken = GoogleToken::where('company_id', $this->companyId)->first();

        if ($googleToken) {
            // Se o token está válido, configura no cliente
            if ($googleToken->isValid()) {
                $this->client->setAccessToken([
                    'access_token' => $googleToken->access_token,
                    'refresh_token' => $googleToken->refresh_token,
                    'expires_in' => $googleToken->expires_at->diffInSeconds(now()),
                    'token_type' => $googleToken->token_type,
                    'scope' => implode(' ', $googleToken->scope ?? [])
                ]);
            }
            // Se o token está expirado, será tratado no método isAuthenticated()
            // quando necessário, evitando renovações desnecessárias
        }
    }

    private function refreshToken(GoogleToken $googleToken)
    {
        try {
            if (empty($googleToken->refresh_token)) {
                throw new \Exception('Refresh token não disponível');
            }
            
            $this->client->refreshToken($googleToken->refresh_token);
            $newToken = $this->client->getAccessToken();
            
            if (!isset($newToken['access_token'])) {
                throw new \Exception('Token de acesso não retornado pela API do Google');
            }

            $googleToken->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                'token_type' => $newToken['token_type'] ?? 'Bearer'
            ]);

            Log::info('Token do Google renovado com sucesso para empresa: ' . $this->companyId, [
                'expires_at' => $googleToken->expires_at,
                'token_type' => $googleToken->token_type
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao renovar token do Google para empresa ' . $this->companyId . ': ' . $e->getMessage(), [
                'error_class' => get_class($e),
                'has_refresh_token' => !empty($googleToken->refresh_token)
            ]);
            
            // Se o refresh token expirou ou é inválido, remove o token da base de dados
            if (strpos($e->getMessage(), 'invalid_grant') !== false || 
                strpos($e->getMessage(), 'Token has been expired') !== false) {
                Log::warning('Refresh token expirado para empresa ' . $this->companyId . '. Removendo token da base de dados.');
                $googleToken->delete();
            }
            
            throw new \Exception('Erro ao renovar token de acesso. Reautorização necessária.');
        }
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                throw new \Exception('Erro na autenticação: ' . $token['error']);
            }

            // Salvar ou atualizar token
            GoogleToken::updateOrCreate(
                ['company_id' => $this->companyId],
                [
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($token['expires_in']),
                    'token_type' => $token['token_type'] ?? 'Bearer',
                    'scope' => explode(' ', $token['scope'] ?? 'https://www.googleapis.com/auth/gmail.send')
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar callback do Google: ' . $e->getMessage());
            return false;
        }
    }

    public function isAuthenticated()
    {
        $googleToken = GoogleToken::where('company_id', $this->companyId)->first();
        
        if (!$googleToken) {
            return false;
        }
        
        // Se o token está válido, retorna true
        if ($googleToken->isValid()) {
            return true;
        }
        
        // Se o token está expirado mas temos refresh_token, tenta renovar
        if ($googleToken->isExpired() && !empty($googleToken->refresh_token)) {
            try {
                $this->refreshToken($googleToken);
                // Recarrega o token após renovação
                $googleToken->refresh();
                return $googleToken->isValid();
            } catch (\Exception $e) {
                Log::warning('Falha ao renovar token automaticamente: ' . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }

    public function sendEmail($to, $subject, $body, $attachmentPath = null)
    {
        try {
            if (!$this->isAuthenticated()) {
                throw new \Exception('Não autenticado com o Google. Configure a integração primeiro.');
            }

            $message = $this->createMessage($to, $subject, $body, $attachmentPath);
            $result = $this->gmail->users_messages->send('me', $message);

            Log::info('Email enviado com sucesso via Google API', [
                'to' => $to,
                'subject' => $subject,
                'message_id' => $result->getId()
            ]);

            return [
                'success' => true,
                'message' => 'Email enviado com sucesso!',
                'message_id' => $result->getId()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email via Google API: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ];
        }
    }

    public function sendBudgetEmail($to, $subject, $budgetData, $attachmentPath = null, $templateId = null)
    {
        try {
            if (!$this->isAuthenticated()) {
                throw new \Exception('Não autenticado com o Google. Configure a integração primeiro.');
            }

            // Renderizar o template HTML
            $htmlBody = $this->renderEmailTemplate($budgetData, $templateId);
            
            // Se um template personalizado foi usado, também renderizar o assunto
            if ($templateId) {
                $emailTemplate = \App\Models\EmailTemplate::where('id', $templateId)
                    ->where('company_id', $this->companyId)
                    ->where('is_active', true)
                    ->first();
                if ($emailTemplate) {
                    $subject = $emailTemplate->render($budgetData, $emailTemplate->subject);
                }
            }
            
            $message = $this->createHtmlMessage($to, $subject, $htmlBody, $attachmentPath);
            $result = $this->gmail->users_messages->send('me', $message);

            Log::info('Email de orçamento enviado com sucesso via Google API', [
                'to' => $to,
                'subject' => $subject,
                'budget_number' => $budgetData['budgetNumber'] ?? 'N/A',
                'template_id' => $templateId,
                'message_id' => $result->getId()
            ]);

            return [
                'success' => true,
                'message' => 'Email enviado com sucesso!',
                'message_id' => $result->getId()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de orçamento via Google API: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Renderizar template de email (personalizado ou padrão)
     */
    private function renderEmailTemplate($budgetData, $templateId = null)
    {
        if ($templateId) {
            $emailTemplate = \App\Models\EmailTemplate::where('id', $templateId)
                ->where('company_id', $this->companyId)
                ->where('is_active', true)
                ->first();
            if ($emailTemplate) {
                return $emailTemplate->render($budgetData);
            }
        }
        
        // Usar template padrão se não houver template personalizado ou se estiver inativo
        return view('emails.budget-template', $budgetData)->render();
    }

    private function createHtmlMessage($to, $subject, $htmlBody, $attachmentPath = null)
    {
        $boundary = uniqid(rand(), true);
        
        // Codificar o assunto para UTF-8 usando MIME encoding
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        
        $rawMessage = "To: {$to}\r\n";
        $rawMessage .= "Subject: {$encodedSubject}\r\n";
        $rawMessage .= "MIME-Version: 1.0\r\n";
        $rawMessage .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
        
        // Corpo do email HTML
        $rawMessage .= "--{$boundary}\r\n";
        $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
        $rawMessage .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $rawMessage .= quoted_printable_encode($htmlBody) . "\r\n\r\n";
        
        // Anexo (se fornecido)
        if ($attachmentPath && file_exists($attachmentPath)) {
            $filename = basename($attachmentPath);
            $fileContent = base64_encode(file_get_contents($attachmentPath));
            
            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
            $rawMessage .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
            $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $rawMessage .= chunk_split($fileContent) . "\r\n";
        }
        
        $rawMessage .= "--{$boundary}--";
        
        $message = new Message();
        $message->setRaw(base64url_encode($rawMessage));
        
        return $message;
    }

    private function createMessage($to, $subject, $body, $attachmentPath = null)
    {
        $boundary = uniqid(rand(), true);
        
        // Codificar o assunto para UTF-8 usando MIME encoding
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        
        $rawMessage = "To: {$to}\r\n";
        $rawMessage .= "Subject: {$encodedSubject}\r\n";
        $rawMessage .= "MIME-Version: 1.0\r\n";
        $rawMessage .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
        
        // Corpo do email
        $rawMessage .= "--{$boundary}\r\n";
        $rawMessage .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $rawMessage .= $body . "\r\n\r\n";
        
        // Anexo (se fornecido)
        if ($attachmentPath && file_exists($attachmentPath)) {
            $filename = basename($attachmentPath);
            $fileContent = base64_encode(file_get_contents($attachmentPath));
            
            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
            $rawMessage .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
            $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $rawMessage .= chunk_split($fileContent) . "\r\n";
        }
        
        $rawMessage .= "--{$boundary}--";
        
        $message = new Message();
        $message->setRaw(base64url_encode($rawMessage));
        
        return $message;
    }

    /**
     * Obtém o email do usuário autenticado
     */
    public function getUserEmail()
    {
        try {
            if (!$this->isAuthenticated()) {
                return null;
            }

            // Usar a API OAuth2 para obter informações do usuário
            $oauth2 = new \Google\Service\Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();
            
            return $userInfo->getEmail();
        } catch (\Exception $e) {
            Log::error('Erro ao obter email do usuário Google: ' . $e->getMessage());
            return null;
        }
    }
}

// Função auxiliar para codificação base64url
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\GoogleEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        try {
            $googleEmailService = new GoogleEmailService(Auth::user()->company_id);
            $authUrl = $googleEmailService->getAuthUrl();
            
            return redirect($authUrl);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao iniciar autenticação com Google: ' . $e->getMessage());
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect()->route('home')->with('error', 'Código de autorização não recebido.');
            }

            $googleEmailService = new GoogleEmailService(Auth::user()->company_id);
            $success = $googleEmailService->handleCallback($code);

            if ($success) {
                return redirect()->route('home')->with('success', 'Integração com Google configurada com sucesso!');
            } else {
                return redirect()->route('home')->with('error', 'Erro ao configurar integração com Google.');
            }
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Erro ao processar callback: ' . $e->getMessage());
        }
    }

    public function checkStatus()
    {
        try {
            $googleEmailService = new GoogleEmailService(Auth::user()->company_id);
            $isAuthenticated = $googleEmailService->isAuthenticated();

            return response()->json([
                'authenticated' => $isAuthenticated,
                'message' => $isAuthenticated ? 'Integração ativa' : 'Integração não configurada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function disconnect()
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Remover token do banco de dados
            \App\Models\GoogleToken::where('company_id', $companyId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Integração com Google desconectada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar: ' . $e->getMessage()
            ], 500);
        }
    }
}
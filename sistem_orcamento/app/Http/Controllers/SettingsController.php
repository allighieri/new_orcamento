<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\UserThemePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Exibir a página de configurações
     */
    public function index()
    {
        $user = auth()->guard('web')->user();
        
        // Super admin não tem configurações específicas
        if ($user->role === 'super_admin') {
            return redirect()->route('dashboard')->with('error', 'Super admin não possui configurações específicas.');
        }
        
        $companyId = session('tenant_company_id');
        $settings = CompanySetting::getForCompany($companyId);
        
        // Carregar tema do banco de dados e sincronizar com a sessão
        $theme = UserThemePreference::getThemeForUser($user->id, $companyId);
        session(['theme' => $theme]);
        
        return view('settings.index', compact('settings'));
    }
    
    /**
     * Obter tema atual do usuário
     */
    public static function getCurrentTheme()
    {
        $user = auth()->guard('web')->user();
        
        if (!$user) {
            return 'blue'; // tema padrão para usuários não autenticados
        }
        
        $companyId = session('tenant_company_id');
        
        if (!$companyId) {
            return 'blue'; // tema padrão se não houver empresa
        }
        
        return UserThemePreference::getThemeForUser($user->id, $companyId);
    }
    
    /**
     * Atualizar as configurações
     */
    public function update(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin não pode alterar configurações
        if ($user->role === 'super_admin') {
            return redirect()->route('dashboard')->with('error', 'Super admin não pode alterar configurações.');
        }
        
        $validator = Validator::make($request->all(), [
            'budget_validity_days' => 'required|integer|min:1|max:120',
            'budget_delivery_days' => 'required|integer|min:1|max:120',
            'enable_pdf_watermark' => 'boolean',
            'show_validity_as_text' => 'boolean',
            'border' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $companyId = session('tenant_company_id');
        $settings = CompanySetting::getForCompany($companyId);
        
        $settings->update([
            'budget_validity_days' => $request->budget_validity_days,
            'budget_delivery_days' => $request->budget_delivery_days,
            'enable_pdf_watermark' => $request->has('enable_pdf_watermark'),
            'show_validity_as_text' => $request->has('show_validity_as_text'),
            'border' => $request->has('border') ? 1 : 0,
        ]);
        
        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Update theme setting.
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:blue,green,teal,cyan,purple,indigo,pink,red,orange,yellow,lime,dark'
        ]);

        $user = auth()->guard('web')->user();
        $companyId = session('tenant_company_id');
        
        // Salvar preferência no banco de dados
        UserThemePreference::setThemeForUser($user->id, $companyId, $request->theme);
        
        // Também manter na sessão para uso imediato
        session(['theme' => $request->theme]);

        return response()->json([
            'success' => true,
            'message' => 'Tema salvo no banco de dados'
        ]);
    }
}

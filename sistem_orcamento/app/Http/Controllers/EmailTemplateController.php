<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $templates = EmailTemplate::forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('email-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('email-templates.create');
    }

    /**
     * Show the visual template builder.
     */
    public function builder(Request $request)
    {
        $template = null;
        
        // Se há um ID de template para editar
        if ($request->has('edit')) {
            $template = EmailTemplate::where('id', $request->get('edit'))
                ->where('company_id', auth()->user()->company_id)
                ->first();
        }
        
        return view('email-templates.builder', compact('template'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,NULL,id,company_id,' . $companyId,
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'header_text' => 'nullable|string',
            'header2_text' => 'nullable|string',
            'initial_message' => 'nullable|string',
            'final_message' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'show_budget_number' => 'boolean',
            'show_budget_value' => 'boolean',
            'show_budget_date' => 'boolean',
            'show_budget_validity' => 'boolean',
            'show_delivery_date' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tratar os campos booleanos explicitamente
        $data = $request->all();
        $data['company_id'] = $companyId;
        $data['is_active'] = $request->input('is_active', 0) == '1' ? 1 : 0;
        $data['show_budget_number'] = $request->input('show_budget_number', 0) == '1' ? 1 : 0;
        $data['show_budget_value'] = $request->input('show_budget_value', 0) == '1' ? 1 : 0;
        $data['show_budget_date'] = $request->input('show_budget_date', 0) == '1' ? 1 : 0;
        $data['show_budget_validity'] = $request->input('show_budget_validity', 0) == '1' ? 1 : 0;
        $data['show_delivery_date'] = $request->input('show_delivery_date', 0) == '1' ? 1 : 0;
        
        $template = EmailTemplate::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template criado com sucesso!',
                'template' => $template
            ]);
        }

        return redirect()->route('email-templates.index')
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        // Verificar se o template pertence à empresa do usuário
        if ($emailTemplate->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado.');
        }
        
        return view('email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        // Verificar se o template pertence à empresa do usuário
        if ($emailTemplate->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado.');
        }
        
        return view('email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        // Verificar se o template pertence à empresa do usuário
        if ($emailTemplate->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id . ',id,company_id,' . $emailTemplate->company_id,
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'header_text' => 'nullable|string',
            'header2_text' => 'nullable|string',
            'initial_message' => 'nullable|string',
            'final_message' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'show_budget_number' => 'boolean',
            'show_budget_value' => 'boolean',
            'show_budget_date' => 'boolean',
            'show_budget_validity' => 'boolean',
            'show_delivery_date' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tratar os campos booleanos explicitamente (checkbox não marcado não é enviado)
        $data = $request->all();
        $data['is_active'] = $request->input('is_active', 0) == '1' ? 1 : 0;
        $data['show_budget_number'] = $request->input('show_budget_number', 0) == '1' ? 1 : 0;
        $data['show_budget_value'] = $request->input('show_budget_value', 0) == '1' ? 1 : 0;
        $data['show_budget_date'] = $request->input('show_budget_date', 0) == '1' ? 1 : 0;
        $data['show_budget_validity'] = $request->input('show_budget_validity', 0) == '1' ? 1 : 0;
        $data['show_delivery_date'] = $request->input('show_delivery_date', 0) == '1' ? 1 : 0;
        
        $emailTemplate->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template atualizado com sucesso!',
                'template' => $emailTemplate
            ]);
        }

        return redirect()->route('email-templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        // Verificar se o template pertence à empresa do usuário
        if ($emailTemplate->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado.');
        }
        
        $emailTemplate->delete();

        return redirect()->route('email-templates.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Preview do template com dados de exemplo
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        // Verificar se o template pertence à empresa do usuário
        if ($emailTemplate->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado.');
        }
        
        // Dados de exemplo para preview
        $sampleData = [
            'recipientName' => 'João Silva',
            'budgetNumber' => '2024-001',
            'budgetValue' => '5.500,00',
            'budgetDate' => date('d/m/Y'),
            'budgetValidity' => '30 dias',
            'budgetStatus' => 'Pendente',
            'companyName' => 'Sua Empresa LTDA',
            'companyAddress' => 'Rua das Flores, 123',
            'companyCity' => 'São Paulo',
            'companyState' => 'SP',
            'companyPhone' => '(11) 99999-9999',
            'companyEmail' => 'contato@suaempresa.com.br'
        ];

        $renderedContent = $emailTemplate->render($sampleData);
        $renderedSubject = $emailTemplate->render($sampleData, $emailTemplate->subject);
        
        return view('email-templates.preview', [
            'emailTemplate' => $emailTemplate,
            'renderedContent' => $renderedContent,
            'renderedSubject' => $renderedSubject
        ]);
    }
}

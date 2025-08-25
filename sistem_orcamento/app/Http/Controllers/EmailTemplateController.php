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
        $templates = EmailTemplate::orderBy('created_at', 'desc')->paginate(10);
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        EmailTemplate::create($request->all());

        return redirect()->route('email-templates.index')
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view('email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $emailTemplate->update($request->all());

        return redirect()->route('email-templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('email-templates.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Preview do template com dados de exemplo
     */
    public function preview(EmailTemplate $emailTemplate)
    {
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

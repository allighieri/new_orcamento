<?php

namespace App\Http\Controllers;

use App\Models\ContactForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'super_admin') {
            $contactForms = ContactForm::with('company')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $companyId = session('tenant_company_id');
            $contactForms = ContactForm::where('company_id', $companyId)
                ->with('company')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('contact-forms.index', compact('contactForms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $companies = [];
        
        if ($user->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('contact-forms.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array|min:1',
            'contacts.*.type' => 'required|in:telefone,celular,whatsapp,email',
            'contacts.*.description' => 'required|string|max:255',
            'contacts.*.active' => 'nullable|boolean'
        ]);
        
        $user = Auth::user();
        $companyId = $user->role === 'super_admin' ? $request->company_id : session('tenant_company_id');
        
        foreach ($request->contacts as $contactData) {
            ContactForm::create([
                'company_id' => $companyId,
                'type' => $contactData['type'],
                'description' => $contactData['description'],
                'active' => isset($contactData['active']) ? 1 : 0
            ]);
        }
        
        return redirect()->route('contact-forms.index')
            ->with('success', 'Contato(s) criado(s) com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactForm $contactForm)
    {
        $user = Auth::user();
        
        // Verificar se o usuário pode ver este contato
        if ($user->role !== 'super_admin' && $contactForm->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        return view('contact-forms.show', compact('contactForm'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactForm $contactForm)
    {
        $user = Auth::user();
        
        // Verificar se o usuário pode editar este contato
        if ($user->role !== 'super_admin' && $contactForm->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $companies = [];
        if ($user->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('contact-forms.edit', compact('contactForm', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactForm $contactForm)
    {
        $user = Auth::user();
        
        // Verificar se o usuário pode editar este contato
        if ($user->role !== 'super_admin' && $contactForm->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $request->validate([
            'type' => 'required|in:telefone,celular,whatsapp,email',
            'description' => 'required|string|max:255',
            'active' => 'boolean'
        ]);
        
        $contactForm->update([
            'type' => $request->type,
            'description' => $request->description,
            'active' => $request->has('active') ? 1 : 0
        ]);
        
        return redirect()->route('contact-forms.index')
            ->with('success', 'Contato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactForm $contactForm)
    {
        $user = Auth::user();
        
        // Verificar se o usuário pode excluir este contato
        if ($user->role !== 'super_admin' && $contactForm->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $contactForm->delete();
        
        return redirect()->route('contact-forms.index')
            ->with('success', 'Contato excluído com sucesso!');
    }
}

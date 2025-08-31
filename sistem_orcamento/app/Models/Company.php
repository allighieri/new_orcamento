<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'fantasy_name',
        'corporate_name',
        'document_number',
        'state_registration',
        'phone',
        'email',
        'address',
        'district',
        'city',
        'state',
        'cep',
        'logo'
    ];

    /**
     * Relacionamento com contatos
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Relacionamento com arquivos PDF
     */
    public function pdfFiles(): HasMany
    {
        return $this->hasMany(PdfFile::class);
    }

    /**
     * Relacionamento com usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relacionamento com formulários de contato
     */
    public function contactForms(): HasMany
    {
        return $this->hasMany(ContactForm::class);
    }

    /**
     * Método helper para consolidar contatos da empresa
     * Prioriza contact-forms sobre dados da tabela companies
     * Remove duplicatas baseado na descrição
     * Ordena emails sempre por último
     */
    public function getConsolidatedContacts()
    {
        $contacts = collect();
        $usedDescriptions = collect();
        
        // Primeiro, adicionar contatos do contact-forms (prioridade)
        $contactForms = $this->contactForms()->active()->get();
        
        foreach ($contactForms as $contactForm) {
            if (!$usedDescriptions->contains($contactForm->description)) {
                $contacts->push([
                    'type' => $contactForm->type,
                    'description' => $contactForm->description,
                    'icon' => $this->getContactIcon($contactForm->type),
                    'source' => 'contact_form'
                ]);
                $usedDescriptions->push($contactForm->description);
            }
        }
        
        // Verificar se telefone da empresa não está em contact-forms
        if ($this->phone && !$this->isPhoneInContactForms($this->phone)) {
            $contacts->push([
                'type' => 'telefone',
                'description' => $this->phone,
                'icon' => $this->getContactIcon('telefone'),
                'source' => 'company'
            ]);
        }
        
        // Verificar se email da empresa não está em contact-forms
        if ($this->email && !$this->isEmailInContactForms($this->email)) {
            $contacts->push([
                'type' => 'email',
                'description' => $this->email,
                'icon' => $this->getContactIcon('email'),
                'source' => 'company'
            ]);
        }
        
        // Ordenar contatos: emails sempre por último
        return $contacts->sortBy(function ($contact) {
            // Emails recebem prioridade 2 (último)
            // Outros contatos recebem prioridade 1 (primeiro)
            return strtolower($contact['type']) === 'email' ? 2 : 1;
        })->values();
    }
    
    /**
     * Verifica se um telefone já existe nos contact-forms
     */
    private function isPhoneInContactForms($phone)
    {
        return $this->contactForms()
            ->active()
            ->whereIn('type', ['telefone', 'celular', 'whatsapp'])
            ->where('description', $phone)
            ->exists();
    }
    
    /**
     * Verifica se um email já existe nos contact-forms
     */
    private function isEmailInContactForms($email)
    {
        return $this->contactForms()
            ->active()
            ->where('type', 'email')
            ->where('description', $email)
            ->exists();
    }
    
    /**
     * Retorna o ícone apropriado para cada tipo de contato
     */
    private function getContactIcon($type)
    {
        // Converter para minúscula para garantir compatibilidade
        $type = strtolower($type);
        
        $icons = [
            'telefone' => 'bi-telephone',
            'celular' => 'bi-phone',
            'whatsapp' => 'bi-whatsapp',
            'email' => 'bi-envelope'
        ];
        
        return $icons[$type] ?? 'bi-info-circle';
    }
}

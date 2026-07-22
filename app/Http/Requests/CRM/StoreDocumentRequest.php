<?php

namespace App\Http\Requests\CRM;

use App\Enums\DocumentType;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::UploadDocuments->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:25600'], // max 25MB
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', new Enum(DocumentType::class)],
            'folder_id' => ['nullable', 'exists:crm_document_folders,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'opportunity_id' => ['nullable', 'exists:crm_opportunities,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }
}

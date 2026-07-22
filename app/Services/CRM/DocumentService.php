<?php

namespace App\Services\CRM;

use App\Models\CrmDocument;
use App\Models\CrmDocumentFolder;
use App\Models\CrmDocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CrmDocument::query()
            ->with(['folder:id,name', 'uploader:id,name', 'latestVersion', 'customer:id,name', 'opportunity:id,title'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc');

        if (! empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['folder_id'])) {
            if ($filters['folder_id'] === 'null' || $filters['folder_id'] === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $filters['folder_id']);
            }
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['opportunity_id'])) {
            $query->where('opportunity_id', $filters['opportunity_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Upload new document (creates Version 1).
     *
     * @param  array<string, mixed>  $data
     */
    public function upload(array $data, UploadedFile $file, User $uploader): CrmDocument
    {
        return DB::transaction(function () use ($data, $file, $uploader) {
            $doc = CrmDocument::create([
                'title' => $data['title'] ?? $file->getClientOriginalName(),
                'description' => $data['description'] ?? null,
                'document_type' => $data['document_type'],
                'folder_id' => $data['folder_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'quotation_id' => $data['quotation_id'] ?? null,
                'is_pinned' => $data['is_pinned'] ?? false,
                'uploaded_by' => $uploader->id,
                'company_id' => $uploader->company_id,
                'branch_id' => $uploader->branch_id,
            ]);

            $filePath = $file->store('crm/documents/'.$doc->id, 'public');

            CrmDocumentVersion::create([
                'document_id' => $doc->id,
                'version_number' => 1,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'uploaded_by' => $uploader->id,
                'change_notes' => 'Initial upload',
            ]);

            return $doc->fresh(['latestVersion', 'uploader']);
        });
    }

    /**
     * Upload new version for existing document.
     */
    public function addVersion(CrmDocument $document, UploadedFile $file, ?string $changeNotes, User $uploader): CrmDocumentVersion
    {
        return DB::transaction(function () use ($document, $file, $changeNotes, $uploader) {
            $nextVersionNumber = ($document->versions()->max('version_number') ?? 0) + 1;
            $filePath = $file->store('crm/documents/'.$document->id, 'public');

            $version = CrmDocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersionNumber,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'uploaded_by' => $uploader->id,
                'change_notes' => $changeNotes ?? "Uploaded version {$nextVersionNumber}",
            ]);

            $document->update([
                'version' => $document->version + 1,
                'updated_at' => now(),
            ]);

            return $version;
        });
    }

    /**
     * Create document folder.
     *
     * @param  array<string, mixed>  $data
     */
    public function createFolder(array $data, User $creator): CrmDocumentFolder
    {
        return CrmDocumentFolder::create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'opportunity_id' => $data['opportunity_id'] ?? null,
            'company_id' => $creator->company_id,
            'created_by' => $creator->id,
        ]);
    }
}

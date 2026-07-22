<?php

namespace App\Http\Controllers\CRM;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreDocumentRequest;
use App\Models\CrmDocument;
use App\Models\CrmDocumentFolder;
use App\Models\CrmDocumentVersion;
use App\Services\CRM\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CrmDocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CrmDocument::class);

        $documents = $this->documentService->getPaginated([
            'document_type' => $request->query('document_type'),
            'folder_id' => $request->query('folder_id'),
            'customer_id' => $request->query('customer_id'),
            'opportunity_id' => $request->query('opportunity_id'),
            'search' => $request->query('search'),
        ]);

        $folders = CrmDocumentFolder::query()
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        return Inertia::render('crm/documents/index', [
            'documents' => $documents,
            'folders' => $folders,
            'filters' => $request->only(['document_type', 'folder_id', 'customer_id', 'opportunity_id', 'search']),
            'documentTypes' => collect(DocumentType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'color' => $t->color(),
            ]),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->documentService->upload($request->validated(), $request->file('file'), $request->user());

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function show(CrmDocument $document): Response
    {
        $this->authorize('view', $document);

        $document->load([
            'uploader:id,name',
            'folder:id,name',
            'customer:id,name',
            'opportunity:id,title',
            'versions.uploader:id,name',
            'comments.author:id,name',
        ]);

        return Inertia::render('crm/documents/show', [
            'document' => $document,
        ]);
    }

    public function storeVersion(Request $request, CrmDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $request->validate([
            'file' => 'required|file|max:25600',
            'change_notes' => 'nullable|string|max:500',
        ]);

        $this->documentService->addVersion($document, $request->file('file'), $request->input('change_notes'), $request->user());

        return redirect()->back()->with('success', 'New version uploaded.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $this->authorize('create', CrmDocument::class);

        $request->validate(['name' => 'required|string|max:100']);

        $this->documentService->createFolder($request->only(['name', 'parent_id', 'customer_id', 'opportunity_id']), $request->user());

        return redirect()->back()->with('success', 'Folder created.');
    }

    public function download(CrmDocument $document, ?int $versionId = null): BinaryFileResponse
    {
        $this->authorize('download', $document);

        if ($versionId) {
            $version = CrmDocumentVersion::where('document_id', $document->id)->findOrFail($versionId);
        } else {
            $version = $document->latestVersion;
        }

        $fullPath = storage_path('app/public/'.$version->file_path);

        return response()->download($fullPath, $version->file_name);
    }

    public function destroy(CrmDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return redirect()->route('crm.documents.index')->with('success', 'Document deleted.');
    }
}

import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { FileText, Plus, Search, Eye, Download, FolderPlus, Folder, File, Pin, HardDrive } from 'lucide-react';
import { useState } from 'react';

interface DocumentVersion {
    id: number;
    file_name: string;
    file_size: number;
    mime_type: string;
    version_number: number;
}

interface DocumentItem {
    id: number;
    title: string;
    description: string | null;
    document_type: string;
    is_pinned: boolean;
    folder?: { id: number; name: string };
    uploader?: { id: number; name: string };
    customer?: { id: number; name: string };
    latest_version?: DocumentVersion;
    updated_at: string;
}

interface FolderItem {
    id: number;
    name: string;
    documents_count: number;
}

interface Props {
    documents: {
        data: DocumentItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    };
    folders: FolderItem[];
    filters: Record<string, string>;
    documentTypes: Array<{ value: string; label: string; color: string }>;
}

export default function DocumentsIndex({ documents, folders, filters, documentTypes }: Props) {
    const { can } = usePermission();
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedFolder, setSelectedFolder] = useState<string | null>(filters.folder_id || null);
    const [showUploadModal, setShowUploadModal] = useState(false);
    const [showFolderModal, setShowFolderModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        file: null as File | null,
        title: '',
        description: '',
        document_type: 'contract',
        folder_id: '',
    });

    const folderForm = useForm({
        name: '',
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/crm/documents', { search: searchQuery, folder_id: selectedFolder || '' }, { preserveState: true });
    };

    const handleUpload = (e: React.FormEvent) => {
        e.preventDefault();
        post('/crm/documents', {
            onSuccess: () => {
                setShowUploadModal(false);
                reset();
            },
        });
    };

    const handleCreateFolder = (e: React.FormEvent) => {
        e.preventDefault();
        folderForm.post('/crm/documents/folders', {
            onSuccess: () => {
                setShowFolderModal(false);
                folderForm.reset();
            },
        });
    };

    const formatBytes = (bytes?: number) => {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    return (
        <>
            <Head title="Repositori Dokumen (Document Vault)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <FileText className="h-6 w-6 text-sky-600" />
                            Dokumen Pelanggan & Kontrak (Document Vault)
                        </h1>
                        <p className="text-sm text-neutral-500">
                            Penyimpanan terpusat untuk berkas gambar, denah CAD, kontrak, dan surat penawaran.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        {can('manage-document-folders') && (
                            <Button variant="outline" onClick={() => setShowFolderModal(true)}>
                                <FolderPlus className="mr-2 h-4 w-4 text-amber-600" /> Buat Folder
                            </Button>
                        )}
                        {can('upload-documents') && (
                            <Button onClick={() => setShowUploadModal(true)} className="bg-sky-600 hover:bg-sky-700 text-white">
                                <Plus className="mr-2 h-4 w-4" /> Unggah Dokumen
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    {/* Left Sidebar: Folder Tree */}
                    <div className="space-y-4">
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardContent className="p-4">
                                <h3 className="text-xs font-bold uppercase text-neutral-400 mb-3">Folder Dokumen</h3>
                                <div className="space-y-1">
                                    <button
                                        onClick={() => {
                                            setSelectedFolder(null);
                                            router.get('/crm/documents', { search: searchQuery }, { preserveState: true });
                                        }}
                                        className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg ${
                                            selectedFolder === null ? 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' : 'text-neutral-700 hover:bg-neutral-50'
                                        }`}
                                    >
                                        <div className="flex items-center gap-2">
                                            <HardDrive className="h-4 w-4 text-sky-600" />
                                            <span>Semua Dokumen</span>
                                        </div>
                                        <span>{documents.total}</span>
                                    </button>

                                    {folders.map(f => (
                                        <button
                                            key={f.id}
                                            onClick={() => {
                                                setSelectedFolder(String(f.id));
                                                router.get('/crm/documents', { search: searchQuery, folder_id: f.id }, { preserveState: true });
                                            }}
                                            className={`w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg ${
                                                selectedFolder === String(f.id) ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'text-neutral-700 hover:bg-neutral-50'
                                            }`}
                                        >
                                            <div className="flex items-center gap-2">
                                                <Folder className="h-4 w-4 text-amber-500" />
                                                <span className="truncate">{f.name}</span>
                                            </div>
                                            <span className="text-neutral-400">{f.documents_count}</span>
                                        </button>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right 3 columns: Documents List */}
                    <div className="space-y-4 lg:col-span-3">
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardContent className="p-4">
                                <form onSubmit={handleSearch} className="flex gap-2">
                                    <div className="relative flex-1">
                                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                                        <input
                                            type="text"
                                            placeholder="Cari nama dokumen atau deskripsi..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            className="w-full pl-9 pr-4 py-2 text-sm border rounded-lg border-neutral-200 dark:border-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                        />
                                    </div>
                                    <Button type="submit" variant="outline">Cari</Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-left text-sm">
                                        <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                                            <tr>
                                                <th className="px-4 py-3">Nama Berkas</th>
                                                <th className="px-4 py-3">Tipe</th>
                                                <th className="px-4 py-3">Versi</th>
                                                <th className="px-4 py-3">Ukuran</th>
                                                <th className="px-4 py-3">Pengunggah</th>
                                                <th className="px-4 py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                            {documents.data.length === 0 ? (
                                                <tr>
                                                    <td colSpan={6} className="px-4 py-8 text-center text-neutral-500">
                                                        Belum ada dokumen yang diunggah.
                                                    </td>
                                                </tr>
                                            ) : (
                                                documents.data.map((doc) => (
                                                    <tr key={doc.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                                        <td className="px-4 py-3">
                                                            <div className="flex items-center gap-2">
                                                                <File className="h-4 w-4 text-sky-600 shrink-0" />
                                                                <div>
                                                                    <Link href={`/crm/documents/${doc.id}`} className="font-semibold text-neutral-900 dark:text-neutral-100 hover:text-sky-600">
                                                                        {doc.title}
                                                                    </Link>
                                                                    {doc.customer && (
                                                                        <span className="block text-xs text-neutral-400">{doc.customer.name}</span>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium uppercase bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                                {doc.document_type}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3 text-xs font-bold text-neutral-600">
                                                            v{doc.latest_version?.version_number || 1}
                                                        </td>
                                                        <td className="px-4 py-3 text-xs text-neutral-500 whitespace-nowrap">
                                                            {formatBytes(doc.latest_version?.file_size)}
                                                        </td>
                                                        <td className="px-4 py-3 text-xs text-neutral-700 dark:text-neutral-300">
                                                            {doc.uploader?.name || '-'}
                                                        </td>
                                                        <td className="px-4 py-3 text-right space-x-2">
                                                            <a href={`/crm/documents/${doc.id}/download`} className="text-sky-600 hover:text-sky-800 inline-flex items-center gap-1 font-medium">
                                                                <Download className="h-4 w-4" /> Download
                                                            </a>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Upload Modal */}
                {showUploadModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Unggah Dokumen Baru</h2>
                            <form onSubmit={handleUpload} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Pilih Berkas</label>
                                    <input
                                        type="file"
                                        required
                                        onChange={(e) => setData('file', e.target.files ? e.target.files[0] : null)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Judul Dokumen</label>
                                    <input
                                        type="text"
                                        placeholder="Judul unik dokumen..."
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Tipe Dokumen</label>
                                        <select
                                            value={data.document_type}
                                            onChange={(e) => setData('document_type', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        >
                                            {documentTypes.map(t => (
                                                <option key={t.value} value={t.value}>{t.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Folder Target</label>
                                        <select
                                            value={data.folder_id}
                                            onChange={(e) => setData('folder_id', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        >
                                            <option value="">Tanpa Folder (Root)</option>
                                            {folders.map(f => (
                                                <option key={f.id} value={f.id}>{f.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                                <div className="flex justify-end gap-2 pt-4">
                                    <Button type="button" variant="outline" onClick={() => setShowUploadModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Unggah Berkas
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* Create Folder Modal */}
                {showFolderModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Buat Folder Baru</h2>
                            <form onSubmit={handleCreateFolder} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Nama Folder</label>
                                    <input
                                        type="text"
                                        required
                                        placeholder="Contoh: Kontrak 2026"
                                        value={folderForm.data.name}
                                        onChange={(e) => folderForm.setData('name', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="flex justify-end gap-2 pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowFolderModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={folderForm.processing} className="bg-amber-600 hover:bg-amber-700 text-white">
                                        Buat Folder
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

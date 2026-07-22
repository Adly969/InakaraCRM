import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FileText, ArrowLeft, Download, Plus, Clock, User, HardDrive } from 'lucide-react';
import { useState } from 'react';

interface Version {
    id: number;
    version_number: number;
    file_name: string;
    file_size: number;
    mime_type: string;
    change_notes: string | null;
    created_at: string;
    uploader?: { id: number; name: string };
}

interface Props {
    document: {
        id: number;
        title: string;
        description: string | null;
        document_type: string;
        folder?: { id: number; name: string };
        customer?: { id: number; name: string };
        uploader?: { id: number; name: string };
        versions: Version[];
    };
}

export default function DocumentShow({ document }: Props) {
    const [showVersionModal, setShowVersionModal] = useState(false);

    const { data, setData, post, processing, reset } = useForm({
        file: null as File | null,
        change_notes: '',
    });

    const handleUploadVersion = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/crm/documents/${document.id}/versions`, {
            onSuccess: () => {
                setShowVersionModal(false);
                reset();
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
            <Head title={`Dokumen: ${document.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <Link href="/crm/documents" className="inline-flex items-center gap-1 text-xs text-sky-600 hover:underline mb-2">
                        <ArrowLeft className="h-3 w-3" /> Kembali ke Document Vault
                    </Link>
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <FileText className="h-6 w-6 text-sky-600" />
                            {document.title}
                        </h1>
                        <Button onClick={() => setShowVersionModal(true)} className="bg-sky-600 hover:bg-sky-700 text-white">
                            <Plus className="mr-2 h-4 w-4" /> Unggah Versi Baru
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Versions History */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">
                                    Riwayat Versi Berkas ({document.versions.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <table className="w-full text-left text-sm">
                                    <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                                        <tr>
                                            <th className="px-4 py-3">Versi</th>
                                            <th className="px-4 py-3">Nama Berkas</th>
                                            <th className="px-4 py-3">Ukuran</th>
                                            <th className="px-4 py-3">Catatan Perubahan</th>
                                            <th className="px-4 py-3">Pengunggah</th>
                                            <th className="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                        {document.versions.map((ver) => (
                                            <tr key={ver.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                                <td className="px-4 py-3 font-bold text-sky-600">
                                                    v{ver.version_number}
                                                </td>
                                                <td className="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                                                    {ver.file_name}
                                                </td>
                                                <td className="px-4 py-3 text-xs text-neutral-500">
                                                    {formatBytes(ver.file_size)}
                                                </td>
                                                <td className="px-4 py-3 text-xs text-neutral-600 dark:text-neutral-400">
                                                    {ver.change_notes || '-'}
                                                </td>
                                                <td className="px-4 py-3 text-xs text-neutral-700 dark:text-neutral-300">
                                                    {ver.uploader?.name || '-'}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <a href={`/crm/documents/${document.id}/download/${ver.id}`} className="text-sky-600 hover:text-sky-800 text-xs font-bold inline-flex items-center gap-1">
                                                        <Download className="h-3 w-3" /> Unduh
                                                    </a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Metadata Dokumen</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div>
                                    <span className="text-xs text-neutral-400 block">Tipe</span>
                                    <span className="font-semibold uppercase text-neutral-900 dark:text-neutral-100">{document.document_type}</span>
                                </div>
                                {document.customer && (
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Pelanggan</span>
                                        <span className="font-semibold text-sky-600">{document.customer.name}</span>
                                    </div>
                                )}
                                <div>
                                    <span className="text-xs text-neutral-400 block">Pengunggah Awal</span>
                                    <span className="font-semibold text-neutral-800 dark:text-neutral-200">{document.uploader?.name || '-'}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Upload Version Modal */}
                {showVersionModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Unggah Versi Baru</h2>
                            <form onSubmit={handleUploadVersion} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Pilih Berkas Baru</label>
                                    <input
                                        type="file"
                                        required
                                        onChange={(e) => setData('file', e.target.files ? e.target.files[0] : null)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Catatan Perubahan (Revisi)</label>
                                    <textarea
                                        rows={2}
                                        placeholder="Alasan revisi..."
                                        value={data.change_notes}
                                        onChange={(e) => setData('change_notes', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="flex justify-end gap-2 pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowVersionModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Unggah Versi
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

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PaymentAttachmentController extends Controller
{
    /**
     * Store a newly created attachment.
     */
    public function store(Request $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('update', $payment);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('payments/attachments', 'public');

            // ponytail: Virus scan hook — EXT-10-006. Future security scanner integration.

            PaymentAttachment::create([
                'payment_id' => $payment->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'uploaded_by' => Auth::id() ?? 1,
            ]);

            return back()->with('success', 'Attachment uploaded successfully.');
        }

        return back()->withErrors(['file' => 'Failed to upload attachment.']);
    }
}

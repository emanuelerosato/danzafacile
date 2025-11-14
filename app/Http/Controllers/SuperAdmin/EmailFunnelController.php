<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailFunnelController extends Controller
{
    /**
     * Display a listing of the email templates
     */
    public function index()
    {
        $templates = EmailTemplate::ordered()->get();

        $stats = [
            'total' => $templates->count(),
            'active' => $templates->where('is_active', true)->count(),
            'inactive' => $templates->where('is_active', false)->count(),
        ];

        return view('super-admin.email-funnel.index', compact('templates', 'stats'));
    }

    /**
     * Show the form for editing the specified email template
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('super-admin.email-funnel.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified email template
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $emailTemplate->update($validated);

        return redirect()
            ->route('super-admin.email-funnel.index')
            ->with('success', 'Template email aggiornato con successo');
    }

    /**
     * Toggle active status of email template
     */
    public function toggleActive(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active
        ]);

        return redirect()
            ->route('super-admin.email-funnel.index')
            ->with('success', 'Status template aggiornato');
    }
}

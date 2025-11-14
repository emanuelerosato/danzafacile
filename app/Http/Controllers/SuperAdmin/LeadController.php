<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

        // Filtro per status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Ricerca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordinamento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate(20)->withQueryString();

        // Statistiche
        $stats = [
            'total' => Lead::count(),
            'nuovo' => Lead::status('nuovo')->count(),
            'contattato' => Lead::status('contattato')->count(),
            'demo_inviata' => Lead::status('demo_inviata')->count(),
            'interessato' => Lead::status('interessato')->count(),
            'chiuso_vinto' => Lead::won()->count(),
            'chiuso_perso' => Lead::lost()->count(),
        ];

        return view('super-admin.leads.index', compact('leads', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        return view('super-admin.leads.show', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|in:nuovo,contattato,demo_inviata,interessato,chiuso_vinto,chiuso_perso',
            'notes' => 'nullable|string',
        ]);

        // Aggiorna timestamp se cambio status
        if ($validated['status'] === 'contattato' && $lead->status !== 'contattato') {
            $validated['contacted_at'] = now();
        }

        if ($validated['status'] === 'demo_inviata' && $lead->status !== 'demo_inviata') {
            $validated['demo_sent_at'] = now();
        }

        $lead->update($validated);

        return redirect()
            ->route('super-admin.leads.show', $lead)
            ->with('success', 'Lead aggiornato con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()
            ->route('super-admin.leads.index')
            ->with('success', 'Lead eliminato con successo');
    }
}

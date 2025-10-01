<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffSchedule;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffScheduleController extends Controller
{

    public function index(Request $request)
    {
        $query = StaffSchedule::with(['staff', 'creator', 'confirmer']);

        // Filtri
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Ricerca testuale
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('staff', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Ordinamento predefinito
        $query->orderBy('date', 'desc')->orderBy('start_time', 'asc');

        $schedules = $query->paginate(20);
        $staff = Staff::where('school_id', Auth::user()->school_id)->get();

        // Statistiche
        $stats = [
            'total_schedules' => StaffSchedule::count(),
            'today_schedules' => StaffSchedule::whereDate('date', today())->count(),
            'this_week_schedules' => StaffSchedule::whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'pending_confirmations' => StaffSchedule::where('status', 'scheduled')->count(),
        ];

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'schedules' => $schedules->items(),
                'pagination' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total(),
                ],
                'stats' => $stats,
                'staff' => $staff
            ]);
        }

        return view('admin.staff-schedules.index', compact('schedules', 'staff', 'stats'));
    }

    public function create()
    {
        $staff = Staff::where('school_id', Auth::user()->school_id)->get();
        return view('admin.staff-schedules.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:course,event,administrative,maintenance,other',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'max_hours' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Verifica sovrapposizione
        $overlap = StaffSchedule::where('staff_id', $validated['staff_id'])
            ->whereDate('date', $validated['date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['overlap' => 'Esiste già un turno per questo staff membro nell\'orario specificato.'])->withInput();
        }

        $validated['school_id'] = Auth::user()->school_id;
        $validated['created_by'] = Auth::id();

        StaffSchedule::create($validated);

        return redirect()->route('admin.staff-schedules.index')
            ->with('success', 'Turno creato con successo.');
    }

    public function show(StaffSchedule $staffSchedule)
    {
        $staffSchedule->load(['staff', 'creator', 'confirmer']);
        return view('admin.staff-schedules.show', compact('staffSchedule'));
    }

    public function edit(StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->isEditable()) {
            return redirect()->route('admin.staff-schedules.index')
                ->with('error', 'Questo turno non può essere modificato.');
        }

        $staff = Staff::where('school_id', Auth::user()->school_id)->get();
        return view('admin.staff-schedules.edit', compact('staffSchedule', 'staff'));
    }

    public function update(Request $request, StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->isEditable()) {
            return redirect()->route('admin.staff-schedules.index')
                ->with('error', 'Questo turno non può essere modificato.');
        }

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:course,event,administrative,maintenance,other',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'max_hours' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Verifica sovrapposizione (escludendo il record corrente)
        $overlap = StaffSchedule::where('staff_id', $validated['staff_id'])
            ->where('id', '!=', $staffSchedule->id)
            ->whereDate('date', $validated['date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['overlap' => 'Esiste già un turno per questo staff membro nell\'orario specificato.'])->withInput();
        }

        $staffSchedule->update($validated);

        return redirect()->route('admin.staff-schedules.index')
            ->with('success', 'Turno aggiornato con successo.');
    }

    public function destroy(StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->canBeCancelled()) {
            return redirect()->route('admin.staff-schedules.index')
                ->with('error', 'Questo turno non può essere eliminato.');
        }

        $staffSchedule->delete();

        return redirect()->route('admin.staff-schedules.index')
            ->with('success', 'Turno eliminato con successo.');
    }

    public function confirm(StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->canBeConfirmed()) {
            return back()->with('error', 'Questo turno non può essere confermato.');
        }

        $staffSchedule->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Turno confermato con successo.');
    }

    public function complete(StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->canBeCompleted()) {
            return back()->with('error', 'Questo turno non può essere segnato come completato.');
        }

        $staffSchedule->update([
            'status' => 'completed'
        ]);

        return back()->with('success', 'Turno segnato come completato.');
    }

    public function cancel(StaffSchedule $staffSchedule)
    {
        if (!$staffSchedule->canBeCancelled()) {
            return back()->with('error', 'Questo turno non può essere annullato.');
        }

        $staffSchedule->update([
            'status' => 'cancelled'
        ]);

        return back()->with('success', 'Turno annullato con successo.');
    }

    public function markNoShow(StaffSchedule $staffSchedule)
    {
        if ($staffSchedule->status !== 'confirmed') {
            return back()->with('error', 'Solo i turni confermati possono essere segnati come assenti.');
        }

        $staffSchedule->update([
            'status' => 'no_show'
        ]);

        return back()->with('success', 'Turno segnato come assente.');
    }

    public function calendar(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        $schedules = StaffSchedule::with('staff')
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($schedule) {
                return $schedule->date->format('Y-m-d');
            });

        $staff = Staff::where('school_id', Auth::user()->school_id)->get();

        return view('admin.staff-schedules.calendar', compact('schedules', 'staff', 'currentDate', 'startOfWeek', 'endOfWeek'));
    }

    public function export(Request $request)
    {
        $query = StaffSchedule::with(['staff']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $schedules = $query->orderBy('date')->orderBy('start_time')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="turni-staff-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($schedules) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Header CSV
            fputcsv($file, [
                'Data',
                'Staff',
                'Titolo',
                'Tipo',
                'Ora Inizio',
                'Ora Fine',
                'Durata',
                'Luogo',
                'Stato',
                'Tariffa Oraria',
                'Compenso Calcolato',
                'Note'
            ]);

            foreach ($schedules as $schedule) {
                fputcsv($file, [
                    $schedule->date->format('d/m/Y'),
                    $schedule->staff->full_name ?? 'N/A',
                    $schedule->title,
                    $schedule->type_label,
                    $schedule->start_time->format('H:i'),
                    $schedule->end_time->format('H:i'),
                    $schedule->duration,
                    $schedule->location,
                    $schedule->status,
                    $schedule->hourly_rate ? '€' . number_format($schedule->hourly_rate, 2) : 'N/A',
                    $schedule->hourly_rate ? '€' . number_format($schedule->calculated_pay, 2) : 'N/A',
                    $schedule->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
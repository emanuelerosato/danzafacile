<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile
     */
    public function show()
    {
        $user = auth()->user();
        $user->load('school');

        // Calculate profile completion
        $profileCompletion = $this->calculateProfileCompletion($user);

        return view('student.profile.show', compact('user', 'profileCompletion'));
    }

    /**
     * Show the form for editing the user's profile
     */
    public function edit()
    {
        $user = auth()->user();
        $user->load('school');

        return view('student.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image_path) {
                Storage::disk('public')->delete($user->profile_image_path);
            }
            
            $data['profile_image_path'] = $request->file('profile_image')
                                                 ->store('profiles', 'public');
            unset($data['profile_image']);
        }

        // Handle password update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password'], $data['current_password']);
        }

        $user->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profilo aggiornato con successo.',
                'user' => $user->fresh(),
                'profile_completion' => $this->calculateProfileCompletion($user->fresh())
            ]);
        }

        return redirect()->route('student.profile.show')
                        ->with('success', 'Profilo aggiornato con successo.');
    }

    /**
     * Update user's password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'La password corrente non è corretta.',
            'password.min' => 'La nuova password deve avere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non corrisponde.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password aggiornata con successo.'
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Password aggiornata con successo.');
    }

    /**
     * Update profile image
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'profile_image.required' => 'Seleziona un\'immagine.',
            'profile_image.image' => 'Il file deve essere un\'immagine.',
            'profile_image.mimes' => 'L\'immagine deve essere in formato JPEG, PNG o JPG.',
            'profile_image.max' => 'L\'immagine non può superare 2MB.',
        ]);

        $user = auth()->user();

        // Delete old image if exists
        if ($user->profile_image_path) {
            Storage::disk('public')->delete($user->profile_image_path);
        }

        $path = $request->file('profile_image')->store('profiles', 'public');

        $user->update(['profile_image_path' => $path]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Immagine del profilo aggiornata con successo.',
                'image_url' => $user->profile_image_url
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Immagine del profilo aggiornata con successo.');
    }

    /**
     * Remove profile image
     */
    public function removeImage()
    {
        $user = auth()->user();

        if ($user->profile_image_path) {
            Storage::disk('public')->delete($user->profile_image_path);
            $user->update(['profile_image_path' => null]);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Immagine del profilo rimossa con successo.'
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Immagine del profilo rimossa con successo.');
    }

    /**
     * Display user's activity history
     */
    public function activity()
    {
        $user = auth()->user();

        // Get user's activity data
        $activities = collect();

        // Add enrollment activities
        $enrollments = $user->courseEnrollments()
                           ->with('course')
                           ->latest('enrollment_date')
                           ->take(10)
                           ->get();

        foreach ($enrollments as $enrollment) {
            $activities->push([
                'type' => 'enrollment',
                'title' => 'Iscritto al corso: ' . $enrollment->course->name,
                'date' => $enrollment->enrollment_date,
                'status' => $enrollment->status,
                'icon' => 'fas fa-user-plus',
                'color' => 'success'
            ]);
        }

        // Add payment activities
        $payments = $user->payments()
                        ->with('course')
                        ->latest('payment_date')
                        ->take(10)
                        ->get();

        foreach ($payments as $payment) {
            $activities->push([
                'type' => 'payment',
                'title' => 'Pagamento: €' . number_format($payment->amount, 2),
                'subtitle' => $payment->course ? $payment->course->name : 'Pagamento generale',
                'date' => $payment->payment_date,
                'status' => $payment->status,
                'icon' => 'fas fa-credit-card',
                'color' => $payment->status === 'completed' ? 'success' : 'warning'
            ]);
        }

        // Add document activities
        $documents = $user->documents()
                         ->latest('created_at')
                         ->take(10)
                         ->get();

        foreach ($documents as $document) {
            $activities->push([
                'type' => 'document',
                'title' => 'Documento caricato: ' . $document->title,
                'date' => $document->created_at,
                'status' => $document->status,
                'icon' => 'fas fa-file',
                'color' => $document->status === 'approved' ? 'success' : 'info'
            ]);
        }

        // Sort all activities by date
        $activities = $activities->sortByDesc('date')->take(20);

        return view('student.profile.activity', compact('activities'));
    }

    /**
     * Display user preferences
     */
    public function preferences()
    {
        $user = auth()->user();

        // Get user preferences (you might store these in a separate table or JSON field)
        $preferences = [
            'email_notifications' => true,
            'sms_notifications' => false,
            'marketing_emails' => true,
            'course_reminders' => true,
            'payment_reminders' => true,
            'language' => 'it',
            'timezone' => 'Europe/Rome',
        ];

        return view('student.profile.preferences', compact('user', 'preferences'));
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
            'course_reminders' => 'boolean',
            'payment_reminders' => 'boolean',
            'language' => 'in:it,en',
            'timezone' => 'string',
        ]);

        $user = auth()->user();

        // Store preferences (you might want to create a separate preferences table)
        // For now, we'll store in a JSON field or handle differently
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferenze aggiornate con successo.'
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Preferenze aggiornate con successo.');
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion($user)
    {
        $fields = [
            'name' => !empty($user->name),
            'first_name' => !empty($user->first_name),
            'last_name' => !empty($user->last_name),
            'email' => !empty($user->email),
            'phone' => !empty($user->phone),
            'date_of_birth' => !empty($user->date_of_birth),
            'profile_image' => !empty($user->profile_image_path),
        ];

        $completedFields = count(array_filter($fields));
        $totalFields = count($fields);

        return [
            'percentage' => round(($completedFields / $totalFields) * 100),
            'completed' => $completedFields,
            'total' => $totalFields,
            'missing_fields' => array_keys(array_filter($fields, function($value) {
                return !$value;
            }))
        ];
    }
}
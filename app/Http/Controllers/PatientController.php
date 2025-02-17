<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;



class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        $user = Auth::user();

        // Make sure user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // If user is not admin, only show their patients
        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        // Apply search if search parameter exists
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        $patients = $query->paginate(10);

        return view('patients.index', compact('patients'));
    }



    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:30|regex:/^[\p{L}\s-]+$/u',
            'last_name' => 'required|string|max:30|regex:/^[\p{L}\s-]+$/u',
            'email' => 'required|email|unique:patients',
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|string|in:male,female',
            'diagnosis' => 'required|string|max:120|regex:/^[\p{L}\s\d\-\.,!?()\'\\":]+$/u',
            'medicines' => 'nullable|array',
            'medicines.*' => 'nullable|string|max:20|regex:/^[\p{L}\s\d.-]+$/u',
            'doses' => 'nullable|array',
            'doses.*' => 'nullable|required_with:medicines.*|integer|min:0', // Validates each dose
            'notes' => 'nullable|string|max:500|regex:/^[\p{L}\s\d\-\.,!?()\'"\/;:]+$/u',
        ]);

        // Filter out empty medicines and their corresponding doses
        $medicines = array_filter($request->medicines ?? [], function ($medicine) {
            return !empty($medicine);
        });
        $doses = array_filter($request->doses ?? [], function ($dose) {
            return $dose !== null && $dose !== '';
        });

        // Combine medicines and doses into a single array
        $medicinesWithDoses = [];
        if (!empty($medicines)) {
            foreach ($medicines as $key => $medicine) {
                $medicinesWithDoses[] = [
                    'name' => $medicine,
                    'dose' => $doses[$key] ?? null
                ];
            }
        }

        // Store the combined data as JSON
        $patient = Patient::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'diagnosis' => $validated['diagnosis'],
            'medicines' => json_encode($medicinesWithDoses),
            'notes' => $validated['notes'] ?? null,
            'user_id' => $user->id
        ]);

        return redirect()->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }



    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)

    {
        Gate::authorize('view', $patient);
        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        Gate::authorize('update', $patient);
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient in storage.
     */

    public function update(Request $request, Patient $patient)
    {
        $user = Auth::user();

        Gate::authorize('update', $patient);
        $validated = $request->validate([
            'name' => 'required|string|max:30|regex:/^[\p{L}\s-]+$/u',
            'last_name' => 'required|string|max:30|regex:/^[\p{L}\s-]+$/u',
            'email' => 'required|email|unique:patients,email,' . $patient->id,
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|string|in:male,female',
            'diagnosis' => 'required|string|max:120|regex:/^[\p{L}\s\d\-\.,!?()\'\\":]+$/u',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'nullable|string|max:20|regex:/^[\p{L}\s\d.-]+$/u',
            'medicines.*.dose' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500|regex:/^[\p{L}\s\d\-\.,!?()\'"\/;:]+$/u',
        ]);

        // Handle medicines - if no medicines are provided, set to empty array
        $medicines = [];
        if ($request->has('medicines') && is_array($request->medicines)) {
            $medicines = collect($request->medicines)
                ->filter(function ($medicine) {
                    // Only include medicines that have at least a name
                    return !empty($medicine['name']);
                })
                ->map(function ($medicine) {
                    return [
                        'name' => $medicine['name'],
                        'dose' => !empty($medicine['dose']) ? $medicine['dose'] : null
                    ];
                })
                ->values()
                ->toArray();
        }

        // Update patient data
        $patient->update([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'diagnosis' => $validated['diagnosis'],
            'medicines' => $medicines, // Will be automatically JSON encoded due to cast
            'notes' => $validated['notes'] ?? null,
            'user_id' => $user->id
        ]);

        return redirect()->route('patients.index')
            ->with('success', 'Patient updated successfully.');
    }



    /**
     * Remove the specified patient from storage.
     */
    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}

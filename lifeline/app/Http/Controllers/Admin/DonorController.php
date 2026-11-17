<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin donor records (M2.6): search, filter and view. Verify / reject stay in DonorVerificationController. */
class DonorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(Donor::BLOOD_TYPES)],
            'status' => ['nullable', Rule::in(['pending', 'verified', 'rejected'])],
            'available' => ['nullable', Rule::in(['1', '0'])],
        ]);

        $donors = Donor::with('user')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->whereHas('user', fn ($users) => $users
                ->where(fn ($match) => $match
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%"))))
            ->when($filters['blood_type'] ?? null, fn ($query, $type) => $query->where('blood_type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('verification_status', $status))
            ->when(isset($filters['available']), fn ($query) => $query->where('is_available', $filters['available'] === '1'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.donors.index', [
            'donors' => $donors,
            'filters' => $filters,
            'bloodTypes' => Donor::BLOOD_TYPES,
        ]);
    }

    public function show(Donor $donor): View
    {
        $donor->load(['user', 'donations' => fn ($query) => $query->orderByDesc('donated_on')]);

        return view('admin.donors.show', [
            'donor' => $donor,
            'matches' => $donor->matches()->with('bloodRequest')->orderByDesc('id')->limit(20)->get(),
            'reviewer' => $donor->verified_by ? User::find($donor->verified_by) : null,
        ]);
    }
}

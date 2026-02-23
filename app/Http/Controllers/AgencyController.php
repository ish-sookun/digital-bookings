<?php

namespace App\Http\Controllers;

use App\CommissionType;
use App\DiscountType;
use App\Http\Requests\AgencyRequest;
use App\Models\Agency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $agencies = Agency::query()->latest()->get();

        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $commissionTypes = CommissionType::cases();
        $discountTypes = DiscountType::cases();

        return view('agencies.create', compact('commissionTypes', 'discountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AgencyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $this->storeLogo($request);
        }

        Agency::create($data);

        return redirect()->route('agencies.index')->with('success', 'Agency created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agency $agency): View
    {
        return view('agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agency $agency): View
    {
        $commissionTypes = CommissionType::cases();
        $discountTypes = DiscountType::cases();

        return view('agencies.edit', compact('agency', 'commissionTypes', 'discountTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AgencyRequest $request, Agency $agency): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            if ($agency->company_logo) {
                Storage::disk('public')->delete($agency->company_logo);
            }

            $data['company_logo'] = $this->storeLogo($request);
        }

        $agency->update($data);

        return redirect()->route('agencies.index')->with('success', 'Agency updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency): RedirectResponse
    {
        if ($agency->company_logo) {
            Storage::disk('public')->delete($agency->company_logo);
        }

        $agency->delete();

        return redirect()->route('agencies.index')->with('success', 'Agency deleted successfully.');
    }

    private function storeLogo(AgencyRequest $request): string
    {
        $file = $request->file('company_logo');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('uploads', $filename, 'public');
    }
}

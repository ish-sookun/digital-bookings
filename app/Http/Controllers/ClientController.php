<?php

namespace App\Http\Controllers;

use App\CommissionType;
use App\DiscountType;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $clients = Client::query()->latest()->get();

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $commissionTypes = CommissionType::cases();
        $discountTypes = DiscountType::cases();

        return view('clients.create', compact('commissionTypes', 'discountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $this->storeLogo($request);
        }

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): View
    {
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        $commissionTypes = CommissionType::cases();
        $discountTypes = DiscountType::cases();

        return view('clients.edit', compact('client', 'commissionTypes', 'discountTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            if ($client->company_logo) {
                Storage::disk('public')->delete($client->company_logo);
            }

            $data['company_logo'] = $this->storeLogo($request);
        }

        $client->update($data);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->company_logo) {
            Storage::disk('public')->delete($client->company_logo);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    private function storeLogo(ClientRequest $request): string
    {
        $file = $request->file('company_logo');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('uploads', $filename, 'public');
    }
}

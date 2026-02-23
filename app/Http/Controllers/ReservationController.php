<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Agency;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $reservations = Reservation::query()
            ->with(['client', 'agency', 'platform', 'placement', 'salesperson'])
            ->latest()
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clients = Client::query()->orderBy('company_name')->get();
        $agencies = Agency::query()->orderBy('company_name')->get();
        $platforms = Platform::query()->orderBy('name')->get();
        $placements = Placement::query()->orderBy('name')->get();
        $salespeople = Salesperson::query()->orderBy('first_name')->orderBy('last_name')->get();
        $channels = ['Run of site', 'Home & multimedia'];
        $scopes = ['Mauritius only', 'Worldwide'];

        $placementsJson = $placements->map(fn (Placement $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'platform_id' => $p->platform_id,
            'price' => $p->price,
        ]);
        $clientsJson = $clients->map(fn (Client $c) => [
            'id' => $c->id,
            'discount' => $c->discount,
            'discount_type' => $c->discount_type?->value,
            'vat_number' => $c->vat_number,
            'vat_exempt' => $c->vat_exempt,
        ]);
        $agenciesJson = $agencies->map(fn (Agency $a) => [
            'id' => $a->id,
            'discount' => $a->discount,
            'discount_type' => $a->discount_type?->value,
            'commission_amount' => $a->commission_amount,
            'commission_type' => $a->commission_type?->value,
        ]);

        return view('reservations.create', compact('clients', 'agencies', 'platforms', 'placements', 'salespeople', 'channels', 'scopes', 'placementsJson', 'clientsJson', 'agenciesJson'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['dates_booked'] = json_decode($data['dates_booked'], true);

        Reservation::create($data);

        return redirect()->route('reservations.index')->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation): View
    {
        $reservation->load(['client', 'agency', 'platform', 'placement', 'salesperson']);

        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation): View
    {
        $clients = Client::query()->orderBy('company_name')->get();
        $agencies = Agency::query()->orderBy('company_name')->get();
        $platforms = Platform::query()->orderBy('name')->get();
        $placements = Placement::query()->orderBy('name')->get();
        $salespeople = Salesperson::query()->orderBy('first_name')->orderBy('last_name')->get();
        $channels = ['Run of site', 'Home & multimedia'];
        $scopes = ['Mauritius only', 'Worldwide'];

        $placementsJson = $placements->map(fn (Placement $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'platform_id' => $p->platform_id,
            'price' => $p->price,
        ]);
        $clientsJson = $clients->map(fn (Client $c) => [
            'id' => $c->id,
            'discount' => $c->discount,
            'discount_type' => $c->discount_type?->value,
            'vat_number' => $c->vat_number,
            'vat_exempt' => $c->vat_exempt,
        ]);
        $agenciesJson = $agencies->map(fn (Agency $a) => [
            'id' => $a->id,
            'discount' => $a->discount,
            'discount_type' => $a->discount_type?->value,
            'commission_amount' => $a->commission_amount,
            'commission_type' => $a->commission_type?->value,
        ]);

        return view('reservations.edit', compact('reservation', 'clients', 'agencies', 'platforms', 'placements', 'salespeople', 'channels', 'scopes', 'placementsJson', 'clientsJson', 'agenciesJson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validated();
        $data['dates_booked'] = json_decode($data['dates_booked'], true);

        $reservation->update($data);

        return redirect()->route('reservations.index')->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Booking deleted successfully.');
    }
}

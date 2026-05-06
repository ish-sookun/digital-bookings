<?php

namespace App\Http\Controllers;

use App\ForeignCurrency;
use App\Http\Requests\ReservationRequest;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\ReservationStatus;
use App\ReservationType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $reservations = Reservation::query()
            ->with(['client', 'representedClient', 'platform', 'placement', 'salesperson'])
            ->latest()
            ->paginate(20);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clients = Client::query()->orderBy('company_name')->get();
        $platforms = Platform::query()->orderBy('name')->get();
        $placements = Placement::query()->orderBy('name')->get();
        $salespeople = Salesperson::query()->orderBy('first_name')->orderBy('last_name')->get();
        $channels = ['Run of site', 'Home & multimedia'];
        $scopes = ['Mauritius only', 'Worldwide'];
        $statuses = ReservationStatus::cases();
        $reservationTypes = ReservationType::cases();
        $foreignCurrencies = ForeignCurrency::cases();

        $placementsJson = $placements->map(fn (Placement $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'platform_id' => $p->platform_id,
            'price' => $p->price,
            'type' => $p->type->value,
        ]);
        $clientsJson = $clients->map(fn (Client $c) => [
            'id' => $c->id,
            'company_name' => $c->company_name,
            'discount' => $c->discount,
            'discount_type' => $c->discount_type?->value,
            'commission_amount' => $c->commission_amount,
            'commission_type' => $c->commission_type?->value,
            'vat_number' => $c->vat_number,
            'vat_exempt' => $c->vat_exempt,
        ]);

        $linkableReservations = Reservation::query()
            ->with('placement')
            ->latest()
            ->limit(100)
            ->get(['id', 'product', 'placement_id', 'client_id']);
        $linkableReservationsJson = $linkableReservations->map(fn (Reservation $r) => [
            'id' => $r->id,
            'label' => $r->id.' — '.($r->product ?? ''),
        ]);

        return view('reservations.create', compact('clients', 'platforms', 'placements', 'salespeople', 'channels', 'scopes', 'statuses', 'reservationTypes', 'foreignCurrencies', 'placementsJson', 'clientsJson', 'linkableReservationsJson'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['dates_booked'] = json_decode($data['dates_booked'], true);
        $data = $this->handleDocumentUploads($request, $data);

        $reservationDate = $data['reservation_date'] ?? null;
        unset($data['reservation_date']);

        $reservation = Reservation::create($data);

        if ($reservationDate) {
            $reservation->created_at = Carbon::parse($reservationDate)->setTimeFrom($reservation->created_at);
            $reservation->saveQuietly();
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation): View
    {
        $reservation->load(['client', 'representedClient', 'platform', 'placement', 'salesperson', 'parent']);

        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation): View
    {
        $clients = Client::query()->orderBy('company_name')->get();
        $platforms = Platform::query()->orderBy('name')->get();
        $placements = Placement::query()->orderBy('name')->get();
        $salespeople = Salesperson::query()->orderBy('first_name')->orderBy('last_name')->get();
        $channels = ['Run of site', 'Home & multimedia'];
        $scopes = ['Mauritius only', 'Worldwide'];
        $statuses = ReservationStatus::cases();
        $reservationTypes = ReservationType::cases();
        $foreignCurrencies = ForeignCurrency::cases();

        $placementsJson = $placements->map(fn (Placement $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'platform_id' => $p->platform_id,
            'price' => $p->price,
            'type' => $p->type->value,
        ]);
        $clientsJson = $clients->map(fn (Client $c) => [
            'id' => $c->id,
            'company_name' => $c->company_name,
            'discount' => $c->discount,
            'discount_type' => $c->discount_type?->value,
            'commission_amount' => $c->commission_amount,
            'commission_type' => $c->commission_type?->value,
            'vat_number' => $c->vat_number,
            'vat_exempt' => $c->vat_exempt,
        ]);

        $linkableReservations = Reservation::query()
            ->with('placement')
            ->where('id', '!=', $reservation->id)
            ->latest()
            ->limit(100)
            ->get(['id', 'product', 'placement_id', 'client_id']);
        $linkableReservationsJson = $linkableReservations->map(fn (Reservation $r) => [
            'id' => $r->id,
            'label' => $r->id.' — '.($r->product ?? ''),
        ]);

        return view('reservations.edit', compact('reservation', 'clients', 'platforms', 'placements', 'salespeople', 'channels', 'scopes', 'statuses', 'reservationTypes', 'foreignCurrencies', 'placementsJson', 'clientsJson', 'linkableReservationsJson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validated();
        $data['dates_booked'] = json_decode($data['dates_booked'], true);
        $data = $this->handleDocumentUploads($request, $data, $reservation);

        $reservationDate = $data['reservation_date'] ?? null;
        unset($data['reservation_date']);

        $reservation->update($data);

        if ($reservationDate) {
            $reservation->created_at = Carbon::parse($reservationDate)->setTimeFrom($reservation->created_at);
            $reservation->saveQuietly();
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    /**
     * Download a PDF reservation order for the specified reservation.
     */
    public function downloadPdf(Reservation $reservation): Response
    {
        $reservation->load(['client', 'representedClient', 'platform', 'placement', 'salesperson', 'parent']);

        $logoPath = public_path('lsl-blue-2x.png');

        $pdf = Pdf::loadView('reservations.pdf', compact('reservation', 'logoPath'))
            ->setPaper('a4', 'portrait');

        $filename = 'RO-'.str_replace(' ', '-', $reservation->product).'-'.$reservation->created_at->format('d.m.Y').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download a document attached to the reservation.
     */
    public function downloadDocument(Reservation $reservation, string $type): StreamedResponse
    {
        $pathField = match ($type) {
            'purchase-order' => 'purchase_order_path',
            'invoice' => 'invoice_path',
            'signed-ro' => 'signed_ro_path',
            default => abort(404),
        };

        $path = $reservation->{$pathField};

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }

    /**
     * Handle async document upload via AJAX.
     */
    public function uploadDocument(Request $request, Reservation $reservation): JsonResponse
    {
        $typeMap = [
            'signed_ro' => ['field' => 'signed_ro_path', 'download_type' => 'signed-ro'],
            'purchase_order' => ['field' => 'purchase_order_path', 'download_type' => 'purchase-order'],
            'invoice' => ['field' => 'invoice_path', 'download_type' => 'invoice'],
        ];

        $type = $request->input('type');

        if (! isset($typeMap[$type])) {
            return response()->json(['error' => 'Invalid document type.'], 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $pathColumn = $typeMap[$type]['field'];

        if ($reservation->{$pathColumn}) {
            Storage::disk('local')->delete($reservation->{$pathColumn});
        }

        $directory = 'documents/'.now()->format('Y').'/'.now()->format('m');
        $reservation->{$pathColumn} = $request->file('file')->store($directory, 'local');
        $reservation->save();

        return response()->json([
            'success' => true,
            'download_url' => route('reservations.document', [$reservation, $typeMap[$type]['download_type']]),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Reservation deleted successfully.');
    }

    private function handleDocumentUploads(ReservationRequest $request, array $data, ?Reservation $reservation = null): array
    {
        $directory = 'documents/'.now()->format('Y').'/'.now()->format('m');

        $fileMap = [
            'purchase_order_file' => 'purchase_order_path',
            'invoice_file' => 'invoice_path',
            'signed_ro_file' => 'signed_ro_path',
        ];

        foreach ($fileMap as $inputName => $pathColumn) {
            if ($request->hasFile($inputName)) {
                if ($reservation && $reservation->{$pathColumn}) {
                    Storage::disk('local')->delete($reservation->{$pathColumn});
                }
                $data[$pathColumn] = $request->file($inputName)->store($directory, 'local');
            }
            unset($data[$inputName]);
        }

        return $data;
    }
}

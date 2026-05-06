<?php

namespace App\Http\Controllers;

use App\Http\Requests\SageExportRequest;
use App\Models\Reservation;
use App\ReservationStatus;
use App\Services\SageCsvBuilder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SageExportController extends Controller
{
    /**
     * Stream a CSV for the given date range and export type.
     *
     * - export_type=sage  → Confirmed reservations where is_cash = false (the
     *   classic SAGE accounting export). Streams a `;`-delimited CSV.
     * - export_type=sales → not yet implemented; redirects back with a flash
     *   notice. The format will be defined separately.
     */
    public function __invoke(SageExportRequest $request): StreamedResponse|RedirectResponse
    {
        $start = Carbon::parse($request->validated('start_date'))->startOfDay();
        $end = Carbon::parse($request->validated('end_date'))->endOfDay();
        $exportType = $request->validated('export_type');

        if ($exportType === 'sales') {
            return redirect()
                ->route('reservations.index')
                ->with('error', 'Sales export is not yet available — the format is pending.');
        }

        $reservations = Reservation::query()
            ->with(['client', 'placement.platform', 'salesperson', 'representedClient', 'platform'])
            ->where('status', ReservationStatus::Confirmed)
            ->where('is_cash', false)
            ->get();

        $builder = new SageCsvBuilder($start, $end);
        $rows = $builder->build($reservations);

        $filename = sprintf(
            'sage-export-%s-%s.csv',
            $start->format('Ymd'),
            $end->format('Ymd'),
        );

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';', '"', '\\');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

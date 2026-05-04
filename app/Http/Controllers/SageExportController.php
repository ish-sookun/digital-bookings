<?php

namespace App\Http\Controllers;

use App\Http\Requests\SageExportRequest;
use App\Models\Reservation;
use App\ReservationStatus;
use App\Services\SageCsvBuilder;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SageExportController extends Controller
{
    /**
     * Stream a SAGE-formatted CSV for the given date range and payment mode.
     *
     * The selection between credit and cash filters reservations on their
     * `is_cash` flag — credit mode exports `is_cash = false` reservations,
     * cash mode exports `is_cash = true`. The CSV format is the same for
     * both modes.
     */
    public function __invoke(SageExportRequest $request): StreamedResponse
    {
        $start = Carbon::parse($request->validated('start_date'))->startOfDay();
        $end = Carbon::parse($request->validated('end_date'))->endOfDay();
        $paymentMode = $request->validated('payment_mode');
        $isCash = $paymentMode === 'cash';

        $reservations = Reservation::query()
            ->with(['client', 'placement.platform', 'salesperson', 'representedClient', 'platform'])
            ->where('status', ReservationStatus::Confirmed)
            ->where('is_cash', $isCash)
            ->get();

        $builder = new SageCsvBuilder($start, $end);
        $rows = $builder->build($reservations);

        $filename = sprintf(
            'sage-export-%s-%s-%s.csv',
            $paymentMode,
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

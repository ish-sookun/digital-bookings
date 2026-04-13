<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesPerformanceController extends Controller
{
    public function export(Request $request): Response
    {
        $request->validate([
            'platform_id' => ['required', 'exists:platforms,id'],
            'format' => ['required', 'in:csv,pdf'],
        ]);

        $platform = Platform::findOrFail($request->integer('platform_id'));
        $now = Carbon::now();
        $financialYearStart = Budget::financialYearStartYear($now);
        $fyStart = Carbon::create($financialYearStart, Budget::FINANCIAL_YEAR_START_MONTH, 1)->startOfDay();
        $fyEnd = $fyStart->copy()->addYear()->subDay()->endOfDay();
        $financialYearLabel = Budget::financialYearLabel($financialYearStart);

        $data = $this->buildReport($platform, $financialYearStart, $fyStart, $fyEnd);

        if ($request->input('format') === 'csv') {
            return $this->exportCsv($data, $platform, $financialYearLabel);
        }

        return $this->exportPdf($data, $platform, $financialYearLabel, $now);
    }

    /**
     * @return array{months: list<array{label: string}>, salespersons: list<mixed>}
     */
    private function buildReport(Platform $platform, int $financialYearStart, Carbon $fyStart, Carbon $fyEnd): array
    {
        $fyMonths = Budget::financialYearMonths($financialYearStart);

        $budgets = Budget::forFinancialYear($financialYearStart)
            ->where('platform_id', $platform->id)
            ->with('salespersonTargets')
            ->get();

        $targetsByKey = [];
        foreach ($budgets as $budget) {
            foreach ($budget->salespersonTargets as $st) {
                $targetsByKey[$st->salesperson_id.'-'.$budget->year.'-'.$budget->month] = (float) $st->amount;
            }
        }

        $monthlySales = Reservation::query()
            ->where('platform_id', $platform->id)
            ->whereBetween('created_at', [$fyStart, $fyEnd])
            ->selectRaw("salesperson_id, strftime('%Y', created_at) as y, strftime('%m', created_at) as m, SUM(gross_amount) as total, COUNT(*) as cnt")
            ->groupBy('salesperson_id', 'y', 'm')
            ->get();

        $salesByKey = [];
        $countByKey = [];
        foreach ($monthlySales as $row) {
            $key = $row->salesperson_id.'-'.((int) $row->y).'-'.((int) $row->m);
            $salesByKey[$key] = (float) $row->total;
            $countByKey[$key] = (int) $row->cnt;
        }

        $salespeople = Salesperson::query()->orderBy('first_name')->get();

        $salespersons = $salespeople->map(function (Salesperson $salesperson) use ($fyMonths, $targetsByKey, $salesByKey, $countByKey) {
            $totalTarget = 0;
            $totalSales = 0;
            $totalReservations = 0;
            $months = [];

            foreach ($fyMonths as $m) {
                $key = $salesperson->id.'-'.$m['year'].'-'.$m['month'];
                $target = $targetsByKey[$key] ?? 0;
                $sales = $salesByKey[$key] ?? 0;
                $reservations = $countByKey[$key] ?? 0;

                $totalTarget += $target;
                $totalSales += $sales;
                $totalReservations += $reservations;

                $months[] = [
                    'target' => $target,
                    'sales' => $sales,
                    'reservations' => $reservations,
                ];
            }

            $percentage = $totalTarget > 0 ? ($totalSales / $totalTarget) * 100 : 0;

            return [
                'salesperson' => $salesperson,
                'months' => $months,
                'totals' => [
                    'target' => $totalTarget,
                    'sales' => $totalSales,
                    'reservations' => $totalReservations,
                    'percentage' => $percentage,
                ],
            ];
        })->all();

        $monthLabels = array_map(
            fn ($m) => ['label' => Carbon::create($m['year'], $m['month'], 1)->format('M Y')],
            $fyMonths,
        );

        return [
            'months' => $monthLabels,
            'salespersons' => $salespersons,
        ];
    }

    private function exportCsv(array $data, Platform $platform, string $financialYearLabel): StreamedResponse
    {
        $filename = 'sales-performance-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data, $platform, $financialYearLabel) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Sales Performance Report']);
            fputcsv($handle, ['Platform: '.$platform->name, 'Financial Year: '.$financialYearLabel, 'Date: '.now()->format('d/m/Y')]);

            foreach ($data['salespersons'] as $entry) {
                fputcsv($handle, []);
                fputcsv($handle, [$entry['salesperson']->first_name.' '.$entry['salesperson']->last_name]);
                fputcsv($handle, ['Month', 'Target (MUR)', 'Sales (MUR)', 'Reservations']);

                foreach ($data['months'] as $i => $month) {
                    fputcsv($handle, [
                        $month['label'],
                        number_format($entry['months'][$i]['target'], 2, '.', ''),
                        number_format($entry['months'][$i]['sales'], 2, '.', ''),
                        $entry['months'][$i]['reservations'],
                    ]);
                }

                fputcsv($handle, [
                    'FY Total',
                    number_format($entry['totals']['target'], 2, '.', ''),
                    number_format($entry['totals']['sales'], 2, '.', ''),
                    $entry['totals']['reservations'],
                ]);
                fputcsv($handle, [
                    'Achievement: '.number_format($entry['totals']['percentage'], 1).'%',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportPdf(array $data, Platform $platform, string $financialYearLabel, Carbon $now): Response
    {
        $filename = 'sales-performance-report-'.$now->format('Y-m-d').'.pdf';

        $logoPath = public_path('images/logo.png');

        return Pdf::loadView('reports.sales-performance', compact(
            'data',
            'platform',
            'financialYearLabel',
            'now',
            'logoPath',
        ))
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}

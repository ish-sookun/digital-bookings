<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Platform;
use App\Models\Salesperson;
use App\Models\SalespersonTarget;
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
     * @return list<array{salesperson: Salesperson, target: float, sales: float, percentage: float}>
     */
    private function buildReport(Platform $platform, int $financialYearStart, Carbon $fyStart, Carbon $fyEnd): array
    {
        $budgetIds = Budget::forFinancialYear($financialYearStart)
            ->where('platform_id', $platform->id)
            ->pluck('id');

        $targets = SalespersonTarget::query()
            ->whereIn('budget_id', $budgetIds)
            ->selectRaw('salesperson_id, SUM(amount) as total_target')
            ->groupBy('salesperson_id')
            ->pluck('total_target', 'salesperson_id');

        $salespeople = Salesperson::query()
            ->withSum(['reservations as sales_total' => function ($query) use ($platform, $fyStart, $fyEnd) {
                $query->where('platform_id', $platform->id)
                    ->whereBetween('created_at', [$fyStart, $fyEnd]);
            }], 'gross_amount')
            ->orderByDesc('sales_total')
            ->get();

        return $salespeople->map(function (Salesperson $salesperson) use ($targets) {
            $target = (float) ($targets[$salesperson->id] ?? 0);
            $sales = (float) $salesperson->sales_total;
            $percentage = $target > 0 ? ($sales / $target) * 100 : 0;

            return [
                'salesperson' => $salesperson,
                'target' => $target,
                'sales' => $sales,
                'percentage' => $percentage,
            ];
        })->all();
    }

    private function exportCsv(array $data, Platform $platform, string $financialYearLabel): StreamedResponse
    {
        $filename = 'sales-performance-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data, $platform, $financialYearLabel) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Sales Performance Report']);
            fputcsv($handle, ['Platform: '.$platform->name, 'Financial Year: '.$financialYearLabel, 'Date: '.now()->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Salesperson', 'Target (MUR)', 'Sales (MUR)', 'Achievement (%)']);

            foreach ($data as $entry) {
                fputcsv($handle, [
                    $entry['salesperson']->first_name.' '.$entry['salesperson']->last_name,
                    number_format($entry['target'], 2, '.', ''),
                    number_format($entry['sales'], 2, '.', ''),
                    number_format($entry['percentage'], 1, '.', ''),
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
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $platformId = $request->filled('platform_id') ? (int) $request->input('platform_id') : null;
        $placementId = $request->filled('placement_id') ? (int) $request->input('placement_id') : null;

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Get all reservations that have dates in this month
        $reservations = Reservation::query()
            ->with(['client', 'placement'])
            ->when($platformId, fn ($query, $platformId) => $query->where('platform_id', $platformId))
            ->when($placementId, fn ($query, $placementId) => $query->where('placement_id', $placementId))
            ->get()
            ->filter(function ($reservation) use ($startOfMonth, $endOfMonth) {
                foreach ($reservation->dates_booked as $date) {
                    $bookingDate = Carbon::parse($date);
                    if ($bookingDate->between($startOfMonth, $endOfMonth)) {
                        return true;
                    }
                }

                return false;
            });

        // Build a map of dates to reservations
        $bookingsByDate = [];
        foreach ($reservations as $reservation) {
            foreach ($reservation->dates_booked as $date) {
                $bookingDate = Carbon::parse($date);
                if ($bookingDate->between($startOfMonth, $endOfMonth)) {
                    $dateKey = $bookingDate->format('Y-m-d');
                    if (! isset($bookingsByDate[$dateKey])) {
                        $bookingsByDate[$dateKey] = [];
                    }
                    $bookingsByDate[$dateKey][] = $reservation;
                }
            }
        }

        // Calculate calendar grid
        $firstDayOfWeek = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $lastDayOfWeek = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $currentWeekStart = $firstDayOfWeek->copy();

        while ($currentWeekStart <= $lastDayOfWeek) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $currentWeekStart->copy()->addDays($i);
                $dateKey = $day->format('Y-m-d');
                $week[] = [
                    'date' => $day,
                    'isCurrentMonth' => $day->month === (int) $month,
                    'isToday' => $day->isToday(),
                    'bookings' => $bookingsByDate[$dateKey] ?? [],
                ];
            }
            $weeks[] = $week;
            $currentWeekStart->addWeek();
        }

        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $platforms = Platform::query()->orderBy('id')->get();

        $placements = Placement::query()
            ->when($platformId, fn ($query, $platformId) => $query->where('platform_id', $platformId))
            ->orderBy('name')
            ->get();

        // Reset selected placement if it no longer belongs to the selected platform.
        if ($placementId !== null && $placements->firstWhere('id', $placementId) === null) {
            $placementId = null;
        }

        return view('calendar.index', compact(
            'currentDate',
            'weeks',
            'prevMonth',
            'nextMonth',
            'platforms',
            'placements',
            'platformId',
            'placementId',
        ));
    }
}

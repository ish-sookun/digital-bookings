<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\PlacementType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Platforms (in display order) that the day-detail modal categorizes bookings by.
     *
     * @var list<string>
     */
    private const MODAL_PLATFORMS = ['lexpress.mu', '5plus.mu'];

    public function index(Request $request): View
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $platformId = $request->filled('platform_id') ? (int) $request->input('platform_id') : null;
        $placementId = $request->filled('placement_id') ? (int) $request->input('placement_id') : null;

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Get all reservations that have dates in this month (excluding programmatic placements)
        $reservations = Reservation::query()
            ->with(['client', 'placement', 'platform'])
            ->whereHas('placement', fn ($query) => $query->where('type', '!=', PlacementType::Programmatic))
            ->when($platformId, fn ($query, $platformId) => $query->where('platform_id', $platformId))
            ->when($placementId, fn ($query, $placementId) => $query->where('placement_id', $placementId))
            ->get()
            ->filter(function ($reservation) use ($startOfMonth, $endOfMonth) {
                foreach ($reservation->dates_booked as $date) {
                    $reservationDate = Carbon::parse($date);
                    if ($reservationDate->between($startOfMonth, $endOfMonth)) {
                        return true;
                    }
                }

                return false;
            });

        // Build a map of dates to reservations
        $reservationsByDate = [];
        foreach ($reservations as $reservation) {
            foreach ($reservation->dates_booked as $date) {
                $reservationDate = Carbon::parse($date);
                if ($reservationDate->between($startOfMonth, $endOfMonth)) {
                    $dateKey = $reservationDate->format('Y-m-d');
                    if (! isset($reservationsByDate[$dateKey])) {
                        $reservationsByDate[$dateKey] = [];
                    }
                    $reservationsByDate[$dateKey][] = $reservation;
                }
            }
        }

        // Build per-day grouped bookings (platform → placement type) for the day modal
        $bookingsByDate = [];
        foreach ($reservationsByDate as $dateKey => $dateReservations) {
            $bookingsByDate[$dateKey] = $this->groupReservationsForModal($dateReservations);
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
                    'reservations' => $reservationsByDate[$dateKey] ?? [],
                ];
            }
            $weeks[] = $week;
            $currentWeekStart->addWeek();
        }

        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $platforms = Platform::query()->orderBy('id')->get();

        $placements = Placement::query()
            ->where('type', '!=', PlacementType::Programmatic)
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
            'bookingsByDate',
        ));
    }

    /**
     * Group a day's reservations into the structure consumed by the calendar day modal.
     *
     * The output always contains a section for each platform in {@see self::MODAL_PLATFORMS}
     * (filtered to the active platform filter when set), and within each section a Web and a
     * Social Media group, so the modal layout stays consistent across days.
     *
     * @param  list<Reservation>  $reservations
     * @return list<array{name: string, groups: list<array{type: string, reservations: list<array<string, mixed>>}>}>
     */
    private function groupReservationsForModal(array $reservations): array
    {
        $sections = [];

        foreach (self::MODAL_PLATFORMS as $platformName) {
            $platformReservations = array_filter(
                $reservations,
                fn (Reservation $reservation) => $reservation->platform?->name === $platformName,
            );

            $sections[] = [
                'name' => $platformName,
                'groups' => [
                    [
                        'type' => PlacementType::Web->label(),
                        'reservations' => $this->serializeReservations($platformReservations, PlacementType::Web),
                    ],
                    [
                        'type' => PlacementType::SocialMedia->label(),
                        'reservations' => $this->serializeReservations($platformReservations, PlacementType::SocialMedia),
                    ],
                ],
            ];
        }

        return $sections;
    }

    /**
     * @param  iterable<Reservation>  $reservations
     * @return list<array<string, mixed>>
     */
    private function serializeReservations(iterable $reservations, PlacementType $type): array
    {
        $serialized = [];

        foreach ($reservations as $reservation) {
            if ($reservation->placement?->type !== $type) {
                continue;
            }

            $serialized[] = [
                'id' => $reservation->id,
                'product' => $reservation->product,
                'client' => $reservation->client?->company_name,
                'placement' => $reservation->placement?->name,
                'status_label' => $reservation->status->label(),
                'status_dot_class' => $reservation->status->dotClasses(),
                'url' => route('reservations.show', $reservation),
            ];
        }

        return $serialized;
    }
}

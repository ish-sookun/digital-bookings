<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    private const ALLOWED_TYPES = ['reservation', 'client'];

    private const PER_PAGE = 20;

    /**
     * Display paginated search results for reservations or clients.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'reservation');

        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'reservation';
        }

        $results = $query === ''
            ? null
            : $this->search($type, $query);

        return view('search.index', compact('query', 'type', 'results'));
    }

    /**
     * Run the paginated query for the given search type.
     */
    private function search(string $type, string $query): LengthAwarePaginator
    {
        $like = '%'.$query.'%';

        return match ($type) {
            'reservation' => Reservation::query()
                ->with(['client', 'representedClient', 'platform', 'placement'])
                ->whereRaw('CAST(id AS TEXT) LIKE ?', [$like])
                ->latest()
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'client' => Client::query()
                ->where('company_name', 'like', $like)
                ->orderBy('company_name')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
        };
    }
}

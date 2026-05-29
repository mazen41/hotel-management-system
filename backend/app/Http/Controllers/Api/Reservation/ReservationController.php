<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\SearchReservationRequest;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\Reservations\ReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservations)
    {
    }

    /**
     * GET /api/reservations
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::query()->with(['guest', 'room.roomType', 'roomType']);

        $this->applyFilters($query, $request);

        $sortField = $request->get('sort', 'check_in_date');
        $sortDirection = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['reservation_number', 'check_in_date', 'check_out_date', 'created_at', 'status', 'payment_status', 'source'];

        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'check_in_date';
        }

        $reservations = $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('reservation_number')
            ->paginate((int) $request->get('per_page', 15))
            ->withQueryString();

        return response()->json([
            'data' => ReservationResource::collection($reservations->items()),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'from' => $reservations->firstItem(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'to' => $reservations->lastItem(),
                'total' => $reservations->total(),
            ],
            'links' => [
                'first' => $reservations->url(1),
                'last' => $reservations->url($reservations->lastPage()),
                'prev' => $reservations->previousPageUrl(),
                'next' => $reservations->nextPageUrl(),
            ],
        ]);
    }

    /**
     * POST /api/reservations
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->reservations->create($request->validated());

        return response()->json([
            'message' => 'Reservation created successfully.',
            'data' => new ReservationResource($reservation),
        ], 201);
    }

    /**
     * GET /api/reservations/{id}
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $reservation->load(['guest', 'room.roomType', 'roomType']);

        return response()->json([
            'data' => new ReservationResource($reservation),
        ]);
    }

    /**
     * PUT /api/reservations/{id}
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $reservation = $this->reservations->update($reservation, $request->validated());

        return response()->json([
            'message' => 'Reservation updated successfully.',
            'data' => new ReservationResource($reservation),
        ]);
    }

    /**
     * DELETE /api/reservations/{id}
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        if ($reservation->status === 'checked_in') {
            return response()->json([
                'message' => 'Checked-in reservations cannot be deleted. Update the reservation status first.',
            ], 409);
        }

        $guestId = $reservation->guest_id;
        $reservation->delete();

        return response()->json([
            'message' => 'Reservation deleted successfully.',
            'guest_id' => $guestId,
        ]);
    }

    /**
     * GET /api/reservations/search?q=
     */
    public function search(SearchReservationRequest $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->with(['guest', 'room.roomType', 'roomType'])
            ->search($request->validated('q'))
            ->orderBy('check_in_date')
            ->limit((int) $request->get('per_page', 20))
            ->get();

        return response()->json([
            'data' => ReservationResource::collection($reservations),
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->search($request->get('search', $request->get('q')));

        foreach (['status', 'payment_status', 'source', 'guest_id', 'room_id', 'room_type_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->get($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->where('check_in_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('check_out_date', '<=', $request->date_to);
        }
    }
}

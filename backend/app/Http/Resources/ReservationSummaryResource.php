<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'confirmation_number' => $this->confirmation_number,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'source' => $this->source,
            'total_amount' => $this->total_amount,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'name' => $this->customer->name ?? null, 
            'email' => $this->customer->email ?? null, 
            'total_seat' => $this->total_seat,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'travel' => [
                'date' => $this->schedule->date,
                'time' => $this->schedule->time,
                'from' => $this->schedule->route->fromLocation->name,
                'to' => $this->schedule->route->toLocation->name,
                'travel_name' => $this->schedule->travel->name,
            ],

        ];
    }
}

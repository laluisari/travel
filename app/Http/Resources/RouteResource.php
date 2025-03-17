<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
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
            'from_location_id' => $this->from_location_id,
            'to_location_id' => $this->to_location_id,
            'from_name' => $this->fromLocation->name ?? null, // Nama lokasi asal
            'to_name' => $this->toLocation->name ?? null,     // Nama lokasi tujuan
        ];
    }
}

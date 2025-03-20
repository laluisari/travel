<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'seats' => $this->seats->map(function ($seat){
                return [
                    'seat_number' => $seat->seat_number,
                    'price' => $seat->pivot->price,
                    'status' => $seat->pivot->status,                                        
                ];
            }),
        ];
    }
}

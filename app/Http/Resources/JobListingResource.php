<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'company' => $this->company,
            'location' => $this->location,
            'salary' => $this->salary,
            'category' => $this->category,
            'created_at' => $this->created_at,
            'user' => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
            ]),
        ];
    }
}

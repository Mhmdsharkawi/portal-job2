<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ApplicationResource extends JsonResource
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
            'job_id' => $this->job_id,
            'user_id' => $this->user_id,
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'resume_url' => $this->resume_path ? Storage::url($this->resume_path) : null,
            'created_at' => $this->created_at,
        ];
    }
}

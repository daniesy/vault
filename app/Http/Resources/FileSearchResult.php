<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileSearchResult extends JsonResource
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
            'name' => $this->name,
            'parents' => array_reverse([['name' => $this->folder?->name, 'id' => $this->folder?->id], ...$this->folder?->parents]),
            'path' => asset(Storage::url($this->path)),
            'type' => $this->type,
            'size' => $this->size,
            'created_at' => $this->created_at,
        ];
    }
}

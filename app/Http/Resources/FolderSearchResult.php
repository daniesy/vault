<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class FolderSearchResult extends JsonResource
{

    static Collection $folders;

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
            'type' => $this->type,
            'parents' => array_reverse([['name' => $this->name, 'id' => $this->id], ...$this->parents]),
            'icon' => $this->type,
            'created_at' => $this->created_at,
        ];
    }




}

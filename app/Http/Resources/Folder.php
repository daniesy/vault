<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Folder extends JsonResource
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
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'parents' => $this->generateParents($this->parent_ids),
            'children' => Folder::collection($this->whenLoaded('children')),
            'files' => File::collection($this->whenLoaded('files')),
        ];
    }

    private function generateParents(array $ids): array
    {
        $parents = \App\Models\Folder::whereIn('id', $ids)
            ->get()
            ->map(fn (\App\Models\Folder $folder) =>
                [
                'id' => $folder->id,
                'name' => $folder->name,
                ]
            )
            ->toArray();

        return [
            ...$parents,
            ['id' => $this->id, 'name' => $this->name]
        ];
    }
}

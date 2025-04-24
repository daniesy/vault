<?php

namespace App\Observers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Collection;

class FolderObserver
{
    /**
     * Handle the Folder "creating" event.
     */
    public function creating(Folder $folder): void
    {
        if (!$folder->parent_id) {
            $folder->parent_ids = [];
        } else {
            $parent = Folder::find($folder->parent_id);
            $folder->parent_ids = collect([$folder->parent_id, ...($parent->parent_ids ?? [])])->unique()->values();
        }
    }

    /**
     * Handle the Folder "updated" event.
     */
    public function updated(Folder $folder): void
    {
        //
    }

    /**
     * Handle the Folder "deleting" event.
     *
     * @param Folder $folder The folder being deleted.
     *
     * @return void
     */
    public function deleting(Folder $folder): void
    {
        /** @var Collection $children */
        $children = Folder::whereJsonContains('parent_ids', $folder->id)->get();

        $folder_ids = [$folder->id, ...$children->pluck('id')->toArray()];
        $files = File::whereIn('folder_id', $folder_ids)->get();
        $files->each(fn (File $f) => $f->delete());

        $children->each(fn (Folder $f) => $f->deleteQuietly());
    }

    /**
     * Handle the Folder "restored" event.
     */
    public function restored(Folder $folder): void
    {
        //
    }

    /**
     * Handle the Folder "force deleted" event.
     */
    public function forceDeleted(Folder $folder): void
    {
        //
    }
}

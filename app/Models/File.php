<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Folder $folder
 * @property int $user_id
 */
class File extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['name', 'folder_id', 'path', 'type', 'size'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function deleteFile()
    {
    }

}

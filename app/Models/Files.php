<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    public function fileable(): MorphTo
    {
        // 'fileable' matches the argument passed to $table->morphs('fileable')
        return $this->morphTo();
    }
}

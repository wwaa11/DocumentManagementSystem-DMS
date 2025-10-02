<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    public function fileable()
    {
        // 'fileable' must match the prefix used in the files table migration
        return $this->morphTo();
    }
}

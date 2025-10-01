<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    public function approvable(): MorphTo
    {
        // 'approvable' matches the argument passed to $table->morphs('approvable')
        return $this->morphTo();
    }
}

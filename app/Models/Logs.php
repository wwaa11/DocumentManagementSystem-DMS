<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'activity_logs';

    public function logable(): MorphTo
    {
        // 'logable' matches the argument passed to $table->morphs('logable')
        return $this->morphTo();
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'activity_logs';

    public function loggable()
    {
        // 'loggable' must match the prefix used in the activity_logs table migration
        return $this->morphTo();
    }
}

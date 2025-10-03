<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'userid',
        'action',
        'details',
    ];

    public function loggable()
    {
        // 'loggable' must match the prefix used in the logs table migration
        return $this->morphTo();
    }
}

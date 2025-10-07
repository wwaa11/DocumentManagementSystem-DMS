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

    public function document()
    {
        return $this->morphTo('document', 'loggable_type', 'loggable_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'taskable_id',
        'taskable_type',
        'step',
        'status',
        'task_name',
        'task_user',
        'task_position',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function document()
    {

        return $this->morphTo('document', 'taskable_type', 'taskable_id');
    }

    public function user()
    {

        return $this->belongsTo(User::class, 'task_user', 'userid');
    }
}

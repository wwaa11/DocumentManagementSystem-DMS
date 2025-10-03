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
        'date',
    ];

    public function document()
    {
        return $this->morphTo('document', 'taskable_type', 'taskable_id');
    }
}

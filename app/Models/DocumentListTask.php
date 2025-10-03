<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentListTask extends Model
{
    protected $table = 'document_list_tasks';

    public $timestamps = false;

    protected $fillable = [
        'document_type',
        'step',
        'task_name',
        'task_description',
        'task_user',
        'task_position',
    ];
}

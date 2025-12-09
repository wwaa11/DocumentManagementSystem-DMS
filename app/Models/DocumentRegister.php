<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRegister extends Model
{
    protected $table = 'document_registers';

    protected $fillable = [
        'document_user_id',
        'document_number',
    ];

    protected $appends = [
        'document_tag',
    ];

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'Register',
            'colour'       => 'warning',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'requester', 'userid');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}

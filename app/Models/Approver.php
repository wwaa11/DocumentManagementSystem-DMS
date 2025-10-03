<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    public $timestamps = false;
    protected $table = 'approvers';

    protected $fillable = [
        'userid',
        'step',
    ];

    public function approvable()
    {
        // 'approvable' must match the prefix used in the approvers table migration
        // This will return the parent document (DocumentIT, DocumentHC, or DocumentPac)
        return $this->morphTo();
    }
}

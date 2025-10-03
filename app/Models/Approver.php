<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    public $timestamps = false;
    protected $table   = 'approvers';

    protected $fillable = [
        'userid',
        'step',
    ];

    public function document()
    {
        return $this->morphTo('document', 'approvable_type', 'approvable_id');
    }

}

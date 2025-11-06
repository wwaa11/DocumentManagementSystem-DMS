<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumber extends Model
{
    //
    protected $table = 'document_numbers';

    protected $fillable = [
        'document_type',
        'document_code',
        'document_number',
        'date',
    ];
    public $timestamps = false;

    public static function getNextNumber($type)
    {
        $documentNumber = self::where('document_type', $type)
            ->whereDate('date', date('Y-m-01'))
            ->first();

        if ($documentNumber == null) {
            $documentNumber = self::create([
                'document_type' => $type,
                'date'          => date('Y-m-01'),
            ]);
        }
        $documentNumber->number++;
        $documentNumber->save();

        if ($documentNumber) {

            return $type . '-' . date('Y-m') . '-' . str_pad($documentNumber->number, 4, '0', STR_PAD_LEFT);
        }

        return null;
    }
}

<?php

namespace App\Services;

use App\Models\DocumentBorrow;
use App\Models\DocumentIT;
use App\Models\DocumentMedia;
use App\Models\DocumentPurchase;
use App\Models\DocumentTraining;
use App\Models\DocumentUser;
use Illuminate\Database\Eloquent\Model;

class DocumentResolver
{
    public function resolve(string $documentType, int|string $documentId): ?Model
    {
        return match ($documentType) {
            'IT' => DocumentIT::find($documentId),
            'USER' => DocumentUser::find($documentId),
            'BORROW' => DocumentBorrow::find($documentId),
            'Training' => DocumentTraining::find($documentId),
            'PURCHASE' => DocumentPurchase::find($documentId),
            'MEDIA' => DocumentMedia::find($documentId),
            default => null,
        };
    }
}

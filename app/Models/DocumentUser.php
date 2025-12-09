<?php
namespace App\Models;

use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentIT;
use App\Models\DocumentPac;
use Illuminate\Database\Eloquent\Model;

class DocumentUser extends Model
{

    public function getAllDocuments()
    {
        $documnet_user_id = $this->id;
        $documnet         = [];

        $it = DocumentIT::where('document_user_id', $documnet_user_id)->first();
        if ($it) {
            $documnet[] = $it;
        }
        $pac = DocumentPac::where('document_user_id', $documnet_user_id)->first();
        if ($pac) {
            $documnet[] = $pac;
        }
        $hc = DocumentHc::where('document_user_id', $documnet_user_id)->first();
        if ($hc) {
            $documnet[] = $hc;
        }
        $heartstream = DocumentHeartstream::where('document_user_id', $documnet_user_id)->first();
        if ($heartstream) {
            $documnet[] = $heartstream;
        }
        $registration = DocumentRegister::where('document_user_id', $documnet_user_id)->first();
        if ($registration) {
            $documnet[] = $registration;
        }

        return $document;
    }
}

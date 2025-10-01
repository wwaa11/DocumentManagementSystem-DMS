<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentITController extends Controller
{
    public function createDocument(Request $request)
    {
        dd($request->all());
        $request->validate([
            'type'        => 'required|in:user,support',
            'requester'   => 'required|string',
            'title'       => 'required|string',
            'description' => 'required|string',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls|max:10240']);

        $path = $request->file('file')->store('imports');

        return response()->json(['path' => $path]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'             => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,gif,xlsx,xls,doc,docx',
            'attachable_type'  => 'required|string',
            'attachable_id'    => 'required|integer',
            'description'      => 'nullable|string|max:255',
        ]);

        $file     = $request->file('file');
        $mime     = $file->getMimeType();
        $fileType = Attachment::getFileType($mime);

        $path = $file->store('attachments/' . date('Y/m'), 'public');

        $attachment = Attachment::create([
            'attachable_type' => $request->attachable_type,
            'attachable_id'   => $request->attachable_id,
            'file_name'       => $file->getClientOriginalName(),
            'file_path'       => $path,
            'file_type'       => $fileType,
            'mime_type'       => $mime,
            'file_size'       => $file->getSize(),
            'description'     => $request->description,
            'uploaded_by'     => auth()->id(),
        ]);

        return response()->json([
            'id'          => $attachment->id,
            'file_name'   => $attachment->file_name,
            'file_type'   => $attachment->file_type,
            'file_size'   => $attachment->file_size_formatted,
            'url'         => Storage::url($path),
            'icon'        => $attachment->icon,
            'description' => $attachment->description,
        ]);
    }

    public function delete(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        return response()->json(['success' => true]);
    }

    public function download(Attachment $attachment)
    {
        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
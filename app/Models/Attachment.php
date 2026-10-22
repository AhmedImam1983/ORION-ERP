<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type', 'attachable_id',
        'file_name', 'file_path', 'file_type',
        'mime_type', 'file_size', 'description',
        'uploaded_by',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getIconAttribute(): string
    {
        return match($this->file_type) {
            'pdf'   => '📄',
            'image' => '🖼️',
            'excel' => '📊',
            'word'  => '📝',
            default => '📎',
        };
    }

    public static function getFileType(string $mime): string
    {
        return match(true) {
            str_contains($mime, 'pdf')                          => 'pdf',
            str_contains($mime, 'image')                        => 'image',
            str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet') => 'excel',
            str_contains($mime, 'word') || str_contains($mime, 'document')     => 'word',
            default                                             => 'other',
        };
    }
}
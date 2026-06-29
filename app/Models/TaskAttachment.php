<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        static::deleting(function (TaskAttachment $attachment) {
            Storage::delete($attachment->path);
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'FILE');
    }

    public function categorySlug(): string
    {
        $extension = strtolower($this->extension());
        $mimeType = (string) $this->mime_type;

        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            return 'pdfs';
        }

        if (str_starts_with($mimeType, 'image/')) {
            return 'images';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'videos';
        }

        if (in_array($extension, ['zip', 'rar', '7z'], true)) {
            return 'archives';
        }

        if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'], true)) {
            return 'documents';
        }

        return 'documents';
    }

    public function humanSize(): string
    {
        if ($this->size < 1024) {
            return $this->size.' B';
        }

        if ($this->size < 1024 * 1024) {
            return round($this->size / 1024, 1).' KB';
        }

        return round($this->size / 1024 / 1024, 1).' MB';
    }
}

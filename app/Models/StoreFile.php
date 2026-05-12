<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class StoreFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk',
        'path',
        'url',
        'mime_type',
        'size',
        'alt',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Delete the physical file from storage when the record is removed
        static::deleting(fn (self $file) => Storage::disk($file->disk)->delete($file->path));
    }

    // ── Type helpers ──────────────────────────────────────────────────────────

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) ($this->size ?? 0);

        return match (true) {
            $bytes >= 1_073_741_824 => round($bytes / 1_073_741_824, 2) . ' GB',
            $bytes >= 1_048_576    => round($bytes / 1_048_576, 2) . ' MB',
            $bytes >= 1_024        => round($bytes / 1_024, 2) . ' KB',
            default                => $bytes . ' B',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match (true) {
            $this->isImage()                       => 'Image',
            $this->isVideo()                       => 'Video',
            $this->mime_type === 'application/pdf' => 'PDF',
            default => strtoupper(pathinfo($this->filename, PATHINFO_EXTENSION) ?: 'File'),
        };
    }

    public function getMetaAttribute(): string
    {
        return $this->formatted_size . ' · ' . $this->type_label;
    }

    // ── Preview HTML (used by the Filament table grid card) ───────────────────

    public function getPreviewHtmlAttribute(): string
    {
        if ($this->isImage()) {
            $src = e(parse_url($this->url, PHP_URL_PATH) ?? ('/storage/' . $this->path));
            $alt = e($this->alt ?? $this->filename);
            return "<img src=\"{$src}\" alt=\"{$alt}\" style=\"width:80px;height:80px;object-fit:contain;display:block;margin:auto;\" loading=\"lazy\" />";
        }

        if ($this->isVideo()) {
            return $this->buildIconPreview(
                bg: 'bg-blue-50 dark:bg-blue-900/20',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>',
                label: 'Video',
                labelColor: 'text-blue-500',
            );
        }

        if ($this->mime_type === 'application/pdf') {
            return $this->buildIconPreview(
                bg: 'bg-red-50 dark:bg-red-900/20',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                label: 'PDF',
                labelColor: 'text-red-500',
            );
        }

        $ext = strtoupper(pathinfo($this->filename, PATHINFO_EXTENSION) ?: 'FILE');

        return $this->buildIconPreview(
            bg: 'bg-gray-100 dark:bg-gray-800',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
            label: $ext,
            labelColor: 'text-gray-500',
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_store_file')
            ->withPivot('position');
    }

    private function buildIconPreview(string $bg, string $icon, string $label, string $labelColor): string
    {
        return "<div class=\"{$bg}\" style=\"width:80px;height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;margin:auto;\">"
             . $icon
             . "<span class=\"text-xs font-semibold {$labelColor}\">" . e($label) . "</span>"
             . "</div>";
    }
}

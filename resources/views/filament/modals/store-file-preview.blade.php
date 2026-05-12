@php
    $src = parse_url($record->url, PHP_URL_PATH) ?? ('/storage/' . $record->path);
@endphp

@if ($record->isImage())
    <div class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
        <img
            src="{{ $src }}"
            alt="{{ $record->alt ?? $record->filename }}"
            style="max-width:100%;max-height:65vh;object-fit:contain;"
            class="rounded shadow"
        />
    </div>

@elseif ($record->isVideo())
    <div class="flex items-center justify-center p-4 bg-black rounded-lg">
        <video controls style="max-width:100%;max-height:65vh;" class="rounded">
            <source src="{{ $src }}" type="{{ $record->mime_type }}" />
            Your browser does not support the video tag.
        </video>
    </div>

@elseif ($record->mime_type === 'application/pdf')
    <div class="p-2">
        <iframe
            src="{{ $src }}"
            style="width:100%;height:65vh;border:none;"
            class="rounded"
        ></iframe>
    </div>

@else
    <div class="flex flex-col items-center justify-center gap-4 py-12">
        <x-heroicon-o-document class="w-16 h-16 text-gray-400" />
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $record->filename }}</p>
        <p class="text-xs text-gray-400">{{ $record->meta }}</p>
        <a
            href="{{ $src }}"
            target="_blank"
            class="text-sm text-primary-600 hover:text-primary-500 underline"
        >
            Open / Download
        </a>
    </div>
@endif

<div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
    <span class="text-xs text-gray-400">{{ $record->meta }}</span>
</div>

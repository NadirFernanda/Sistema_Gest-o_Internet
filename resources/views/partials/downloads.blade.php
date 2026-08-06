@if(!empty($files) && count($files))
    <div class="downloads" style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
        @foreach($files as $file)
            @php
                // Support model objects or arrays
                $path = '';
                $name = '';
                if (is_object($file)) {
                    $path = $file->path ?? $file->storage_path ?? $file->file_path ?? '';
                    $name = $file->name ?? $file->filename ?? basename($path);
                } elseif (is_array($file)) {
                    $path = $file['path'] ?? $file['storage_path'] ?? $file['file_path'] ?? '';
                    $name = $file['name'] ?? $file['filename'] ?? basename($path);
                } else {
                    $path = (string) $file;
                    $name = basename($path);
                }
                // Prefer app route that serves files (supports authorization/logging). Falls back to public storage URL.
                $publicUrl = $path ? route('files.download', ['encoded' => rtrim(strtr(base64_encode($path), '+/', '-_'), '=')]) : null;
            @endphp

            @if($publicUrl)
                <a href="{{ $publicUrl }}" class="btn btn-sm btn-primary" download="{{ $name }}" target="_blank" rel="noopener">Descarregar {{ $name }}</a>
            @endif
        @endforeach
    </div>
@endif

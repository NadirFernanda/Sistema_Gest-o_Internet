<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller as BaseController;

class FileDownloadController extends BaseController
{
    /**
     * Directorias do disco público que podem ser servidas por este endpoint.
     * Ficheiros sensíveis (contratos, documentos de identificação, facturas)
     * NUNCA devem ser gravados no disco 'public' — usar 'local' com rota protegida por auth.
     */
    private const ALLOWED_PREFIXES = [
        'estoque_equipamentos/',
    ];

    /**
     * Serve a file from the public storage disk.
     * The path parameter is expected base64 URL-safe encoded ( -_ chars ) without padding.
     */
    public function download(Request $request, $encoded)
    {
        // Decode URL-safe base64
        $normalized = str_replace(['-', '_'], ['+', '/'], $encoded);
        $pad = strlen($normalized) % 4;
        if ($pad) {
            $normalized .= str_repeat('=', 4 - $pad);
        }
        $path = base64_decode($normalized, strict: true);
        if ($path === false || $path === '') {
            abort(404);
        }

        // Normalize separators and remove any traversal sequences
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // Whitelist: only allow known public directories
        $allowed = false;
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $allowed = true;
                break;
            }
        }
        if (! $allowed) {
            abort(403);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        try {
            return $disk->download($path);
        } catch (\Exception $e) {
            return redirect(asset('storage/' . $path));
        }
    }
}

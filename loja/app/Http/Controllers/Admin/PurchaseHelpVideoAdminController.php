<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class PurchaseHelpVideoAdminController extends Controller
{
    public function index(): View
    {
        $videoPath = DB::table('purchase_help_videos')->where('id', 1)->value('video_path');

        return view('admin.purchase_help_video.index', [
            'videoPath' => $videoPath,
            'videoUrl' => $videoPath ? Storage::disk('public')->url($videoPath) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,webm', 'mimetypes:video/mp4,video/webm', 'max:512000'],
        ], [
            'video.required' => 'Seleccione um vídeo para enviar.',
            'video.file' => 'O ficheiro enviado não é válido.',
            'video.mimes' => 'O vídeo deve estar no formato MP4 ou WebM.',
            'video.mimetypes' => 'O conteúdo do ficheiro não corresponde a um vídeo MP4 ou WebM.',
            'video.max' => 'O vídeo não pode exceder 500 MB.',
        ]);

        $videoPath = $request->file('video')->store('purchase-help', 'public');
        if (! $videoPath) {
            throw new RuntimeException('Não foi possível guardar o vídeo de ajuda da compra.');
        }

        $oldVideoPath = DB::table('purchase_help_videos')->where('id', 1)->value('video_path');
        DB::table('purchase_help_videos')->updateOrInsert(
            ['id' => 1],
            ['video_path' => $videoPath, 'created_at' => now(), 'updated_at' => now()]
        );

        if ($oldVideoPath && $oldVideoPath !== $videoPath) {
            Storage::disk('public')->delete($oldVideoPath);
        }

        return redirect()->route('admin.purchase-help-video.index')
            ->with('status', 'Vídeo de ajuda actualizado com sucesso.');
    }

    public function destroy(): RedirectResponse
    {
        $videoPath = DB::table('purchase_help_videos')->where('id', 1)->value('video_path');
        DB::table('purchase_help_videos')->where('id', 1)->update([
            'video_path' => null,
            'updated_at' => now(),
        ]);

        if ($videoPath) {
            Storage::disk('public')->delete($videoPath);
        }

        return redirect()->route('admin.purchase-help-video.index')
            ->with('status', 'Vídeo removido da ajuda de compra.');
    }
}

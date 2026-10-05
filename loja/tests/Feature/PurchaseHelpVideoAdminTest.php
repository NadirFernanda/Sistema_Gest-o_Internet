<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseHelpVideoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_remove_the_purchase_help_video(): void
    {
        config(['services.sg.admin_token' => 'test-admin-token']);
        Storage::fake('public');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.purchase-help-video.store'), [
                'video' => UploadedFile::fake()->create('como-comprar.mp4', 256, 'video/mp4'),
            ])
            ->assertRedirect(route('admin.purchase-help-video.index'));

        $videoPath = DB::table('purchase_help_videos')->where('id', 1)->value('video_path');
        $this->assertNotEmpty($videoPath);
        Storage::disk('public')->assertExists($videoPath);

        $helpHtml = view('partials.purchase-help', [
            'context' => 'plans',
            'purchaseHelpVideoUrl' => Storage::disk('public')->url($videoPath),
        ])->render();
        $this->assertStringContainsString($videoPath, $helpHtml);
        $this->assertStringContainsString('<video', $helpHtml);

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.purchase-help-video.index'))
            ->assertOk()
            ->assertSee($videoPath, false);

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.purchase-help-video.destroy'))
            ->assertRedirect(route('admin.purchase-help-video.index'));

        $this->assertNull(DB::table('purchase_help_videos')->where('id', 1)->value('video_path'));
        Storage::disk('public')->assertMissing($videoPath);
    }
}

<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroSlideAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sg.admin_token' => 'test-admin-token']);
        Storage::fake('public');
    }

    public function test_existing_slides_are_migrated_and_admin_can_manage_the_full_lifecycle(): void
    {
        $this->assertSame(3, HeroSlide::count());
        $this->get(route('admin.hero-slides.index'))->assertRedirect(route('admin.login'));

        $admin = $this->withSession(['sg_admin_authenticated' => true]);
        $admin->get(route('admin.hero-slides.index'))->assertOk()->assertSee('WiFi Ilimitada');
        $admin->get(route('admin.hero-slides.create'))->assertOk()->assertSee('Adicionar slide');

        $admin->post(route('admin.hero-slides.store'), [
            'image' => UploadedFile::fake()->image('novo.jpg', 1600, 900),
            'eyebrow' => 'Novidade',
            'title' => 'Título novo',
            'highlighted_title' => 'Destaque novo',
            'description' => 'Descrição nova',
            'primary_label' => 'Ver planos',
            'primary_url' => '#planos',
            'secondary_label' => 'Saiba mais',
            'secondary_url' => '/como-comprar',
            'sort_order' => 0,
            'active' => 1,
        ])->assertRedirect(route('admin.hero-slides.index'));

        $slide = HeroSlide::where('title', 'Título novo')->firstOrFail();
        $admin->get(route('admin.hero-slides.edit', $slide))->assertOk()->assertSee('Título novo');
        Storage::disk('public')->assertExists($slide->image_path);
        $this->get(route('hero-slides.image', $slide))->assertOk()->assertHeader('content-type', 'image/jpeg');
        $this->get('/')->assertOk()->assertSee('Destaque novo')->assertSee('Descrição nova')->assertSee(route('hero-slides.image', $slide));

        $oldImage = $slide->image_path;
        $admin->put(route('admin.hero-slides.update', $slide), [
            'image' => UploadedFile::fake()->image('substituta.png'),
            'title' => 'Título editado',
            'sort_order' => 4,
            'active' => 0,
        ])->assertRedirect(route('admin.hero-slides.index'));
        $slide->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($slide->image_path);
        $this->get('/')->assertDontSee('Título editado');

        $newImage = $slide->image_path;
        $admin->delete(route('admin.hero-slides.destroy', $slide))->assertRedirect(route('admin.hero-slides.index'));
        Storage::disk('public')->assertMissing($newImage);
        $this->assertDatabaseMissing('hero_slides', ['id' => $slide->id]);
    }

    public function test_unsafe_links_and_invalid_images_are_rejected(): void
    {
        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.hero-slides.store'), [
                'image' => UploadedFile::fake()->create('texto.txt', 10, 'text/plain'),
                'title' => 'Teste',
                'sort_order' => 1,
            ])->assertSessionHasErrors('image');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.hero-slides.store'), [
                'image' => UploadedFile::fake()->image('valida.jpg'),
                'title' => 'Teste',
                'primary_label' => 'Abrir',
                'primary_url' => 'javascript:alert(1)',
                'sort_order' => 1,
            ])->assertSessionHasErrors('primary_url');
        $this->assertSame(3, HeroSlide::count());
    }
}

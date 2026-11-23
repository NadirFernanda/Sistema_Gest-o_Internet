<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HeroSlideAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.hero_slides.index', [
            'slides' => HeroSlide::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.hero_slides.form', ['slide' => new HeroSlide(['active' => true, 'sort_order' => (HeroSlide::max('sort_order') ?? 0) + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $data['image_path'] = $request->file('image')->store('hero-slides', 'public');
        unset($data['image']);
        HeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide criado com sucesso.');
    }

    public function edit(HeroSlide $slide): View
    {
        return view('admin.hero_slides.form', compact('slide'));
    }

    public function update(Request $request, HeroSlide $slide): RedirectResponse
    {
        $data = $this->validated($request);
        unset($data['image']);
        $oldPath = $slide->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('hero-slides', 'public');
        }
        $slide->update($data);
        if ($oldPath !== $slide->image_path) {
            $this->deleteUploadedImage($oldPath);
        }

        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide actualizado com sucesso.');
    }

    public function destroy(HeroSlide $slide): RedirectResponse
    {
        $path = $slide->image_path;
        $slide->delete();
        $this->deleteUploadedImage($path);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide removido.');
    }

    private function validated(Request $request, bool $creating = false): array
    {
        $data = $request->validate([
            'image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'highlighted_title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'primary_label' => ['nullable', 'string', 'max:80', 'required_with:primary_url'],
            'primary_url' => ['nullable', 'string', 'max:2048', 'required_with:primary_label'],
            'secondary_label' => ['nullable', 'string', 'max:80', 'required_with:secondary_url'],
            'secondary_url' => ['nullable', 'string', 'max:2048', 'required_with:secondary_label'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'active' => ['nullable', 'boolean'],
        ]);

        foreach (['primary_url', 'secondary_url'] as $field) {
            $url = $data[$field] ?? null;
            if ($url !== null && ! preg_match('~\A(?:/(?!/)[^\s\\\\]*|#[a-zA-Z][a-zA-Z0-9_-]*|https?://[^\s\\\\]+)\z~i', $url)) {
                throw ValidationException::withMessages([$field => 'Use um caminho interno, uma âncora (#secção) ou um endereço http/https.']);
            }
        }

        $data['active'] = $request->boolean('active');
        return $data;
    }

    private function deleteUploadedImage(string $path): void
    {
        if (str_starts_with($path, 'hero-slides/')) {
            Storage::disk('public')->delete($path);
        }
    }
}

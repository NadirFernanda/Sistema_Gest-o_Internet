<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::orderBy('id', 'desc')->get();
        return view('sites', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sites.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'localizacao' => 'nullable|string|max:255',
            'status' => 'required|string',
            'capacidade' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);
        $site = Site::create($data);
        return redirect()->route('sites.show', $site)->with('success', 'Site cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return view('sites.show', compact('site'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'localizacao' => 'nullable|string|max:255',
            'status' => 'required|string',
            'capacidade' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);
        $site->update($data);
        return redirect()->route('sites.show', $site)->with('success', 'Site atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('sites.index')->with('success', 'Site removido com sucesso!');
    }
}

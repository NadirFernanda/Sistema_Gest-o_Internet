@extends('layouts.app')

@section('content')
<style>
    .form-modern input[type="text"],
    .form-modern input[type="number"],
    .form-modern input[type="url"],
    .form-modern textarea,
    .form-modern select {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 10px 14px;
        font-size: 1rem;
        margin-bottom: 4px;
        width: 100%;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        font-family: inherit;
    }
    .form-modern input:focus, .form-modern textarea:focus, .form-modern select:focus {
        border-color: #f7b500;
        box-shadow: 0 2px 8px rgba(247,181,0,0.10);
    }
    .form-modern label { font-size: 0.98rem; color: #222; margin-bottom: 2px; font-weight: 500; display: block; }
    .form-modern .form-group { margin-bottom: 18px; text-align: left; }
    .form-modern .btn-primary {
        background: #f7b500; color: #0F172A; border: none; border-radius: 10px;
        font-size: 1.05rem; padding: 12px 0; width: 100%; margin-top: 10px;
        box-shadow: 0 2px 8px rgba(247,181,0,0.08); transition: background 0.2s; font-weight: 700; cursor: pointer;
    }
    .form-modern .btn-primary:hover { background: #e0a800; }
    .text-danger { color: #b91c1c; font-size: 0.85rem; margin-top: 2px; display: block; }
</style>
<div class="container" style="max-width: 560px; margin: 40px auto;">
    <div style="text-align:center; margin-bottom:12px;">
        <a href="{{ route('catalog_equipamentos.index') }}" class="btn btn-ghost">← Voltar ao catálogo</a>
    </div>
    <h2 style="text-align:center; margin-bottom:1.5rem;">Editar Produto — {{ $item->nome }}</h2>

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:1rem;">
            <ul style="margin:0;padding-left:1rem;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('catalog_equipamentos.update', $item->id) }}" method="POST" class="form-modern">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nome">Nome do produto <span class="text-danger" style="display:inline;">*</span></label>
            <input type="text" id="nome" name="nome" value="{{ old('nome', $item->nome) }}" placeholder="Ex: Router TP-Link TL-WR840N">
            @error('nome')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="categoria">Categoria</label>
            <input type="text" id="categoria" name="categoria" value="{{ old('categoria', $item->categoria) }}" placeholder="Ex: Routers, Repetidores, Antenas, Cabos">
            @error('categoria')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="preco">Preço (Kz) <span class="text-danger" style="display:inline;">*</span></label>
            <input type="number" id="preco" name="preco" value="{{ old('preco', $item->preco) }}" min="0" step="1">
            @error('preco')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="quantidade">Quantidade em stock <span class="text-danger" style="display:inline;">*</span></label>
            <input type="number" id="quantidade" name="quantidade" value="{{ old('quantidade', $item->quantidade) }}" min="0" step="1">
            @error('quantidade')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição curta do produto...">{{ old('descricao', $item->descricao) }}</textarea>
            @error('descricao')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="imagem_url">URL da imagem (opcional)</label>
            <input type="url" id="imagem_url" name="imagem_url" value="{{ old('imagem_url', $item->imagem_url) }}" placeholder="https://...">
            @error('imagem_url')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="ativo" value="1" style="width:auto;" {{ old('ativo', $item->ativo ? '1' : '') == '1' ? 'checked' : '' }}>
                Visível na loja
            </label>
        </div>

        <button type="submit" class="btn-primary">Guardar alterações</button>
    </form>
</div>
@endsection

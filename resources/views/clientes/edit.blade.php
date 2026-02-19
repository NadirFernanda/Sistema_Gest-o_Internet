@extends('layouts.app')

@section('content')
<div class="container" style="max-width:960px;margin:18px auto;">
    <h1>Editar Cliente</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('clientes.update', $cliente->id) }}" style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.06);">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label for="editBI"><strong>BI / NIF</strong></label>
                <input id="editBI" name="bi" type="text" value="{{ old('bi', $cliente->bi) }}" class="form-control" required>
                @error('bi') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="editNome"><strong>Nome</strong></label>
                <input id="editNome" name="nome" type="text" value="{{ old('nome', $cliente->nome) }}" class="form-control" required>
                @error('nome') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="editEmail"><strong>Email</strong></label>
                <input id="editEmail" name="email" type="email" value="{{ old('email', $cliente->email) }}" class="form-control">
                @error('email') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="editContato"><strong>Contacto (WhatsApp)</strong></label>
                <input id="editContato" name="contato" type="text" value="{{ old('contato', $cliente->contato) }}" class="form-control">
                @error('contato') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:8px;align-items:center;">
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-ghost">Voltar à ficha</a>
        </div>
    </form>
</div>
@endsection

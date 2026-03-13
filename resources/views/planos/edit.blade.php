@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <a href="{{ route('planos.index') }}" class="btn btn-secondary">← Voltar</a>
            <h2 style="margin:0">Editar Plano: {{ $plano->nome }}</h2>
        </div>
        @include('planos._form', ['plano' => $plano, 'clientes' => $clientes, 'editMode' => true])
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <h2 style="margin-top:0">Plano: {{ $plano->nome }}</h2>
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
            <div style="font-weight:700;font-size:1.15rem;color:#111">{{ $plano->preco ? 'Kz '.number_format($plano->preco,2,',','.') : '-' }}</div>
            <div style="background:#f0f0f0;padding:6px 10px;border-radius:20px">{{ $plano->ciclo }} dias</div>
            <div style="margin-left:auto">Status: <strong>{{ $plano->estado }}</strong></div>
        </div>
        <p>{{ $plano->descricao }}</p>
        <div style="margin-top:12px;display:flex;gap:8px;">
            <a href="{{ route('planos.edit', $plano->id) }}" class="btn btn-warning">Editar</a>
            <form action="{{ route('planos.destroy', $plano->id) }}" method="POST" onsubmit="return confirm('Apagar plano?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Apagar</button>
            </form>
            <a href="{{ route('planos.index') }}" class="btn btn-ghost">Voltar</a>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <div style="margin-bottom:16px;">
            <a href="{{ route('planos.index') }}" class="btn btn-secondary">← Voltar</a>
            <h2 style="margin-top:12px;text-align:center;">Editar Plano: {{ $plano->nome }}</h2>
        </div>
        @include('planos._form', ['plano' => $plano, 'clientes' => $clientes, 'editMode' => true])
    </div>
@endsection

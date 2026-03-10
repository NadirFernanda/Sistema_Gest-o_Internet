@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <h2 style="margin-top:0">Editar Plano: {{ $plano->nome }}</h2>
        @include('planos._form', ['plano' => $plano, 'clientes' => $clientes, 'editMode' => true])
    </div>
@endsection

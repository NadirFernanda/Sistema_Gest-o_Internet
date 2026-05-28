@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Novo Plano',
        'subtitle' => 'Contrato de serviço de internet',
        'heroCtAs' => '<a href="'.route('planos.index').'" class="btn btn-ghost">← Planos</a>',
    ])

    <div style="max-width:660px; margin:24px auto 56px; padding:0 16px;">
        @include('planos._form')
    </div>

</div>
@endsection

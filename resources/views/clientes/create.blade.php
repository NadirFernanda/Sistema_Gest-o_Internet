@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Cliente</h2>

    <form id="formClienteCreate" class="form-cadastro" method="POST" action="{{ route('clientes.store') }}">
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome completo</label>
            <input type="text" id="nome" name="nome" class="form-control" placeholder="Nome completo" required>
        </div>
        <div class="mb-3">
            <label for="bi" class="form-label">BI / NIF</label>
            <input type="text" id="bi" name="bi" class="form-control" placeholder="BI / NIF" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="email@exemplo.com" required>
        </div>
        <div class="mb-3">
            <label for="contato" class="form-label">Contacto (WhatsApp)</label>
            <input type="text" id="contato" name="contato" class="form-control" placeholder="+244 9XX XXX XXX" required>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Cliente</button>
        <a href="{{ route('clientes') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection

<!-- No client-side JS needed: form submits normally to the controller -->

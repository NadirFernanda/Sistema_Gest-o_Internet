@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <h2 style="margin-top:0">Editar Plano: {{ $plano->nome }}</h2>
        <form id="formPlano" data-no-ajax="1" action="{{ route('planos.update', $plano->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <input name="nome" value="{{ old('nome', $plano->nome) }}" required placeholder="Nome" />
                <input name="preco" value="{{ old('preco', $plano->preco) }}" placeholder="Preço" />
                <select name="cliente_id" class="select" data-placeholder="Pesquisar cliente...">
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ $plano->cliente_id == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                    @endforeach
                </select>
                <input name="ciclo" value="{{ old('ciclo', $plano->ciclo) }}" placeholder="Ciclo (dias)" />
                <input type="date" name="data_ativacao" value="{{ old('data_ativacao', $plano->data_ativacao ? \Carbon\Carbon::parse($plano->data_ativacao)->format('Y-m-d') : '') }}" placeholder="Data de ativação" />
                <select name="estado" class="select" aria-label="Estado do plano">
                    <option value="">Escolha o estado</option>
                    @php $st = old('estado', $plano->estado); @endphp
                    <option value="Ativo" {{ $st === 'Ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="Em aviso" {{ $st === 'Em aviso' ? 'selected' : '' }}>Em aviso</option>
                    <option value="Suspenso" {{ $st === 'Suspenso' ? 'selected' : '' }}>Suspenso</option>
                    <option value="Cancelado" {{ $st === 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="Site" {{ $st === 'Site' ? 'selected' : '' }}>Site</option>
                    <option value="Agente Autorizado" {{ $st === 'Agente Autorizado' ? 'selected' : '' }}>Agente Autorizado</option>
                </select>
            </div>
            <div style="margin-top:8px;">
                <textarea name="descricao" style="width:100%">{{ old('descricao', $plano->descricao) }}</textarea>
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="{{ route('planos.show', $plano->id) }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

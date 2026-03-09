@extends('layouts.app')

@section('content')

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Catálogo de Venda — Equipamentos',
        'subtitle' => 'Produtos visíveis na loja (angolawifi.ao/equipamentos)',
        'stackLeft' => true,
    ])

    <div class="clientes-toolbar">
        <form method="GET" action="{{ route('catalog_equipamentos.index') }}" class="search-form-inline">
            <input type="search" name="busca" value="{{ $busca ?? '' }}" placeholder="Pesquisar por nome ou categoria..." class="search-input" />
            <button type="submit" class="btn btn-search">Pesquisar</button>
            @if(!empty($busca))
                <a href="{{ route('catalog_equipamentos.index') }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
            @endif
        </form>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('catalog_equipamentos.create') }}" class="btn btn-cta">+ Adicionar Produto</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço (Kz)</th>
                    <th>Qtd.</th>
                    <th>Ativo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itens as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->nome }}</strong>
                            @if($item->descricao)
                                <br><small style="color:#64748b;">{{ Str::limit($item->descricao, 60) }}</small>
                            @endif
                        </td>
                        <td>{{ $item->categoria ?: '—' }}</td>
                        <td>{{ number_format($item->preco, 0, ',', '.') }}</td>
                        <td>
                            @if($item->quantidade <= 0)
                                <span style="color:#b91c1c;font-weight:700;">0 (sem stock)</span>
                            @else
                                {{ $item->quantidade }}
                            @endif
                        </td>
                        <td>
                            @if($item->ativo)
                                <span style="color:#16a34a;font-weight:700;">✓ Visível</span>
                            @else
                                <span style="color:#94a3b8;">Oculto</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('catalog_equipamentos.edit', $item->id) }}" class="btn-icon btn-warning" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                            <form action="{{ route('catalog_equipamentos.destroy', $item->id) }}" method="POST" style="display:inline-block;margin-left:6px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-danger" title="Apagar" onclick="return confirm('Remover este produto do catálogo?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:#64748b;">
                            Nenhum produto no catálogo. <a href="{{ route('catalog_equipamentos.create') }}">Adicionar o primeiro</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

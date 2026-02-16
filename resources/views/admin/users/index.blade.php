@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Usuários',
        'subtitle' => '',
        'stackLeft' => true,
    ])

    <div class="clientes-toolbar">
        <div class="toolbar-left">
            <form method="GET" action="{{ route('admin.users.index') }}" class="search-form-inline">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Pesquisar por nome ou e-mail..." class="search-input modern-search" />
                <button type="submit" class="btn btn-cta btn-search">Pesquisar</button>
                @if(request('q'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-clear">Limpar</a>
                @endif
            </form>
        </div>
        <div class="toolbar-right">
            @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-cta">Criar usuário</a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="estoque-tabela-moderna">
        <div class="table-responsive">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Papéis</th>
                    <th>Criado</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->roles->pluck('name')->join(', ') }}</td>
                        <td>{{ $u->created_at->format('Y-m-d') }}</td>
                        <td style="white-space:nowrap;text-align:center;">
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </a>
                            @endcan
                            @can('users.delete')
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="Apagar" aria-label="Apagar" onclick="return confirm('Confirma exclusão deste usuário?');">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Nenhum usuário encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>

@endsection
    <style>
    /* Copied from estoque_equipamentos index to ensure identical table appearance */
    .tabela-estoque-moderna th {
        background: #fffbe7;
        color: #f7b500;
        font-weight: bold;
        font-size: 1.09em;
        border-bottom: 2px solid #ffe6a0;
        padding: 14px 12px;
    }
    .tabela-estoque-moderna td {
        background: #fff;
        color: #222;
        font-size: 1em;
        padding: 13px 12px;
    }
    .tabela-estoque-moderna tr {
        border-bottom: 1px solid #f3e6b0;
    }
    .table-responsive { overflow-x:auto; }
    .clientes-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:18px 0; }
    .clientes-toolbar .search-form-inline { display:flex; gap:10px; align-items:center; }
    .modern-search { min-width:360px; padding:12px 14px; border-radius:10px; border:2px solid #f7b500; background:#fffaf0; }
    .btn-cta { background:#f7b500; color:#fff; border-radius:10px; padding:10px 18px; font-weight:700; border:none; }
    .btn-ghost { background:transparent; color:#f7b500; border:1px solid rgba(247,181,0,0.12); border-radius:10px; padding:10px 16px; }
    .btn-search { padding:10px 16px; }
    .btn-clear { padding:8px 12px; }
    .btn-icon {
        padding: 6px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid #e6e6e6;
        background: #fff;
        color: #222;
        cursor: pointer;
        transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
    }
    .btn-icon svg { width: 16px; height: 16px; }
    .btn-icon:hover { background: #f7b500; color: #fff; border-color: #f7b500; }
    .btn-icon.btn-danger:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
    </style>

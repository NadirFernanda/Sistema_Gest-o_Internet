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
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-form-inline">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Pesquisar por nome ou e-mail..." class="search-input" />
            <button type="submit" class="btn btn-search">Pesquisar</button>
            @if(request('q'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
            @endif
        </form>
        <div style="display:flex;gap:8px;">
            @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-cta">Criar usuário</a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">Nome</th>
                    <th style="text-align:center;vertical-align:middle;">E-mail</th>
                    <th style="text-align:center;vertical-align:middle;">Papéis</th>
                    <th style="text-align:center;vertical-align:middle;">Criado</th>
                    <th style="text-align:center;vertical-align:middle;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->name }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->email }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->roles->pluck('name')->join(', ') }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->created_at->format('Y-m-d') }}</td>
                        <td style="white-space:nowrap;text-align:center;vertical-align:middle;">
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

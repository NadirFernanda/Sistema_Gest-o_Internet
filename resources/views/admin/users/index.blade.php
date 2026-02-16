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
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">#</th>
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
                        <td style="text-align:center;vertical-align:middle;">{{ $u->id }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->name }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->email }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->roles->pluck('name')->join(', ') }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $u->created_at->format('Y-m-d') }}</td>
                        <td style="white-space:nowrap;text-align:center;vertical-align:middle;">
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                    Editar
                                </a>
                            @endcan
                            @can('users.delete')
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="Apagar" aria-label="Apagar" onclick="return confirm('Deseja apagar este usuário?')">Apagar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nenhum usuário encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>

@endsection

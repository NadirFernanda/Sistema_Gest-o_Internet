@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Auditoria',
        'subtitle' => '',
        'stackLeft' => true,
    ])

    {{-- filtro removido conforme solicitado: permanecer apenas header e tabela --}}

    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">ID</th>
                    <th style="text-align:center;vertical-align:middle;">Quando</th>
                    <th style="text-align:center;vertical-align:middle;">Usuário</th>
                    <th style="text-align:center;vertical-align:middle;">Ação</th>
                    <th style="text-align:center;vertical-align:middle;">Módulo</th>
                    <th style="text-align:center;vertical-align:middle;">Recurso</th>
                    <th style="text-align:center;vertical-align:middle;">Resumo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $a)
                    <tr>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->id }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->created_at }}</td>
                        <td style="text-align:center;vertical-align:middle;">
                            {{ $a->actor_name ?? (\App\Models\User::find($a->user_id)?->name ?? $a->user_id) }}
                            ({{ \App\Services\AuditService::translateRole($a->actor_role ?? $a->role) }})
                        </td>
                        <td style="text-align:center;vertical-align:middle;">{{ \App\Services\AuditService::translateAction($a->action) }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->module }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ class_basename($a->resource_type ?? $a->auditable_type) }}#{{ $a->resource_id ?? $a->auditable_id }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ \App\Services\AuditService::formatHumanReadable($a) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $audits->links() }}
</div>
<style>
.estoque-container-moderna {
    max-width: 1100px;
    margin: 48px auto 0 auto;
    background: #fafafa;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    padding: 38px 38px 38px 38px;
    min-width: 350px;
}
.estoque-cabecalho-moderna {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
}
.estoque-cabecalho-moderna h1 {
    color: #111;
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 0;
}
.estoque-cabecalho-botoes {
    max-width: 1100px;
    margin: 18px auto 0;
    margin-bottom: 12px;
    padding: 0 12px;
}
.estoque-cabecalho-botoes-inner {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}
.estoque-cabecalho-botoes-inner .btn-block {
    display: block;
    width: 100%;
    padding: 12px 18px;
    border-radius: 12px;
    background: #f7b500;
    color: #fff;
    text-align: center;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(247,181,0,0.18);
    text-decoration: none;
}
.estoque-cabecalho-botoes-inner .btn-block:hover { opacity: 0.95; }
.estoque-busca-form {
    margin: 0 0 8px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
}
.estoque-busca-form input[type="text"] {
    flex: 1;
    min-width: 220px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.estoque-busca-form button[type="submit"] {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #f7b500;
    color: #fff;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-busca-form .btn-limpar-busca {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #aaa;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-tabela-moderna {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px #0001;
    padding: 18px 18px 8px 18px;
    margin-top: 18px;
    overflow-x: auto;
}
.tabela-estoque-moderna {
    width: 100%;
    min-width: 640px;
    font-size: 1.07em;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.tabela-estoque-moderna td .btn,
.tabela-estoque-moderna td form .btn {
    padding: 6px 8px;
    font-size: 0.95em;
}
.tabela-estoque-moderna th,
.tabela-estoque-moderna td {
    padding: 8px 6px;
}

/* Responsive: hide Nº Série (4th column) on narrower viewports and tighten table */
@media (max-width: 900px) {
    .tabela-estoque-moderna {
        min-width: 520px;
        font-size: 0.98em;
    }
    .tabela-estoque-moderna th:nth-child(4),
    .tabela-estoque-moderna td:nth-child(4) {
        display: none;
    }
    /* Reduce padding on very small screens */
    @media (max-width: 640px) {
        .tabela-estoque-moderna th,
        .tabela-estoque-moderna td {
            padding: 6px 6px;
            font-size: 0.95em;
        }
        .tabela-estoque-moderna { min-width: 480px; }
    }
}
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
/* Icon buttons for compact actions */
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
@endsection

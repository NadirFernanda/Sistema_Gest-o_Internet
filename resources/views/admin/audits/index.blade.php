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
@endsection

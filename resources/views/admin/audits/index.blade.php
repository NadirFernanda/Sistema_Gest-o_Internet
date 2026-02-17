@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Auditoria</h1>

    <form method="get" class="mb-3">
        <div class="row g-2">
            <div class="col-sm-3"><input name="user" value="{{ request('user') }}" placeholder="Usuário" class="form-control"/></div>
            <div class="col-sm-2"><input name="module" value="{{ request('module') }}" placeholder="Módulo" class="form-control"/></div>
            <div class="col-sm-2"><input name="action" value="{{ request('action') }}" placeholder="Ação" class="form-control"/></div>
            <div class="col-sm-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"/></div>
            <div class="col-sm-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"/></div>
            <div class="col-sm-1"><button class="btn btn-primary">Filtrar</button></div>
        </div>
    </form>

    <table class="table table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Quando</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Módulo</th>
                <th>Recurso</th>
                <th>Resumo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audits as $a)
                <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ $a->created_at }}</td>
                    <td>
                        {{ $a->actor_name ?? (\App\Models\User::find($a->user_id)?->name ?? $a->user_id) }}
                        ({{ $a->actor_role ?? $a->role }})
                    </td>
                    <td>{{ $a->action }}</td>
                    <td>{{ $a->module }}</td>
                    <td>{{ class_basename($a->resource_type ?? $a->auditable_type) }}#{{ $a->resource_id ?? $a->auditable_id }}</td>
                    <td>{{ \App\Services\AuditService::formatHumanReadable($a) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $audits->links() }}
</div>
@endsection

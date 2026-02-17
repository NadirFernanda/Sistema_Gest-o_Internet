@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Logs de Auditoria</h1>

  <form class="mb-3" method="get">
    <div class="row">
      <div class="col-md-3"><input name="role" class="form-control" placeholder="Role" value="{{ request('role') }}"></div>
      <div class="col-md-3"><input name="action" class="form-control" placeholder="Action" value="{{ request('action') }}"></div>
      <div class="col-md-4"><input name="model" class="form-control" placeholder="Model (FQCN)" value="{{ request('model') }}"></div>
      <div class="col-md-2"><button class="btn btn-primary">Filtrar</button></div>
    </div>
  </form>

  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Quando</th>
        <th>Usuário</th>
        <th>Role</th>
        <th>Ação</th>
        <th>Alvo</th>
        <th>Antes / Depois</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      @foreach($logs as $log)
      <tr>
        <td>{{ $log->id }}</td>
        <td>{{ $log->created_at }}</td>
        <td>{{ $log->user_id }}</td>
        <td>{{ $log->role }}</td>
        <td>{{ $log->action }}</td>
        <td>{{ optional($log->auditable_type) ? class_basename($log->auditable_type)." (#{$log->auditable_id})" : '-' }}</td>
        <td style="max-width:300px;overflow:auto">
          <small>
            <strong>Old:</strong> {{ json_encode($log->old_values) }}
            <br>
            <strong>New:</strong> {{ json_encode($log->new_values) }}
          </small>
        </td>
        <td>{{ $log->ip_address }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $logs->links() }}
</div>
@endsection

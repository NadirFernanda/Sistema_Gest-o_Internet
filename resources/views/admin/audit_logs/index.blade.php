@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Logs de Auditoria</h1>

  <form class="mb-3" method="get" aria-label="Filtrar logs de auditoria">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small">Usuário (nome ou email)</label>
        <input name="user" class="form-control" placeholder="Digite nome ou email" value="{{ request('user') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Função</label>
        <select name="role" class="form-control">
          <option value="">— Todas —</option>
          @if(!empty($roles))
            @foreach($roles as $r)
              <option value="{{ $r }}" @selected(request('role') == $r)>{{ $r }}</option>
            @endforeach
          @endif
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Data (de)</label>
        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Data (até)</label>
        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
      </div>
      <div class="col-md-2 text-end">
        <button class="btn btn-warning w-100">Filtrar</button>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-md-4">
        <label class="form-label small">Ação</label>
        <input name="action" class="form-control" placeholder="Ex: updated, created, deleted" value="{{ request('action') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Modelo (FQCN)</label>
        <input name="model" class="form-control" placeholder="Ex: App\\Models\\Cliente" value="{{ request('model') }}">
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Quando</th>
          <th>Nome</th>
          <th>Função</th>
          <th>Ação</th>
          <th>Alvo</th>
          <th>Antes</th>
          <th>Depois</th>
          <th>IP</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $log)
        <tr>
          <td>{{ $log->id }}</td>
          <td>{{ $log->created_at }}</td>
          <td>
            @if(isset($users) && $users->has($log->user_id))
              <div>{{ $users->get($log->user_id)->name }}</div>
              <div class="small text-muted">{{ $users->get($log->user_id)->email }}</div>
            @else
              <div>{{ $log->user_id ?? '-' }}</div>
            @endif
          </td>
          <td>
            @php
              $rolesList = [];
              if(isset($users) && $users->has($log->user_id)) {
                $rolesList = $users->get($log->user_id)->roles->pluck('name')->toArray();
              } elseif(!empty($log->role)) {
                $rolesList = array_map('trim', explode(',', $log->role));
              }
              $roleColors = [
                'Administrador' => 'bg-danger',
                'Gerente' => 'bg-primary',
                'Colaborador' => 'bg-secondary',
              ];
            @endphp
            @if(count($rolesList))
              @foreach($rolesList as $r)
                <span class="badge {{ $roleColors[$r] ?? 'bg-light text-dark' }} me-1">{{ $r }}</span>
              @endforeach
            @else
              -
            @endif
          </td>
          <td>{{ $log->action }}</td>
          <td>{{ optional($log->auditable_type) ? class_basename($log->auditable_type)." (#{$log->auditable_id})" : '-' }}</td>
          @php
            $old = $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
            $new = $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
          @endphp
          <td style="max-width:240px;overflow:hidden;vertical-align:top;">
            <div class="small text-muted">{{ Str::limit($old, 180) ?: '(vazio)' }}</div>
          </td>
          <td style="max-width:240px;overflow:hidden;vertical-align:top;">
            <div class="small">{{ Str::limit($new, 180) ?: '(vazio)' }}</div>
          </td>
          <td>{{ $log->ip_address }}</td>
          <td>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-json-old='@json($old)' data-json-new='@json($new)' onclick="showAuditModal(this)">Ver</button>
            <a href="{{ route('admin.audit_logs.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success">Exportar CSV (filtros)</a>
            <a href="{{ route('admin.audit_logs.export_xlsx') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-primary ms-1">Exportar Excel (XLSX)</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{ $logs->links() }}

  <!-- Modal -->
  <div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalhes do Audit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Antes</h6>
              <pre id="auditOld" class="audit-pre"></pre>
            </div>
            <div class="col-md-6">
              <h6>Depois</h6>
              <pre id="auditNew" class="audit-pre"></pre>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
  function showAuditModal(btn) {
    const old = btn.getAttribute('data-json-old') || '';
    const neu = btn.getAttribute('data-json-new') || '';
    try {
      document.getElementById('auditOld').textContent = old ? JSON.stringify(JSON.parse(old), null, 2) : '(vazio)';
    } catch(e) {
      document.getElementById('auditOld').textContent = old;
    }
    try {
      document.getElementById('auditNew').textContent = neu ? JSON.stringify(JSON.parse(neu), null, 2) : '(vazio)';
    } catch(e) {
      document.getElementById('auditNew').textContent = neu;
    }
    var modal = new bootstrap.Modal(document.getElementById('auditModal'));
    modal.show();
  }
  </script>
  @endpush

  @push('styles')
  <style>
    /* Audit UI small tweaks to match app theme */
    .audit-pre{background:#f8f9fa;padding:12px;border-radius:6px;max-height:52vh;overflow:auto;font-family:Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.9rem}
    .table thead th{vertical-align:middle}
    .table td{vertical-align:middle}
    .btn-warning{background:#f0b400;border-color:#f0b400;color:#111}
    .form-label.small{font-size:.8rem;color:#666}
    .small.text-muted{color:#6c757d}
  </style>
  @endpush

</div>
@endsection

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

  <div class="table-responsive">
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
          <td>
            @if(isset($users) && $users->has($log->user_id))
              {{ $users->get($log->user_id)->name }}
              <br><small>{{ $users->get($log->user_id)->email }}</small>
            @else
              {{ $log->user_id ?? '-' }}
            @endif
          </td>
          <td>{{ $log->role }}</td>
          <td>{{ $log->action }}</td>
          <td>{{ optional($log->auditable_type) ? class_basename($log->auditable_type)." (#{$log->auditable_id})" : '-' }}</td>
          <td style="max-width:400px;overflow:hidden;">
            @php
              $old = $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
              $new = $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
            @endphp
            <div style="max-height:3.6rem;overflow:hidden;white-space:pre-wrap;font-family:monospace;">{{ Str::limit($old, 200) }}</div>
            <div style="max-height:3.6rem;overflow:hidden;white-space:pre-wrap;font-family:monospace;margin-top:6px;">{{ Str::limit($new, 200) }}</div>
            <div style="margin-top:6px;">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-json-old='@json($old)' data-json-new='@json($new)' onclick="showAuditModal(this)">Ver</button>
            </div>
          </td>
          <td>{{ $log->ip_address }}</td>
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
          <h6>Antes</h6>
          <pre id="auditOld" style="background:#f8f9fa;padding:12px;border-radius:4px;max-height:40vh;overflow:auto;"></pre>
          <h6 class="mt-3">Depois</h6>
          <pre id="auditNew" style="background:#f8f9fa;padding:12px;border-radius:4px;max-height:40vh;overflow:auto;"></pre>
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

</div>
@endsection

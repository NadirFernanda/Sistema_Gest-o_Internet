@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Logs de Auditoria</h1>

  <form class="mb-3" method="get" aria-label="Filtrar logs de auditoria">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small">Usuário (nome ou email)</label>
        <input name="user" class="form-control" placeholder="" value="{{ request('user') }}">
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
        <input name="action" class="form-control" placeholder="" value="{{ request('action') }}">
      </div>
      <div class="col-md-6">
        <input name="model" class="form-control" placeholder="" value="{{ request('model') }}">
      </div>
    </div>
  </form>
</div>

<div class="container mt-3">
  <div class="row">
    <div class="col-md-12">
      <p class="small text-muted">Aplicar filtros acima e depois usar os botões para exportar os resultados filtrados.</p>
      <a href="{{ route('admin.audit_logs.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success">Exportar CSV (filtros)</a>
      <a href="{{ route('admin.audit_logs.export_xlsx') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-primary ms-2">Exportar Excel (XLSX)</a>
    </div>
  </div>
  </div>
@endsection

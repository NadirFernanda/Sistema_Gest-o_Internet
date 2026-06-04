@extends('layouts.app')
@section('title', 'Tickets de Suporte — Admin')

@push('styles')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-red:#dc2626;--a-amber:#d97706;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1160px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.85rem 1rem;text-decoration:none;display:block;color:inherit;}
.ap-stat:hover,.ap-stat.active{border-color:var(--a-brand);}
.ap-stat-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;margin-bottom:1.25rem;}
.ap-fg{display:flex;flex-direction:column;gap:.25rem;}
.ap-fg.grow{flex:1;min-width:170px;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}
.ap-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fffdf5;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.b-open{background:#dbeafe;color:#1d4ed8;}.b-prog{background:#fef3c7;color:#b45309;}
.b-resolved{background:#dcfce7;color:#15803d;}.b-closed{background:#f1f5f9;color:#475569;}
.b-urgent{background:#fee2e2;color:#b91c1c;}.b-high{background:#ffedd5;color:#9a3412;}
.b-cat{background:#ede9fe;color:#6d28d9;}
</style>
@endpush

@section('content')
<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Tickets de Suporte</h1>
      <p class="ap-sub">Admin &rsaquo; Pedidos de ajuda de clientes, revendedores e instalações</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  <div class="ap-stats">
    @php
      $statusMap = ['open'=>['b-open','Abertos'],'in_progress'=>['b-prog','Em análise'],'resolved'=>['b-resolved','Resolvidos'],'closed'=>['b-closed','Fechados']];
    @endphp
    @foreach($statusMap as $s => $meta)
      <a href="{{ route('admin.tickets.index', ['status' => $s]) }}"
         class="ap-stat {{ request('status') === $s ? 'active' : '' }}">
        <p class="ap-stat-val">{{ $counts[$s] ?? 0 }}</p>
        <p class="ap-stat-lbl">{{ $meta[1] }}</p>
      </a>
    @endforeach
    <a href="{{ route('admin.tickets.index') }}" class="ap-stat {{ !request('status') ? 'active' : '' }}">
      <p class="ap-stat-val">{{ array_sum($counts) }}</p>
      <p class="ap-stat-lbl">Todos</p>
    </a>
  </div>

  <form method="get" class="ap-filters">
    <div class="ap-fg">
      <label class="ap-label">Categoria</label>
      <select name="category" class="ap-ctrl" style="min-width:180px;">
        <option value="">Todas</option>
        @foreach(\App\Models\Ticket::CATEGORIES as $v => $l)
          <option value="{{ $v }}" @selected(request('category') === $v)>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">Prioridade</label>
      <select name="priority" class="ap-ctrl" style="min-width:130px;">
        <option value="">Todas</option>
        <option value="urgent" @selected(request('priority') === 'urgent')>Urgente</option>
        <option value="high"   @selected(request('priority') === 'high')>Alta</option>
        <option value="normal" @selected(request('priority') === 'normal')>Normal</option>
        <option value="low"    @selected(request('priority') === 'low')>Baixa</option>
      </select>
    </div>
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="Ref., nome, e-mail, telefone, assunto...">
    </div>
    <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
    @if(request()->hasAny(['status','category','priority','q']))
      <a href="{{ route('admin.tickets.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>Ref.</th>
          <th>Assunto</th>
          <th>Cliente</th>
          <th>Categoria</th>
          <th>Estado</th>
          <th>Prior.</th>
          <th>Respostas</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tickets as $ticket)
          <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.tickets.show', $ticket) }}'">
            <td style="font-weight:700;color:var(--a-amber);white-space:nowrap;">{{ $ticket->ref }}</td>
            <td style="max-width:250px;">
              <span style="font-weight:600;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $ticket->subject }}</span>
            </td>
            <td>
              <span style="font-weight:600;font-size:.85rem;">{{ $ticket->name }}</span>
              @if($ticket->phone)<br><span style="color:var(--a-faint);font-size:.78rem;">{{ $ticket->phone }}</span>@endif
            </td>
            <td><span class="badge b-cat">{{ \App\Models\Ticket::CATEGORIES[$ticket->category] ?? $ticket->category }}</span></td>
            <td>
              <span class="badge {{ $ticket->status === 'open' ? 'b-open' : ($ticket->status === 'in_progress' ? 'b-prog' : ($ticket->status === 'resolved' ? 'b-resolved' : 'b-closed')) }}">
                {{ \App\Models\Ticket::statusLabel($ticket->status) }}
              </span>
            </td>
            <td>
              @if($ticket->priority === 'urgent') <span class="badge b-urgent">Urgente</span>
              @elseif($ticket->priority === 'high') <span class="badge b-high">Alta</span>
              @else <span style="color:var(--a-faint);font-size:.82rem;">{{ \App\Models\Ticket::priorityLabel($ticket->priority) }}</span>
              @endif
            </td>
            <td style="text-align:center;color:var(--a-muted);">{{ $ticket->replies_count }}</td>
            <td style="color:var(--a-faint);font-size:.82rem;white-space:nowrap;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="ap-empty"><p style="font-weight:700;color:var(--a-muted);margin:0 0 .3rem;">Nenhum ticket</p><p style="font-size:.82rem;margin:0;">Ajuste os filtros.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $tickets->links() }}</div>
  </div>

</div></div>
@endsection

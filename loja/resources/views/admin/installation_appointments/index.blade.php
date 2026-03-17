@extends('layouts.app')

@section('title', 'Agendamentos de Instalação &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.85rem 1rem;text-decoration:none;display:block;color:inherit;transition:border-color .15s;}
.ap-stat:hover{border-color:var(--a-brand);}
.ap-stat-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-stat.active{border-color:var(--a-brand);}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}
.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}
.bg-purple{background:#ede9fe;color:#6d28d9;}.bg-teal{background:#ccfbf1;color:#0f766e;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
/* Inline status form inside expanded row */
.ap-detail-row td{padding:0!important;border-bottom:1px solid #e8ecf0;}
.ap-detail-inner{padding:.85rem 1rem;background:#fafbff;display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
@media(max-width:600px){.ap-detail-inner{grid-template-columns:1fr;}}
.ap-ctrl label{display:block;font-size:.78rem;font-weight:700;color:#374151;margin-bottom:.3rem;}
.ap-ctrl select,.ap-ctrl textarea{width:100%;padding:.45rem .75rem;border:1px solid var(--a-border);border-radius:7px;font-size:.85rem;font-family:inherit;color:var(--a-text);background:#fff;outline:none;}
.ap-ctrl select:focus,.ap-ctrl textarea:focus{border-color:var(--a-brand);}
.ap-ctrl textarea{resize:vertical;min-height:64px;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-sm{padding:.3rem .7rem;font-size:.78rem;}
.ap-toggle{background:none;border:1px solid var(--a-border);border-radius:7px;padding:.25rem .65rem;font-size:.78rem;font-weight:600;color:var(--a-muted);cursor:pointer;font-family:inherit;transition:border-color .15s;}
.ap-toggle:hover{border-color:var(--a-brand);color:var(--a-text);}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;line-height:1.55;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Agendamentos de Instalação</h1>
      <p class="ap-sub">Admin &rsaquo; Pedidos de agendamento submetidos pelos clientes</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="ap-err">{{ session('error') }}</div>
  @endif

  <div class="ap-note">
    <strong>O que é esta página?</strong> Lista dos pedidos de agendamento de instalação do serviço AngolaWiFi submetidos pelos clientes no site público. Cada pedido é de um cliente que quer instalar o serviço em casa ou na empresa.<br><br>
    <strong>Como gerir um agendamento (passo a passo):</strong><br>
    1. Clique no botão <strong>"Gerir"</strong> à direita do pedido &mdash; abre um formulário inline sem mudar de página.<br>
    2. Altere o <strong>estado</strong> conforme o progresso e adicione uma <strong>nota interna</strong> (ex: "agendado para 20/03 às 10h, técnico João", "cliente não atendeu, ligar de novo").<br>
    3. Clique <strong>"Guardar"</strong> para registar as alterações. O formulário fecha automaticamente.<br><br>
    <strong>Estados de um agendamento:</strong><br>
    &bull; <strong>Pendente</strong>: pedido novo, ainda não foi contactado o cliente.<br>
    &bull; <strong>Contactado</strong>: já falou com o cliente, a confirmar data e hora da visita.<br>
    &bull; <strong>Concluído</strong>: instalação realizada com sucesso.<br>
    &bull; <strong>Cancelado</strong>: pedido cancelado (cliente desistiu ou outro motivo).<br><br>
    <strong>Tipos de instalação:</strong> <strong>Residencial</strong> = habitação particular &nbsp;&bull;&nbsp; <strong>Empresarial</strong> = escritório, loja ou empresa.
  </div>

  {{-- Contadores por estado --}}
  @php
    $filterMeta = [
      'all'       => ['label' => 'Todos',      'val' => array_sum($counts)],
      'pending'   => ['label' => 'Pendentes',  'val' => $counts['pending']],
      'contacted' => ['label' => 'Contactados','val' => $counts['contacted']],
      'done'      => ['label' => 'Concluídos', 'val' => $counts['done']],
      'cancelled' => ['label' => 'Cancelados', 'val' => $counts['cancelled']],
    ];
  @endphp
  <div class="ap-stats">
    @foreach($filterMeta as $key => $meta)
      <a href="{{ route('admin.appointments.index', ['status' => $key]) }}"
         class="ap-stat {{ $status === $key ? 'active' : '' }}">
        <div class="ap-stat-val">{{ $meta['val'] }}</div>
        <div class="ap-stat-lbl">{{ $meta['label'] }}</div>
      </a>
    @endforeach
  </div>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>Tipo</th>
          <th>Estado</th>
          <th>Data</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($appointments as $appt)
          @php
            $typeColors  = ['familia'=>'bg-blue','empresa'=>'bg-purple','instituicao'=>'bg-teal'];
            $statColors  = ['pending'=>'bg-amber','contacted'=>'bg-blue','done'=>'bg-green','cancelled'=>'bg-red'];
            $tcls = $typeColors[$appt->type]    ?? 'bg-gray';
            $scls = $statColors[$appt->status]  ?? 'bg-gray';
          @endphp
          <tr>
            <td class="dim">{{ $appt->id }}</td>
            <td style="font-weight:600;">{{ $appt->name }}</td>
            <td>
              <a href="https://wa.me/{{ preg_replace('/\D/','',$appt->phone) }}"
                 target="_blank" rel="noopener"
                 style="color:inherit;text-decoration:none;">
                {{ $appt->phone }}
              </a>
            </td>
            <td><span class="badge {{ $tcls }}">{{ \App\Models\InstallationAppointment::typeLabel($appt->type) }}</span></td>
            <td><span class="badge {{ $scls }}">{{ \App\Models\InstallationAppointment::statusLabel($appt->status) }}</span></td>
            <td class="dim">{{ $appt->created_at->format('d/m/Y H:i') }}</td>
            <td>
              <button class="ap-toggle" onclick="toggleDetail({{ $appt->id }})">Gerir</button>
            </td>
          </tr>
          {{-- Detail / status update row --}}
          <tr class="ap-detail-row" id="detail-{{ $appt->id }}" style="display:none;">
            <td colspan="7">
              <div class="ap-detail-inner">
                {{-- Message --}}
                <div>
                  <p style="font-size:.78rem;font-weight:700;color:#374151;margin:0 0 .35rem;">Mensagem do cliente</p>
                  <p style="font-size:.84rem;color:#4b5563;margin:0;white-space:pre-wrap;">{{ $appt->message ?: '—' }}</p>
                </div>
                {{-- Update form --}}
                <form method="POST"
                      action="{{ route('admin.appointments.status', $appt->id) }}"
                      style="display:flex;flex-direction:column;gap:.6rem;">
                  @csrf
                  @method('PATCH')
                  <div class="ap-ctrl">
                    <label>Estado</label>
                    <select name="status">
                      <option value="pending"   {{ $appt->status==='pending'   ? 'selected':'' }}>Pendente</option>
                      <option value="contacted" {{ $appt->status==='contacted' ? 'selected':'' }}>Contactado</option>
                      <option value="done"      {{ $appt->status==='done'      ? 'selected':'' }}>Concluído</option>
                      <option value="cancelled" {{ $appt->status==='cancelled' ? 'selected':'' }}>Cancelado</option>
                    </select>
                  </div>
                  <div class="ap-ctrl">
                    <label>Notas internas</label>
                    <textarea name="admin_notes" placeholder="Observações…">{{ $appt->admin_notes }}</textarea>
                  </div>
                  <div>
                    <button type="submit" class="ap-btn ap-btn-primary ap-btn-sm">Guardar</button>
                  </div>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhum agendamento encontrado</p>
                <p class="ap-empty-s">Os pedidos submetidos pelos clientes aparecem aqui.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    @if($appointments->hasPages())
      <div class="ap-pager">{{ $appointments->links() }}</div>
    @endif
  </div>

</div></div>

@push('scripts')
<script>
function toggleDetail(id) {
  var row = document.getElementById('detail-' + id);
  if (row) {
    row.style.display = row.style.display === 'none' ? '' : 'none';
  }
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<style>
/* ── Admin Voucher Plans ─────────────────────────────── */
:root {
  --a-bg:     #f4f6f9;
  --a-surf:   #ffffff;
  --a-border: #dde2ea;
  --a-text:   #1a202c;
  --a-muted:  #64748b;
  --a-faint:  #9aa5b4;
  --a-blue:   #3b82f6;
  --a-indigo: #4f46e5;
  --a-green:  #16a34a;
  --a-amber:  #d97706;
  --a-red:    #dc2626;
  --a-purple: #7c3aed;
}
.ap { font-family: Inter, system-ui, sans-serif; background: var(--a-bg); min-height: 60vh; padding: 2rem 0 4rem; color: var(--a-text); }
.ap-wrap { max-width: 1000px; margin: 0 auto; padding: 0 1.5rem; }
.ap-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.ap-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .15rem; letter-spacing: -.02em; }
.ap-topbar .ap-sub { font-size: .78rem; color: var(--a-faint); }
.ap-back { font-size: .82rem; font-weight: 600; color: var(--a-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--a-border); border-radius: 7px; background: var(--a-surf); }
.ap-back:hover { background: var(--a-border); color: var(--a-text); }
.ap-nav { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1.75rem; }
.ap-nav a { font-size: .8rem; font-weight: 600; padding: .38rem .85rem; border-radius: 7px; border: 1px solid var(--a-border); background: var(--a-surf); color: var(--a-muted); text-decoration: none; }
.ap-nav a:hover, .ap-nav a.here { background: #eef2ff; border-color: #c7d2fe; color: var(--a-indigo); }
.ap-ok  { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--a-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.ap-err { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--a-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.ap-sec { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--a-faint); margin: 0 0 .85rem; display: flex; align-items: center; gap: .6rem; }
.ap-sec::after { content: ''; flex: 1; height: 1px; background: var(--a-border); }
/* plan cards grid */
.vp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 2.5rem; }
.vp-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 12px; overflow: hidden; }
.vp-card-head { padding: 1.1rem 1.25rem .8rem; border-bottom: 1px solid var(--a-border); display: flex; justify-content: space-between; align-items: center; }
.vp-card-title { font-weight: 800; font-size: 1rem; margin: 0; }
.vp-badge-active   { background: #dcfce7; color: #15803d; font-size: .72rem; font-weight: 700; padding: .2rem .55rem; border-radius: 99px; }
.vp-badge-inactive { background: #f1f5f9; color: var(--a-muted); font-size: .72rem; font-weight: 700; padding: .2rem .55rem; border-radius: 99px; }
.vp-card-body { padding: 1rem 1.25rem; }
.vp-row { display: flex; justify-content: space-between; font-size: .84rem; margin-bottom: .45rem; }
.vp-row-label { color: var(--a-muted); }
.vp-row-value { font-weight: 600; color: var(--a-text); }
.vp-profit { color: var(--a-green); }
.vp-card-foot { padding: .75rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--a-border); display: flex; gap: .5rem; justify-content: flex-end; }
/* form card */
.ap-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 11px; padding: 1.4rem; }
.ap-card-title { font-size: .95rem; font-weight: 800; margin: 0 0 1rem; color: var(--a-text); }
.ap-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
@media (max-width: 540px) { .ap-grid-2 { grid-template-columns: 1fr; } }
.ap-field { margin-bottom: .65rem; }
.ap-label { display: block; font-size: .77rem; font-weight: 600; color: #374151; margin-bottom: .3rem; }
.ap-label sup { color: var(--a-red); }
.ap-ctrl { width: 100%; box-sizing: border-box; padding: .55rem .75rem; border: 1.5px solid var(--a-border); border-radius: 8px; font-size: .875rem; color: var(--a-text); background: #f8fafc; font-family: inherit; outline: none; transition: border-color .15s; }
.ap-ctrl:focus { border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: #fff; }
.ap-hint { font-size: .72rem; color: var(--a-faint); margin-top: .2rem; }
.ap-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.1rem; border-radius: 8px; font-size: .85rem; font-weight: 700; border: none; cursor: pointer; font-family: inherit; text-decoration: none; white-space: nowrap; }
.ap-btn-primary { background: var(--a-indigo); color: #fff; }
.ap-btn-primary:hover { background: #4338ca; }
.ap-btn-outline { background: var(--a-surf); color: var(--a-muted); border: 1.5px solid var(--a-border); }
.ap-btn-outline:hover { background: var(--a-border); color: var(--a-text); }
.ap-btn-sm { padding: .3rem .7rem; font-size: .78rem; }
.ap-btn-warning { background: #fff7ed; color: var(--a-amber); border: 1.5px solid #fed7aa; }
.ap-btn-warning:hover { background: #ffedd5; }
/* edit modal */
.vp-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center; }
.vp-modal-overlay.open { display: flex; }
.vp-modal { background: #fff; border-radius: 14px; padding: 1.75rem; width: 100%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.18); max-height: 90vh; overflow-y: auto; }
.vp-modal h3 { margin: 0 0 1.25rem; font-size: 1.05rem; font-weight: 800; }
.vp-modal-close { float: right; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--a-muted); margin-top: -.2rem; }
</style>

<div class="ap">
<div class="ap-wrap">

  {{-- Top bar --}}
  <div class="ap-topbar">
    <div>
      <h1>Planos de Voucher</h1>
      <p class="ap-sub">Gerir planos disponíveis no canal de revenda</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">← Dashboard</a>
  </div>

  {{-- Nav --}}
  <nav class="ap-nav">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.autovenda.index') }}">Recargas</a>
    <a href="{{ route('admin.wifi_codes.index') }}">Códigos WiFi</a>
    <a href="{{ route('admin.voucher_plans.index') }}" class="here">Planos Voucher</a>
    <a href="{{ route('admin.resellers.index') }}">Revendedores</a>
    <a href="{{ route('admin.equipment.orders.index') }}">Encomendas</a>
    <a href="{{ route('admin.equipment.products.index') }}">Produtos</a>
    <a href="{{ route('admin.family_requests.index') }}">Planos</a>
    <a href="{{ route('admin.site_stats.index') }}">Estatísticas</a>
    <a href="{{ route('admin.reports') }}">Relatórios</a>
  </nav>

  {{-- Flash --}}
  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif
  @if(session('error') || $errors->any())
    <div class="ap-err">
      {{ session('error') }}
      @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
  @endif

  {{-- Plans grid --}}
  <p class="ap-sec">Planos Actuais ({{ $plans->count() }})</p>

  @if($plans->isEmpty())
    <div style="background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:2rem;text-align:center;color:var(--a-muted);margin-bottom:2rem;">
      Nenhum plano criado. Use o formulário abaixo para adicionar.
    </div>
  @else
    <div class="vp-grid">
      @foreach($plans as $plan)
      <div class="vp-card">
        <div class="vp-card-head">
          <h3 class="vp-card-title">{{ $plan->name }}</h3>
          @if($plan->active)
            <span class="vp-badge-active">Activo</span>
          @else
            <span class="vp-badge-inactive">Inactivo</span>
          @endif
        </div>
        <div class="vp-card-body">
          <div class="vp-row"><span class="vp-row-label">Slug</span><span class="vp-row-value" style="font-family:monospace;font-size:.8rem;">{{ $plan->slug }}</span></div>
          <div class="vp-row"><span class="vp-row-label">Validade</span><span class="vp-row-value">{{ $plan->validity_label }}</span></div>
          <div class="vp-row"><span class="vp-row-label">Velocidade</span><span class="vp-row-value">{{ $plan->speed_label ?? '—' }}</span></div>
          <div class="vp-row"><span class="vp-row-label">Preço Público</span><span class="vp-row-value">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} Kz</span></div>
          <div class="vp-row"><span class="vp-row-label">Preço Revenda</span><span class="vp-row-value">{{ number_format($plan->price_reseller_aoa, 0, ',', '.') }} Kz</span></div>
          <div class="vp-row"><span class="vp-row-label">Lucro/Voucher</span><span class="vp-row-value vp-profit">{{ number_format($plan->profitPerVoucher(), 0, ',', '.') }} Kz ({{ $plan->marginPercent() }}%)</span></div>
          <div class="vp-row"><span class="vp-row-label">Stock Disponível</span><span class="vp-row-value" style="color:var(--a-blue);">{{ number_format($plan->availableStock()) }} vouchers</span></div>
          <div class="vp-row"><span class="vp-row-label">Ordem</span><span class="vp-row-value">{{ $plan->sort_order }}</span></div>
        </div>
        <div class="vp-card-foot">
          <button type="button" class="ap-btn ap-btn-outline ap-btn-sm" onclick="openEdit({{ $plan->id }}, '{{ e($plan->name) }}', '{{ e($plan->validity_label) }}', {{ $plan->validity_minutes }}, '{{ e($plan->speed_label) }}', {{ $plan->price_public_aoa }}, {{ $plan->price_reseller_aoa }}, {{ $plan->sort_order }})">
            ✏️ Editar
          </button>
          <form method="POST" action="{{ route('admin.voucher_plans.toggle', $plan) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" class="ap-btn ap-btn-warning ap-btn-sm">
              {{ $plan->active ? '🚫 Desactivar' : '✅ Activar' }}
            </button>
          </form>
        </div>
      </div>
      @endforeach
    </div>
  @endif

  {{-- Create form --}}
  <p class="ap-sec">Adicionar Novo Plano</p>
  <div class="ap-card" style="margin-bottom:2rem;">
    <h2 class="ap-card-title">Novo Plano de Voucher</h2>
    <form method="POST" action="{{ route('admin.voucher_plans.store') }}">
      @csrf
      <div class="ap-grid-2">
        <div class="ap-field">
          <label class="ap-label">Slug <sup>*</sup></label>
          <input type="text" name="slug" class="ap-ctrl" placeholder="ex: diario" value="{{ old('slug') }}" pattern="[a-z0-9_-]+" required>
          <div class="ap-hint">Identificador único, só minúsculas, sem espaços.</div>
        </div>
        <div class="ap-field">
          <label class="ap-label">Nome <sup>*</sup></label>
          <input type="text" name="name" class="ap-ctrl" placeholder="ex: Plano Diário" value="{{ old('name') }}" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Rótulo de Validade <sup>*</sup></label>
          <input type="text" name="validity_label" class="ap-ctrl" placeholder="ex: 24 horas" value="{{ old('validity_label') }}" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Duração (minutos) <sup>*</sup></label>
          <input type="number" name="validity_minutes" class="ap-ctrl" placeholder="1440" min="1" value="{{ old('validity_minutes') }}" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Velocidade</label>
          <input type="text" name="speed_label" class="ap-ctrl" placeholder="ex: Até 10 Mbps" value="{{ old('speed_label') }}">
        </div>
        <div class="ap-field">
          <label class="ap-label">Ordem de Exibição <sup>*</sup></label>
          <input type="number" name="sort_order" class="ap-ctrl" value="{{ old('sort_order', $plans->count() + 1) }}" min="0" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Preço Público (Kz) <sup>*</sup></label>
          <input type="number" name="price_public_aoa" class="ap-ctrl" placeholder="500" min="0" value="{{ old('price_public_aoa') }}" required>
          <div class="ap-hint">Preço que o cliente final paga.</div>
        </div>
        <div class="ap-field">
          <label class="ap-label">Preço de Revenda (Kz) <sup>*</sup></label>
          <input type="number" name="price_reseller_aoa" class="ap-ctrl" placeholder="300" min="0" value="{{ old('price_reseller_aoa') }}" required>
          <div class="ap-hint">Preço que o revendedor paga.</div>
        </div>
      </div>
      <div style="margin-top:1rem;">
        <button type="submit" class="ap-btn ap-btn-primary">＋ Criar Plano</button>
      </div>
    </form>
  </div>

</div>
</div>

{{-- Edit Modal --}}
<div class="vp-modal-overlay" id="editModal">
  <div class="vp-modal">
    <h3>Editar Plano <button class="vp-modal-close" onclick="closeEdit()">×</button></h3>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="ap-grid-2">
        <div class="ap-field">
          <label class="ap-label">Nome <sup>*</sup></label>
          <input type="text" name="name" id="edit_name" class="ap-ctrl" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Rótulo de Validade <sup>*</sup></label>
          <input type="text" name="validity_label" id="edit_validity_label" class="ap-ctrl" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Duração (minutos) <sup>*</sup></label>
          <input type="number" name="validity_minutes" id="edit_validity_minutes" class="ap-ctrl" min="1" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Velocidade</label>
          <input type="text" name="speed_label" id="edit_speed_label" class="ap-ctrl">
        </div>
        <div class="ap-field">
          <label class="ap-label">Preço Público (Kz) <sup>*</sup></label>
          <input type="number" name="price_public_aoa" id="edit_price_public" class="ap-ctrl" min="0" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Preço de Revenda (Kz) <sup>*</sup></label>
          <input type="number" name="price_reseller_aoa" id="edit_price_reseller" class="ap-ctrl" min="0" required>
        </div>
        <div class="ap-field">
          <label class="ap-label">Ordem de Exibição <sup>*</sup></label>
          <input type="number" name="sort_order" id="edit_sort_order" class="ap-ctrl" min="0" required>
        </div>
      </div>
      <div style="margin-top:1rem; display:flex; gap:.6rem;">
        <button type="submit" class="ap-btn ap-btn-primary">💾 Guardar</button>
        <button type="button" class="ap-btn ap-btn-outline" onclick="closeEdit()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, name, validityLabel, validityMinutes, speedLabel, pricePublic, priceReseller, sortOrder) {
  document.getElementById('edit_name').value            = name;
  document.getElementById('edit_validity_label').value  = validityLabel;
  document.getElementById('edit_validity_minutes').value= validityMinutes;
  document.getElementById('edit_speed_label').value     = speedLabel;
  document.getElementById('edit_price_public').value    = pricePublic;
  document.getElementById('edit_price_reseller').value  = priceReseller;
  document.getElementById('edit_sort_order').value      = sortOrder;
  document.getElementById('editForm').action = '/admin/planos-voucher/' + id;
  document.getElementById('editModal').classList.add('open');
}
function closeEdit() {
  document.getElementById('editModal').classList.remove('open');
}
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEdit();
});
</script>
@endsection

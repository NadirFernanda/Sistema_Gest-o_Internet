@extends('layouts.app')

@section('content')
<style>
:root {
  --a-bg:     #f4f6f9;
  --a-surf:   #ffffff;
  --a-border: #dde2ea;
  --a-text:   #1a202c;
  --a-muted:  #64748b;
  --a-faint:  #9aa5b4;
  --a-brand:  #f7b500;
  --a-green:  #16a34a;
  --a-red:    #dc2626;
}
.ap { font-family: Inter, system-ui, sans-serif; background: var(--a-bg); min-height: 60vh; padding: 2rem 0 4rem; color: var(--a-text); }
.ap-wrap { max-width: 720px; margin: 0 auto; padding: 0 1.5rem; }
.ap-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.ap-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .15rem; letter-spacing: -.02em; }
.ap-topbar .ap-sub { font-size: .78rem; color: var(--a-faint); }
.ap-back { font-size: .82rem; font-weight: 600; color: var(--a-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--a-border); border-radius: 7px; background: var(--a-surf); }
.ap-back:hover { background: var(--a-border); color: var(--a-text); }
.ap-ok  { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--a-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1.25rem; }
.ap-err { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--a-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1.25rem; }

.ns-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 14px; overflow: hidden; margin-bottom: 1.5rem; }
.ns-card-head { background: #1a202c; color: #fff; padding: 1.1rem 1.5rem; }
.ns-card-head h2 { font-size: 1rem; font-weight: 700; margin: 0 0 .2rem; }
.ns-card-head p  { font-size: .78rem; color: rgba(255,255,255,.55); margin: 0; }

.ns-row { display: grid; grid-template-columns: 1fr 130px; gap: 1rem; align-items: center; padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--a-border); }
.ns-row:last-child { border-bottom: none; }
.ns-label { font-weight: 600; font-size: .9rem; color: var(--a-text); margin-bottom: .25rem; }
.ns-desc  { font-size: .78rem; color: var(--a-muted); }
.ns-input-wrap { display: flex; align-items: center; gap: .4rem; }
.ns-input { width: 80px; text-align: right; border: 1.5px solid var(--a-border); border-radius: 8px; padding: .5rem .7rem; font-size: 1rem; font-weight: 700; color: var(--a-text); font-variant-numeric: tabular-nums; }
.ns-input:focus { outline: none; border-color: var(--a-brand); box-shadow: 0 0 0 3px rgba(247,181,0,.15); }
.ns-unit { font-size: .85rem; font-weight: 700; color: var(--a-muted); }

.ns-actions { padding: 1.25rem 1.5rem; display: flex; justify-content: flex-end; }
.btn-save { background: var(--a-brand); color: #fff; border: none; padding: .65rem 1.5rem; border-radius: 9px; font-size: .9rem; font-weight: 700; cursor: pointer; }
.btn-save:hover { background: #d9920a; }

.ns-info { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; padding: .85rem 1.25rem; border-radius: 8px; font-size: .82rem; color: #78350f; margin-bottom: 1.5rem; line-height: 1.6; }
</style>

<div class="ap">
  <div class="ap-wrap">

    <div class="ap-topbar">
      <div>
        <h1>Percentagens da Rede</h1>
        <div class="ap-sub">Ajuste os descontos e metas da rede de revendedores</div>
      </div>
      <a href="{{ route('admin.resellers.index') }}" class="ap-back">← Revendedores</a>
    </div>

    @if(session('status'))
      <div class="ap-ok">{{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="ap-err">{{ $errors->first() }}</div>
    @endif

    <div class="ns-info">
      <strong>Nota:</strong> Estas percentagens afectam todos os novos cálculos de descontos, bónus e metas.
      As alterações têm efeito imediato. Os valores já registados nos revendedores existentes não são alterados retroactivamente.
    </div>

    <form method="POST" action="{{ route('admin.network-settings.update') }}">
      @csrf @method('PUT')

      <div class="ns-card">
        <div class="ns-card-head">
          <h2>Descontos por Modo de Revendedor</h2>
          <p>Percentagem de desconto sobre o preço público aplicada na compra de vouchers</p>
        </div>

        @foreach($settings->whereIn('key', ['mode_own_discount_percent', 'mode_angolawifi_discount_percent']) as $i => $s)
        <div class="ns-row">
          <div>
            <input type="hidden" name="settings[{{ $i }}][id]" value="{{ $s->id }}">
            <div class="ns-label">{{ $s->label }}</div>
            @if($s->description)
              <div class="ns-desc">{{ $s->description }}</div>
            @endif
          </div>
          <div class="ns-input-wrap">
            <input type="number" class="ns-input" name="settings[{{ $i }}][value]"
                   value="{{ number_format($s->value, 1) }}" min="0" max="100" step="0.5">
            <span class="ns-unit">{{ $s->unit }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <div class="ns-card">
        <div class="ns-card-head">
          <h2>Bónus e Metas</h2>
          <p>Percentagens usadas nos cálculos de bónus de instalação e metas mensais</p>
        </div>

        @php $offset = $settings->whereIn('key', ['mode_own_discount_percent', 'mode_angolawifi_discount_percent'])->count(); @endphp
        @foreach($settings->whereIn('key', ['bonus_install_percent', 'monthly_target_percent']) as $j => $s)
        <div class="ns-row">
          <div>
            <input type="hidden" name="settings[{{ $offset + $j }}][id]" value="{{ $s->id }}">
            <div class="ns-label">{{ $s->label }}</div>
            @if($s->description)
              <div class="ns-desc">{{ $s->description }}</div>
            @endif
          </div>
          <div class="ns-input-wrap">
            <input type="number" class="ns-input" name="settings[{{ $offset + $j }}][value]"
                   value="{{ number_format($s->value, 1) }}" min="0" max="100" step="0.5">
            <span class="ns-unit">{{ $s->unit }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <div class="ns-actions">
        <button type="submit" class="btn-save">Guardar Alterações</button>
      </div>
    </form>

  </div>
</div>
@endsection

@extends('layouts.app')

@push('styles')
<style>
.rv-page{min-height:80vh;background:#f8fafc;padding:2.5rem 1rem 6rem;}
.rv-wrap{max-width:1000px;margin:0 auto;}
.rv-topbar{background:#fff;border-radius:1rem;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.rv-back{padding:.5rem 1.1rem;border:1.5px solid #e2e8f0;border-radius:.6rem;background:#fff;font-size:.85rem;font-weight:600;color:#64748b;text-decoration:none;}
.rv-back:hover{border-color:#f7b500;color:#92400e;background:#fffbeb;}
.rv-alert-ok{background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;border-radius:.75rem;padding:.85rem 1.1rem;margin-bottom:1rem;font-size:.92rem;display:flex;gap:.5rem;align-items:flex-start;}
.rv-alert-err{background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c;border-radius:.75rem;padding:.85rem 1.1rem;margin-bottom:1rem;font-size:.92rem;display:flex;gap:.5rem;align-items:flex-start;}
.rv-panel{background:#fff;border-radius:1rem;box-shadow:0 2px 10px rgba(0,0,0,.055);padding:1.5rem;margin-bottom:1.25rem;}
.rv-panel-title{font-size:1rem;font-weight:800;color:#0f172a;padding-bottom:.65rem;margin-bottom:1rem;border-bottom:1.5px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.5rem;}
.rv-label{display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.rv-input{width:100%;box-sizing:border-box;padding:.6rem .85rem;border:1.5px solid #e2e8f0;border-radius:.6rem;font-size:.88rem;color:#0f172a;background:#f8fafc;outline:none;transition:border-color .15s;}
.rv-input:focus{border-color:#f7b500;background:#fff;}
.rv-input-err{border-color:#dc2626!important;}
.rv-field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;}
.rv-btn{display:inline-flex;align-items:center;gap:.35rem;padding:.5rem 1.1rem;border-radius:.55rem;font-size:.85rem;font-weight:700;border:none;cursor:pointer;transition:background .15s;text-decoration:none;}
.rv-btn-primary{background:#f7b500;color:#1a202c;}.rv-btn-primary:hover{background:#e0a800;}
.rv-btn-sm{padding:.35rem .75rem;font-size:.78rem;}
.rv-btn-danger{background:#fee2e2;color:#b91c1c;border:1.5px solid #fecaca;}.rv-btn-danger:hover{background:#fecaca;}
.rv-btn-outline{background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;}.rv-btn-outline:hover{background:#e2e8f0;}
.rv-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;}
.rv-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}

/* Staff cards */
.rv-staff-grid{display:flex;flex-direction:column;gap:.75rem;}
.rv-staff-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:.9rem;padding:1.1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;transition:border-color .15s;}
.rv-staff-card.suspended{opacity:.65;border-style:dashed;}
.rv-staff-card:hover{border-color:#f7b500;}
.rv-staff-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;font-size:1rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rv-staff-avatar.suspended{background:#94a3b8;}
.rv-staff-name{font-size:.95rem;font-weight:700;color:#0f172a;}
.rv-staff-phone{font-size:.82rem;color:#64748b;}
.rv-badge{display:inline-flex;align-items:center;padding:.15rem .55rem;border-radius:999px;font-size:.72rem;font-weight:700;}
.rv-badge-green{background:#dcfce7;color:#15803d;}
.rv-badge-gray{background:#f1f5f9;color:#64748b;}
.rv-badge-red{background:#fee2e2;color:#b91c1c;}

/* Quota bar */
.rv-quota{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#64748b;min-width:160px;}
.rv-quota-bar{flex:1;height:6px;background:#f1f5f9;border-radius:999px;overflow:hidden;}
.rv-quota-fill{height:6px;border-radius:999px;background:#f7b500;}

/* Stats mini */
.rv-mini-stats{display:flex;gap:.6rem;flex-wrap:wrap;}
.rv-mini-stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.3rem .7rem;font-size:.78rem;color:#374151;}
.rv-mini-stat strong{color:#0f172a;}

/* PIN form inline */
.rv-pin-form{display:flex;gap:.45rem;align-items:center;flex-wrap:wrap;}
.rv-pin-input{width:90px;padding:.38rem .6rem;border:1.5px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;text-align:center;font-family:monospace;letter-spacing:.1em;background:#f8fafc;}
.rv-pin-input:focus{outline:none;border-color:#f7b500;}

/* Info box */
.rv-info{background:#fffbeb;border:1.5px solid #fde68a;border-left:4px solid #f7b500;border-radius:.7rem;padding:.85rem 1rem;font-size:.85rem;color:#78350f;margin-bottom:1.25rem;line-height:1.55;}

/* Add form */
.rv-add-form{background:#f8fafc;border:1.5px dashed #e2e8f0;border-radius:.9rem;padding:1.25rem;}

@media(max-width:640px){
  .rv-grid-3{grid-template-columns:1fr 1fr;}
  .rv-grid-2{grid-template-columns:1fr;}
  .rv-staff-card{flex-direction:column;align-items:flex-start;}
}
@media(max-width:440px){.rv-grid-3{grid-template-columns:1fr;}}
</style>
@endpush

@section('content')
<div class="rv-page">
<div class="rv-wrap">

  <div class="rv-topbar">
    <div>
      <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">Equipa de Revenda</div>
      <div style="font-size:.82rem;color:#64748b;">{{ $application->full_name }}</div>
    </div>
    <a href="{{ route('reseller.panel') }}" class="rv-back">← Voltar ao painel</a>
  </div>

  @if(session('success'))
    <div class="rv-alert-ok"><span>✅</span><div>{{ session('success') }}</div></div>
  @endif
  @if(session('error'))
    <div class="rv-alert-err"><span>⚠️</span><div>{{ session('error') }}</div></div>
  @endif
  @if($errors->any())
    <div class="rv-alert-err"><span>⚠️</span><div>{{ $errors->first() }}</div></div>
  @endif

  <div class="rv-info">
    <strong>📋 Como funciona a equipa de revenda?</strong><br>
    Pode registar até <strong>{{ \App\Models\ResellerStaff::MAX_PER_RESELLER }} membros</strong> de equipa. Cada membro faz login em
    <strong><a href="{{ route('staff.panel') }}" style="color:#92400e;" target="_blank">/painel-equipa</a></strong>
    com o seu número de telemóvel + PIN que define aqui.<br>
    Os membros vendem vouchers do seu stock — o sistema regista quem vendeu o quê.
    A reconciliação financeira entre si e a sua equipa é feita directamente por si.
  </div>

  {{-- Membros existentes --}}
  <div class="rv-panel">
    <div class="rv-panel-title">
      <span>👥 Membros da equipa ({{ $staff->count() }}/{{ \App\Models\ResellerStaff::MAX_PER_RESELLER }})</span>
    </div>

    @if($staff->isEmpty())
      <p style="color:#94a3b8;font-size:.9rem;text-align:center;padding:1.5rem 0;">
        Ainda não tem membros na equipa. Registe o primeiro abaixo.
      </p>
    @else
      <div class="rv-staff-grid">
        @foreach($staff as $member)
          @php
            $stats   = $salesStats[$member->id]   ?? null;
            $monthly = $monthlySales[$member->id]  ?? null;
            $totalSold = $stats->sold_count ?? 0;
            $totalAoa  = $stats->sales_aoa  ?? 0;
            $thisMon   = $monthly->sold_this_month ?? 0;
          @endphp
          <div class="rv-staff-card {{ $member->status === 'suspended' ? 'suspended' : '' }}">
            <div style="display:flex;align-items:center;gap:.85rem;flex:1;min-width:200px;">
              <div class="rv-staff-avatar {{ $member->status === 'suspended' ? 'suspended' : '' }}">
                {{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}
              </div>
              <div>
                <div class="rv-staff-name">{{ $member->full_name }}</div>
                <div class="rv-staff-phone">{{ $member->phone }}{{ $member->email ? ' · ' . $member->email : '' }}</div>
                <div style="margin-top:.35rem;">
                  @if($member->status === 'active')
                    <span class="rv-badge rv-badge-green">✔ Activo</span>
                  @else
                    <span class="rv-badge rv-badge-gray">⏸ Suspenso</span>
                  @endif
                </div>
              </div>
            </div>

            {{-- Mini stats --}}
            <div class="rv-mini-stats">
              <div class="rv-mini-stat">Este mês: <strong>{{ $thisMon }}</strong></div>
              <div class="rv-mini-stat">Total: <strong>{{ $totalSold }}</strong> vouchers</div>
              @if($totalAoa > 0)
                <div class="rv-mini-stat">Receita: <strong>{{ number_format($totalAoa, 0, ',', '.') }} Kz</strong></div>
              @endif
            </div>

            {{-- Acções --}}
            <div style="display:flex;flex-direction:column;gap:.5rem;min-width:160px;">

              {{-- Reset PIN --}}
              <form action="{{ route('reseller.staff.pin', $member) }}" method="POST">
                @csrf
                <div class="rv-pin-form">
                  <input type="number" name="pin" class="rv-pin-input"
                         placeholder="novo PIN" min="1000" max="999999"
                         style="width:95px;" required>
                  <button type="submit" class="rv-btn rv-btn-outline rv-btn-sm">Novo PIN</button>
                </div>
              </form>

              <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
                {{-- Toggle activo/suspenso --}}
                <form action="{{ route('reseller.staff.toggle', $member) }}" method="POST" style="display:inline;">
                  @csrf @method('PATCH')
                  <button type="submit" class="rv-btn rv-btn-outline rv-btn-sm">
                    {{ $member->status === 'active' ? '⏸ Suspender' : '▶ Activar' }}
                  </button>
                </form>

                {{-- Remover --}}
                <form action="{{ route('reseller.staff.destroy', $member) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Remover {{ $member->full_name }} da equipa?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="rv-btn rv-btn-danger rv-btn-sm">✕ Remover</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Adicionar membro --}}
  @if($staff->count() < \App\Models\ResellerStaff::MAX_PER_RESELLER)
  <div class="rv-panel">
    <div class="rv-panel-title">
      <span>➕ Registar novo membro</span>
      <span style="font-size:.78rem;font-weight:500;color:#64748b;">
        {{ \App\Models\ResellerStaff::MAX_PER_RESELLER - $staff->count() }} lugar(es) disponível(eis)
      </span>
    </div>

    <form action="{{ route('reseller.staff.store') }}" method="POST" class="rv-add-form">
      @csrf

      <div class="rv-grid-3" style="margin-bottom:.85rem;">
        <div>
          <label class="rv-label" for="s-name">Nome completo *</label>
          <input id="s-name" name="full_name" type="text" class="rv-input {{ $errors->has('full_name') ? 'rv-input-err' : '' }}"
                 value="{{ old('full_name') }}" placeholder="Nome do membro" required>
          @error('full_name')<p class="rv-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="rv-label" for="s-phone">Telemóvel *</label>
          <input id="s-phone" name="phone" type="tel" class="rv-input {{ $errors->has('phone') ? 'rv-input-err' : '' }}"
                 value="{{ old('phone') }}" placeholder="9XXXXXXXX" required>
          @error('phone')<p class="rv-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="rv-label" for="s-email">Email (opcional)</label>
          <input id="s-email" name="email" type="email" class="rv-input {{ $errors->has('email') ? 'rv-input-err' : '' }}"
                 value="{{ old('email') }}" placeholder="email@exemplo.ao">
          @error('email')<p class="rv-field-error">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="rv-grid-2" style="margin-bottom:.85rem;">
        <div>
          <label class="rv-label" for="s-pin">PIN de acesso * <span style="font-weight:400;color:#64748b;">(4 a 6 dígitos — partilhe com o membro)</span></label>
          <input id="s-pin" name="pin" type="text" inputmode="numeric"
                 class="rv-input {{ $errors->has('pin') ? 'rv-input-err' : '' }}"
                 value="{{ old('pin') }}" placeholder="ex: 1234" maxlength="6"
                 style="font-family:monospace;letter-spacing:.15em;" required>
          @error('pin')<p class="rv-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="rv-label" for="s-notes">Notas (opcional)</label>
          <input id="s-notes" name="notes" type="text" class="rv-input"
                 value="{{ old('notes') }}" placeholder="ex: zona Rangel, turno manhã">
        </div>
      </div>

      <button type="submit" class="rv-btn rv-btn-primary">➕ Adicionar à equipa</button>
    </form>
  </div>
  @else
    <div class="rv-alert-err">
      <span>🚫</span>
      <div>Limite de <strong>{{ \App\Models\ResellerStaff::MAX_PER_RESELLER }} membros</strong> atingido. Remova ou suspenda um membro para libertar um lugar.</div>
    </div>
  @endif

  {{-- Link para painel da equipa --}}
  <div style="text-align:center;margin-top:1.5rem;">
    <a href="{{ route('staff.panel') }}" target="_blank"
       style="font-size:.85rem;color:#64748b;text-decoration:underline;">
      Abrir painel da equipa →
    </a>
  </div>

</div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de stock de códigos WiFi">
  <div class="container">

    <h2>Stock de Códigos WiFi</h2>
    <p class="lead">
      Os códigos WiFi são fornecidos pela operadora e importados aqui <strong>separados por plano</strong>
      (Diário, Semanal, Mensal) antes de serem atribuídos automaticamente às vendas correspondentes.
    </p>

    {{-- Alertas --}}
    @if(session('success'))
      <div class="checkout-errors" style="background:#f0fdf4;border-color:#86efac;color:#166534;margin-bottom:1rem;">
        <p>{{ session('success') }}</p>
      </div>
    @endif
    @if($errors->any())
      <div class="checkout-errors" style="margin-bottom:1rem;">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
      </div>
    @endif

    {{-- Contadores de stock por plano --}}
    @php
      $available = $statusCounts['available'] ?? 0;
      $used      = $statusCounts['used']      ?? 0;
      $reserved  = $statusCounts['reserved']  ?? 0;
      $planLabels = ['diario' => 'Diário', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
    @endphp
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
      <div class="checkout-summary-card" style="flex:1;min-width:130px;text-align:center;padding:1rem;">
        <p class="total" style="font-size:1.6rem;margin:0;">{{ $available }}</p>
        <p style="font-size:0.8rem;color:#64748b;margin:0;">Disponíveis (total)</p>
      </div>
      <div class="checkout-summary-card" style="flex:1;min-width:130px;text-align:center;padding:1rem;opacity:0.7;">
        <p class="total" style="font-size:1.6rem;margin:0;">{{ $used }}</p>
        <p style="font-size:0.8rem;color:#64748b;margin:0;">Utilizados</p>
      </div>
      <div class="checkout-summary-card" style="flex:1;min-width:130px;text-align:center;padding:1rem;opacity:0.7;">
        <p class="total" style="font-size:1.6rem;margin:0;">{{ $reserved }}</p>
        <p style="font-size:0.8rem;color:#64748b;margin:0;">Reservados</p>
      </div>
    </div>

    {{-- Disponíveis por plano --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
      @foreach($planLabels as $pid => $plabel)
        @php $planAvail = $planCounts[$pid] ?? 0; @endphp
        <div class="checkout-summary-card" style="flex:1;min-width:140px;text-align:center;padding:1rem;border-left:4px solid {{ $planAvail > 0 ? '#16a34a' : '#dc2626' }};">
          <p class="total" style="font-size:1.6rem;margin:0;color:{{ $planAvail > 0 ? '#16a34a' : '#dc2626' }};">{{ $planAvail }}</p>
          <p style="font-size:0.8rem;color:#64748b;margin:0;">Disponíveis — {{ $plabel }}</p>
          @if($planAvail === 0)
            <p style="font-size:0.75rem;color:#dc2626;margin:.25rem 0 0;">⚠ Stock esgotado</p>
          @endif
        </div>
      @endforeach
    </div>

    {{-- IMPORTAR CÓDIGOS --}}
    <div style="display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));margin-bottom:2rem;">

      {{-- Colar códigos --}}
      <div class="checkout-form-card">
        <h2>➕ Importar — Colar lista</h2>
        <p style="font-size:0.85rem;color:#64748b;margin-bottom:.75rem;">
          Seleccione o plano, cole os códigos (um por linha ou separados por vírgula/ponto-e-vírgula).
          Duplicados são ignorados automaticamente.
        </p>
        <form method="POST" action="{{ route('admin.wifi_codes.import_paste') }}">
          @csrf
          <div style="margin-bottom:.75rem;">
            <label for="paste_plan_id" style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:.25rem;">Plano <span style="color:#dc2626;">*</span></label>
            <select id="paste_plan_id" name="plan_id" required class="newsletter-input">
              <option value="">— Escolha o plano —</option>
              @foreach($planLabels as $pid => $plabel)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $plabel }}</option>
              @endforeach
            </select>
          </div>
          <textarea name="codes_text" rows="6" placeholder="ABC123DEF4&#10;XYZ789GHJ1&#10;..." required
            style="width:100%;padding:.5rem .75rem;border:1px solid #cbd5e1;border-radius:6px;font-family:monospace;font-size:0.9rem;box-sizing:border-box;resize:vertical;"></textarea>
          <div class="checkout-actions" style="margin-top:.75rem;">
            <button type="submit" class="btn-primary" style="width:100%;">Importar Códigos</button>
          </div>
        </form>
      </div>

      {{-- Upload CSV --}}
      <div class="checkout-form-card">
        <h2>📁 Importar — Ficheiro CSV/TXT</h2>
        <p style="font-size:0.85rem;color:#64748b;margin-bottom:.75rem;">
          Seleccione o plano e faça upload de um ficheiro <code>.csv</code> ou <code>.txt</code>
          com os códigos (um por linha). Máximo: 500 MB.
        </p>
        <form method="POST" action="{{ route('admin.wifi_codes.import_csv') }}" enctype="multipart/form-data">
          @csrf
          <div style="margin-bottom:.75rem;">
            <label for="csv_plan_id" style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:.25rem;">Plano <span style="color:#dc2626;">*</span></label>
            <select id="csv_plan_id" name="plan_id" required class="newsletter-input">
              <option value="">— Escolha o plano —</option>
              @foreach($planLabels as $pid => $plabel)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $plabel }}</option>
              @endforeach
            </select>
          </div>
          <input type="file" name="csv_file" accept=".csv,.txt,text/plain" required
            style="display:block;width:100%;padding:.4rem 0;font-size:0.9rem;">
          <div class="checkout-actions" style="margin-top:.75rem;">
            <button type="submit" class="btn-primary" style="width:100%;">Carregar Ficheiro</button>
          </div>
        </form>
      </div>
    </div>

    {{-- FILTRO + TABELA --}}
    <form method="get" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;margin-bottom:1rem;">
      <div style="display:flex;flex-direction:column;gap:.25rem;max-width:160px;">
        <label for="filter_plan" style="font-size:0.82rem;font-weight:600;">Plano</label>
        <select id="filter_plan" name="plan_id" class="newsletter-input">
          <option value="">Todos os planos</option>
          @foreach($planLabels as $pid => $plabel)
            <option value="{{ $pid }}" @selected(request('plan_id') === $pid)>{{ $plabel }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;flex-direction:column;gap:.25rem;max-width:160px;">
        <label for="status" style="font-size:0.82rem;font-weight:600;">Estado</label>
        <select id="status" name="status" class="newsletter-input">
          <option value="">Todos</option>
          <option value="available" @selected(request('status') === 'available')>Disponível</option>
          <option value="used"      @selected(request('status') === 'used')>Utilizado</option>
          <option value="reserved"  @selected(request('status') === 'reserved')>Reservado</option>
        </select>
      </div>
      <div style="display:flex;flex-direction:column;gap:.25rem;flex:1;min-width:180px;">
        <label for="q" style="font-size:0.82rem;font-weight:600;">Pesquisar código</label>
        <input id="q" name="q" value="{{ request('q') }}" class="newsletter-input" placeholder="Ex: ABC123...">
      </div>
      <div>
        <button type="submit" class="btn-primary" style="white-space:nowrap;">Filtrar</button>
        <a href="{{ route('admin.wifi_codes.index') }}" style="margin-left:.5rem;font-size:0.85rem;color:#64748b;">Limpar</a>
      </div>
    </form>

    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">#</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Plano</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Código</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Estado</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Ordem</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Utilizado em</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;">Importado em</th>
            <th style="text-align:left;padding:.5rem .6rem;border-bottom:2px solid #e2e8f0;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($codes as $code)
            <tr>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">#{{ $code->id }}</td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">
                @php $planLabels2 = ['diario' => 'Diário', 'semanal' => 'Semanal', 'mensal' => 'Mensal']; @endphp
                @if($code->plan_id && isset($planLabels2[$code->plan_id]))
                  <span style="font-size:0.82rem;font-weight:600;padding:.15rem .4rem;border-radius:4px;background:#e0f2fe;color:#0369a1;">{{ $planLabels2[$code->plan_id] }}</span>
                @else
                  <span style="color:#f59e0b;font-size:0.8rem;">⚠ sem plano</span>
                @endif
              </td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;font-family:monospace;font-weight:700;">{{ $code->code }}</td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">
                @if($code->status === 'available')
                  <span style="color:#16a34a;font-weight:700;">● Disponível</span>
                @elseif($code->status === 'used')
                  <span style="color:#94a3b8;">● Utilizado</span>
                @else
                  <span style="color:#f59e0b;">● Reservado</span>
                @endif
              </td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">
                @if($code->autovenda_order_id)
                  <a href="{{ route('admin.autovenda.index', ['q' => $code->autovenda_order_id]) }}">#{{ $code->autovenda_order_id }}</a>
                @else —
                @endif
              </td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">{{ optional($code->used_at)->format('d/m/Y H:i') ?: '—' }}</td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">{{ optional($code->created_at)->format('d/m/Y H:i') }}</td>
              <td style="padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;">
                @if($code->status === 'available')
                  <form method="POST" action="{{ route('admin.wifi_codes.destroy', $code) }}" onsubmit="return confirm('Eliminar este código?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:0.82rem;padding:0;">🗑 Eliminar</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding:.75rem;text-align:center;color:#94a3b8;">
                Nenhum código encontrado. Importe códigos acima para começar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:1rem;">
      {{ $codes->links() }}
    </div>

  </div>
</section>
@endsection


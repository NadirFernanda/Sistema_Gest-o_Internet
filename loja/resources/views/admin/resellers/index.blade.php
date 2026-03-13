@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de pedidos de revenda">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:.5rem;">
      <div>
        <h2 style="margin:0;">Pedidos de Revendedor</h2>
        <p class="lead" style="margin:.25rem 0 0;">Candidaturas ao programa de revenda AngolaWiFi.</p>
      </div>
      <a href="{{ route('admin.resellers.purchases.index') }}" class="btn-modern" style="font-size:.85rem;padding:.4rem .9rem;">Ver Compras em Bloco →</a>
    </div>

    <form method="get" class="howto-hero" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
      <div class="form-row" style="max-width:200px;">
        <label for="status">Estado</label>
        <select id="status" name="status" class="newsletter-input">
          <option value="">Todos</option>
          @foreach([\App\Models\ResellerApplication::STATUS_PENDING => 'Pendente', \App\Models\ResellerApplication::STATUS_APPROVED => 'Aprovado', \App\Models\ResellerApplication::STATUS_REJECTED => 'Rejeitado'] as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-row" style="flex:1;min-width:200px;">
        <label for="q">Pesquisa</label>
        <input id="q" name="q" value="{{ request('q') }}" class="newsletter-input" placeholder="ID, nome, e-mail ou telefone">
      </div>

      <div class="form-actions" style="margin-top:0;">
        <button type="submit" class="btn-primary">Filtrar</button>
      </div>
    </form>

    <div class="plans-table" style="margin-top:1rem;overflow-x:auto;">
      <table class="w-full text-sm" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Nome</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">E-mail</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Telefone</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Modo</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Manutenção</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Estado</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criado em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($applications as $app)
            <tr>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                <a href="{{ route('admin.resellers.show', $app) }}" style="color:inherit;">#{{ $app->id }}</a>
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                <a href="{{ route('admin.resellers.show', $app) }}" style="color:inherit;font-weight:600;">{{ $app->full_name }}</a>
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->email }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->phone }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($app->reseller_mode === 'own')
                  <span style="background:#dbeafe;color:#1d4ed8;padding:.15rem .45rem;border-radius:.3rem;font-size:.8rem;">Modo 1</span>
                @elseif($app->reseller_mode === 'angolawifi')
                  <span style="background:#fef3c7;color:#92400e;padding:.15rem .45rem;border-radius:.3rem;font-size:.8rem;">Modo 2</span>
                @else
                  <span style="color:#94a3b8;font-size:.85rem;">—</span>
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($app->maintenance_status === 'ok')
                  <span style="color:#16a34a;font-size:.85rem;">✅ OK</span>
                @elseif($app->maintenance_status === 'overdue')
                  <span style="color:#dc2626;font-size:.85rem;">⚠️ Atraso</span>
                @elseif($app->maintenance_status === 'pending')
                  <span style="color:#f59e0b;font-size:.85rem;">⏳ Pendente</span>
                @else
                  <span style="color:#94a3b8;font-size:.85rem;">—</span>
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->status }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($app->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding:.6rem;text-align:center;" class="muted">Nenhum pedido encontrado para os filtros atuais.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:1rem;">
      {{ $applications->links() }}
    </div>
  </div>
</section>
@endsection

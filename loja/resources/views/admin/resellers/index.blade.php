@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de pedidos de revenda">
  <div class="container">
    <h2>Pedidos de Revendedor</h2>
    <p class="lead">Candidaturas ao programa de revenda AngolaWiFi.</p>

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
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Local Instalação</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Tipo Internet</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Estado</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criado em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($applications as $app)
            <tr>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">#{{ $app->id }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->full_name }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->email }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->phone }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $app->installation_location }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($app->internet_type === \App\Models\ResellerApplication::INTERNET_OWN)
                  <span title="Tem internet própria">Própria</span>
                @elseif($app->internet_type === \App\Models\ResellerApplication::INTERNET_ANGOLAWIFI)
                  <span title="Necessita internet da AngolaWiFi">AngolaWiFi</span>
                @else
                  <span class="muted">—</span>
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

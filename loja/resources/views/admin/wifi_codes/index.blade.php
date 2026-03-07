@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de stock de códigos WiFi">
  <div class="container">
    <h2>Stock de Códigos WiFi</h2>
    <p class="lead">Gerencie os códigos disponíveis, usados e reservados para autovenda.</p>

    <form method="get" class="howto-hero" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
      <div class="form-row" style="max-width:200px;">
        <label for="status">Status</label>
        <select id="status" name="status" class="newsletter-input">
          <option value="">Todos</option>
          <option value="available" @selected(request('status') === 'available')>Disponível</option>
          <option value="used" @selected(request('status') === 'used')>Usado</option>
          <option value="reserved" @selected(request('status') === 'reserved')>Reservado</option>
        </select>
      </div>
      <div class="form-row" style="flex:1;min-width:200px;">
        <label for="q">Pesquisa</label>
        <input id="q" name="q" value="{{ request('q') }}" class="newsletter-input" placeholder="Código WiFi">
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
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Código</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Status</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Ordem</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Usado em</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criado em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($codes as $code)
            <tr>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">#{{ $code->id }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $code->code }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $code->status }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($code->autovenda_order_id)
                  <a href="{{ route('admin.autovenda.index', ['q' => $code->autovenda_order_id]) }}">#{{ $code->autovenda_order_id }}</a>
                @else
                  —
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($code->used_at)->format('d/m/Y H:i') }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($code->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="padding:.6rem;text-align:center;" class="muted">Nenhum código encontrado para os filtros atuais.</td>
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

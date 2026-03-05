@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Relatórios de autovenda">
  <div class="container">
    <h2>Relatórios de Autovenda</h2>
    <p class="lead">Resumo por estado e por dia das ordens de autovenda.</p>

    <div class="info-grid" style="margin-top:1.5rem;">
      <div class="info-card">
        <h3>Por estado</h3>
        @if($byStatus->isEmpty())
          <p>Nenhuma ordem registada.</p>
        @else
          <ul class="steps-list">
            @foreach($byStatus as $row)
              <li>
                <strong>{{ $row->status }}:</strong>
                {{ $row->total }} ordens &mdash;
                {{ number_format($row->total_amount ?? 0, 0, ',', '.') }} AOA
              </li>
            @endforeach
          </ul>
        @endif
      </div>

      <div class="info-card">
        <h3>Últimos dias</h3>
        @if($latestDays->isEmpty())
          <p>Nenhuma ordem registada.</p>
        @else
          <ul class="steps-list">
            @foreach($latestDays as $row)
              <li>
                <strong>{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}:</strong>
                {{ $row->total }} ordens &mdash;
                {{ number_format($row->total_amount ?? 0, 0, ',', '.') }} AOA
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection

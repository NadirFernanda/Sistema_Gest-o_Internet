@extends('layouts.app')

@section('title', 'Anúncios patrocinados — Admin')

@section('content')
<style>
.ads-admin{background:#f4f6f9;min-height:60vh;padding:2rem 0 4rem;color:#1a202c;font-family:Inter,system-ui,sans-serif}.ads-wrap{max-width:1140px;margin:auto;padding:0 1.25rem}.ads-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem}.ads-head h1{margin:0;font-size:1.4rem}.ads-sub{margin:.25rem 0 0;color:#64748b;font-size:.85rem}.ads-btn{display:inline-block;border:0;border-radius:7px;padding:.5rem .85rem;background:#f7b500;color:#1a202c;text-decoration:none;font-weight:700;font-size:.82rem;cursor:pointer}.ads-btn-muted{background:#e2e8f0}.ads-success{padding:.8rem 1rem;margin-bottom:1rem;border:1px solid #86efac;background:#f0fdf4;border-radius:8px;color:#166534}.ads-table-wrap{overflow-x:auto;background:white;border:1px solid #dde2ea;border-radius:10px}.ads-table{width:100%;border-collapse:collapse;font-size:.84rem}.ads-table th,.ads-table td{padding:.7rem .8rem;border-bottom:1px solid #eef1f5;text-align:left;vertical-align:middle}.ads-table th{background:#f8fafc;color:#64748b;font-size:.7rem;text-transform:uppercase;white-space:nowrap}.ads-table td:last-child{white-space:nowrap}.ads-empty{padding:2rem;text-align:center;color:#64748b}.ads-state{font-weight:700}.ads-note{padding:.85rem 1rem;margin-bottom:1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;color:#78350f;font-size:.84rem;line-height:1.5}
</style>
<div class="ads-admin"><div class="ads-wrap">
  <header class="ads-head">
    <div><h1>Campanhas e anúncios</h1><p class="ads-sub">Promoções próprias gratuitas e publicidade patrocinada de terceiros.</p></div>
    <div><a class="ads-btn" href="{{ route('admin.ads.create') }}">+ Nova campanha</a> <a class="ads-btn ads-btn-muted" href="{{ route('admin.dashboard') }}">Dashboard</a></div>
  </header>
  <div class="ads-note"><strong>Como funciona:</strong> as campanhas próprias AngolaWiFi são gratuitas e servem para divulgar ofertas da empresa. As campanhas patrocinadas são de anunciantes externos. Ambas podem usar as mesmas posições; a activação, o período e o limite máximo de exibições são definidos pela administração. O limite é contado por campanha e reinicia no início do dia, da semana (segunda-feira) ou do mês. As métricas não guardam IP nem identificadores pessoais e não garantem vendas.</div>
  @if(session('success'))<div class="ads-success">{{ session('success') }}</div>@endif
  <div class="ads-table-wrap"><table class="ads-table">
    <thead><tr><th>Tipo</th><th>Anunciante / campanha</th><th>Posição</th><th>Período</th><th>Estado</th><th>Limite de exibição</th><th>Impressões totais</th><th>Cliques</th><th>CTR</th><th>Acções</th></tr></thead>
    <tbody>
    @forelse($campaigns as $campaign)
      <tr>
        <td>{{ \App\Models\AdCampaign::TYPES[$campaign->campaign_type] ?? $campaign->campaign_type }}</td>
        <td><strong>{{ $campaign->advertiser_name }}</strong><br>{{ $campaign->title }}
          @if($campaign->bonus_plan_slug)<br><small>Bónus: {{ $campaign->bonusPlan?->name ?? $campaign->bonus_plan_slug }}</small>@endif
        </td>
        <td>{{ \App\Models\AdCampaign::PLACEMENTS[$campaign->placement] ?? $campaign->placement }}</td>
        <td>{{ $campaign->starts_at?->format('d/m/Y H:i') ?? 'Sem início' }}<br>{{ $campaign->ends_at?->format('d/m/Y H:i') ?? 'Sem fim' }}</td>
        <td class="ads-state">{{ $campaign->active ? 'Activa' : 'Pausada' }}</td>
        <td>@if($campaign->frequency_limit)<strong>{{ number_format($campaign->period_impressions_count, 0, ',', '.') }} / {{ number_format($campaign->frequency_limit, 0, ',', '.') }}</strong><br><small>por {{ strtolower(\App\Models\AdCampaign::FREQUENCY_PERIODS[$campaign->frequency_period]) }}</small>@else Sem limite @endif</td>
        <td>{{ number_format($campaign->impressions_count, 0, ',', '.') }}</td>
        <td>{{ number_format($campaign->clicks_count, 0, ',', '.') }}</td>
        <td>{{ $campaign->impressions_count ? number_format($campaign->clicks_count / $campaign->impressions_count * 100, 2, ',', '.') : '0,00' }}%</td>
        <td><a class="ads-btn ads-btn-muted" href="{{ route('admin.ads.edit', $campaign->id) }}">Editar</a>
          <form method="POST" action="{{ route('admin.ads.destroy', $campaign->id) }}" style="display:inline" onsubmit="return confirm('Eliminar esta campanha e as métricas associadas?')">
            @csrf @method('DELETE')<button class="ads-btn" style="background:#fee2e2;color:#991b1b" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td class="ads-empty" colspan="10">Ainda não existem campanhas. Crie uma para começar.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  <div style="margin-top:1rem">{{ $campaigns->links() }}</div>
</div></div>
@endsection

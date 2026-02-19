@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Planos</h1>

    <style>
      /* Toolbar for Planos templates modal — modernized, equal-width buttons */
      .templates-toolbar { display:flex; gap:12px; align-items:center; margin:8px 0 18px; }
      .templates-toolbar .template-btn { flex:1; min-width:0; padding:12px 14px; height:44px; border-radius:8px; text-align:center; font-weight:700; }
      @media (max-width:640px) { .templates-toolbar { flex-direction:column; } .templates-toolbar .template-btn { width:100%; } }
    </style>

    <div class="templates-toolbar" role="toolbar" aria-label="Ações de planos">
      @can('planos.create')
      <a href="{{ route('plan-templates.create') }}" class="btn btn-cta template-btn">Novo Plano</a>
      @endcan
      <button id="reloadTemplatesBtn" type="button" class="btn btn-cta template-btn">Recarregar</button>
      <button id="closeTemplatesBtn" type="button" class="btn btn-ghost template-btn">Fechar</button>
    </div>

    <table class="table" style="width:100%; margin-top:8px; border-collapse:collapse;">
      <thead>
        <tr style="text-align:left">
          <th>Nome</th>
          <th>Preço</th>
          <th>Ciclo (dias)</th>
          <th>Clientes ativos</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $t)
          <tr>
            <td>{{ $t->name }}</td>
            <td>{{ $t->preco ? 'Kz ' . number_format($t->preco, 2, ',', '.') : '-' }}</td>
            <td>{{ $t->ciclo ?? '-' }}</td>
            <td style="white-space:nowrap">{{ $t->active_clients_count ?? 0 }} {{ ($t->active_clients_count ?? 0) === 1 ? 'Cliente' : 'Clientes' }} cadastrados</td>
            <td>{{ $t->estado ?? '-' }}</td>
            <td style="text-align:right">
              @if($t->id)
                @can('planos.edit')
                <a href="{{ route('plan-templates.edit', $t) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar Plano">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                </a>
                @endcan
                @can('planos.delete')
                <form action="{{ route('plan-templates.destroy', $t) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Remover este plano?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn-icon btn-danger" type="submit" title="Remover" aria-label="Remover Plano">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
                @endcan
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5">Nenhum plano cadastrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

      <script>
        (function(){
          const reload = document.getElementById('reloadTemplatesBtn');
          const closeBtn = document.getElementById('closeTemplatesBtn');
          if(reload){
            reload.addEventListener('click', function(e){
              e.preventDefault();
              // simple full refresh; could be enhanced to re-fetch via AJAX
              window.location.reload();
            });
          }
          if(closeBtn){
            closeBtn.addEventListener('click', function(){
              // if modal wrapper exists, hide it; otherwise navigate back
              const modal = document.getElementById('templatesModal') || document.querySelector('.modal');
              if(modal) modal.style.display = 'none';
              else history.back();
            });
          }
        })();
      </script>
@endsection

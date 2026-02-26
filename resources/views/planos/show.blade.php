@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <h2 style="margin-top:0">Plano: {{ $plano->nome }}</h2>
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
            <div style="font-weight:700;font-size:1.15rem;color:#111">{{ $plano->preco ? 'Kz '.number_format($plano->preco,2,',','.') : '-' }}</div>
            <div style="background:#f0f0f0;padding:6px 10px;border-radius:20px">{{ $plano->ciclo }} dias</div>
            <div style="margin-left:auto">Status: <strong>{{ $plano->estado }}</strong></div>
        </div>

        {{-- Ações de Compensação / Janela (migradas da ficha do cliente) --}}
        <div class="no-print" style="display:flex;gap:10px;flex-direction:row;justify-content:flex-end;align-items:center;margin-bottom:12px;">
            <a href="{{ route('clientes.compensacoes', $plano->cliente_id) }}" class="btn btn-outline-secondary" style="padding:10px 18px; border-radius:8px; font-weight:700;">
                Histórico de Compensações
            </a>
            <button id="compensar-dias-btn" class="btn btn-warning" style="padding:12px 22px; font-size:1.05rem; border-radius:8px; min-width:160px; font-weight:700; margin-right:8px;">
                Compensar Dias
            </button>
            <button id="adicionar-janela-btn" class="btn btn-primary" style="padding:12px 22px; font-size:1.05rem; border-radius:8px; min-width:220px; font-weight:700;">
                Adicionar Janela
            </button>
        </div>

        <p>{{ $plano->descricao }}</p>
        <div style="margin-top:12px;display:flex;gap:8px;">
            @can('planos.edit')
            <a href="{{ route('planos.edit', $plano->id) }}" class="btn btn-warning">Editar</a>
            @endcan
            @can('planos.delete')
            <form action="{{ route('planos.destroy', $plano->id) }}" method="POST" onsubmit="return confirm('Apagar plano?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Apagar</button>
            </form>
            @endcan
            <a href="{{ route('planos.index') }}" class="btn btn-ghost">Voltar</a>
        </div>

        <!-- Modal para compensar dias -->
        <div id="modal-compensar-dias" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:32px 28px 24px 28px;border-radius:14px;max-width:380px;width:96vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;flex-direction:column;align-items:center;">
                <h5 style="margin-bottom:18px;">Compensar Dias ao Plano</h5>
                <form id="form-compensar-dias" method="POST" action="{{ route('clientes.compensar_dias', $plano->cliente_id) }}">
                    @csrf
                    <label for="dias_compensados" style="font-weight:600;">Dias a compensar:</label>
                    <input type="number" min="1" max="90" name="dias_compensados" id="dias_compensados" class="form-control" style="margin:10px 0 18px 0;width:120px;text-align:center;" required>
                    <button type="submit" class="btn btn-primary" style="min-width:120px;">Salvar</button>
                    <button type="button" id="fechar-modal-compensar" class="btn btn-ghost" style="margin-left:10px;">Cancelar</button>
                </form>
            </div>
        </div>

        <!-- Modal para adicionar janela automática -->
        <div id="modal-adicionar-janela" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:24px 20px;border-radius:12px;max-width:520px;width:96vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;flex-direction:column;">
                <h5 style="margin-bottom:12px;">Adicionar Janela automática</h5>
                <p style="margin:0 0 12px 0;color:#444;">Selecione o plano que deseja estender. Se não escolher, será usado o plano ativo mais recente.</p>
                <form id="form-adicionar-janela" method="POST" action="{{ route('clientes.adicionar_janela', $plano->cliente_id) }}">
                    @csrf
                    <div style="max-height:220px;overflow:auto;margin-bottom:12px;">
                        @foreach($plano->cliente->planos ?? [] as $pl)
                            <label style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:8px;border:1px solid #f0f0f0;margin-bottom:6px;">
                                <input type="radio" name="plano_id" value="{{ $pl->id }}">
                                <div style="flex:1;">
                                    <div style="font-weight:700;">{{ $pl->nome }}</div>
                                    @php
                                        $proximaDisplay = '—';
                                        if (!empty($pl->proxima_renovacao)) {
                                            $proximaDisplay = \Carbon\Carbon::parse($pl->proxima_renovacao)->format('d/m/Y');
                                        } elseif (!empty($pl->data_ativacao) && !empty($pl->ciclo)) {
                                            $cicloInt = intval(preg_replace('/\D/', '', (string) $pl->ciclo));
                                            if ($cicloInt <= 0) { $cicloInt = (int) $pl->ciclo ?: 30; }
                                            $proximaDisplay = \Carbon\Carbon::parse($pl->data_ativacao)->addDays($cicloInt - 1)->format('d/m/Y');
                                        }
                                    @endphp
                                    <div class="muted">Ativação: {{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : '—' }} • Ciclo: {{ $pl->ciclo ?? '—' }} dias • Próx: {{ $proximaDisplay }}</div>
                                </div>
                            </label>
                        @endforeach
                        @if(empty($plano->cliente->planos) || $plano->cliente->planos->isEmpty())
                            <p class="p-2 muted">Nenhum plano encontrado para este cliente.</p>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Adicionar Janela</button>
                        <button type="button" id="fechar-modal-janela" class="btn btn-ghost">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Modal logic for Compensar Dias
    const btnCompensar = document.getElementById('compensar-dias-btn');
    const modal = document.getElementById('modal-compensar-dias');
    const fechar = document.getElementById('fechar-modal-compensar');
    if(btnCompensar && modal && fechar){
        btnCompensar.onclick = () => { modal.style.display = 'flex'; const el = document.getElementById('dias_compensados'); if(el) el.focus(); };
        fechar.onclick = () => { modal.style.display = 'none'; };
        modal.onclick = (e) => { if(e.target === modal) modal.style.display = 'none'; };
    }

    // Modal logic for Adicionar Janela
    const btnJanela = document.getElementById('adicionar-janela-btn');
    const modalJanela = document.getElementById('modal-adicionar-janela');
    const fecharJanela = document.getElementById('fechar-modal-janela');
    if (btnJanela && modalJanela && fecharJanela) {
        btnJanela.onclick = () => { modalJanela.style.display = 'flex'; };
        fecharJanela.onclick = () => { modalJanela.style.display = 'none'; };
        modalJanela.onclick = (e) => { if (e.target === modalJanela) modalJanela.style.display = 'none'; };
    }
});
</script>
@endpush

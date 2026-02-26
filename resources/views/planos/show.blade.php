@extends('layouts.app')

@section('content')
    <div style="max-width:980px;margin:18px auto;padding:8px 18px;background:#fff;border-radius:10px;">
        @php
            use App\Models\Cobranca;
            use App\Models\Compensacao;
            $cliente = $plano->cliente ?? null;
            $compCount = $cliente ? Compensacao::where('cliente_id', $cliente->id)->count() : 0;
        @endphp

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;">
            <div style="flex:1;min-width:320px;">
                <div style="margin-bottom:6px;color:#333;font-size:0.98rem;">Plano: <strong>{{ $plano->nome }}</strong></div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                    <div style="font-size:2rem;font-weight:800;">Kz {{ isset($plano->preco) ? number_format($plano->preco,2,',','.') : '-' }}</div>
                    <div style="background:#f0f0f0;padding:8px 12px;border-radius:999px;font-weight:700;color:#333;">{{ $plano->ciclo ?? '30' }} dias</div>
                </div>
                <p style="margin:6px 0 12px 0;color:#333;">{{ $plano->descricao }}</p>

                <div class="plano-top-actions" style="display:flex;gap:12px;align-items:stretch;">
                    <a href="{{ route('planos.edit', $plano->id) }}" class="plano-top-btn btn btn-warning" style="flex:1;text-align:center;padding:14px 0;border-radius:10px;">Editar</a>
                    <form action="{{ route('planos.destroy', $plano->id) }}" method="POST" onsubmit="return confirm('Apagar plano?');" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="plano-top-btn btn btn-warning" style="padding:14px 18px;border-radius:10px;margin-left:6px;">Apagar</button>
                    </form>
                    <a href="{{ route('planos.index') }}" class="plano-top-btn btn btn-warning" style="flex:1;text-align:center;padding:14px 0;border-radius:10px;">Voltar</a>
                </div>

                <div style="margin-top:18px;background:#fff6df;padding:14px;border-radius:10px;">
                    <div style="display:flex;gap:12px;">
                        <button type="button" onclick="location.href='{{ $plano->cliente_id ? route('clientes.compensacoes', $plano->cliente_id) : '#' }}'" class="btn btn-cta" style="flex:1;padding:12px 0;border-radius:10px;font-weight:700;background:#f7b500;color:#fff;border:none;">Histórico de Compensações</button>
                        <button id="compensar-dias-btn" class="btn btn-cta" style="flex:1;padding:12px 0;border-radius:10px;font-weight:700;background:#f7b500;color:#fff;border:none;">Compensar Dias</button>
                        <button id="adicionar-janela-btn" class="btn btn-cta" style="flex:1;padding:12px 0;border-radius:10px;font-weight:700;background:#f7b500;color:#fff;border:none;">Adicionar Janela</button>
                    </div>
                </div>
            </div>

            <div style="min-width:160px;text-align:right;">
                <div style="color:#666;margin-bottom:6px;">Status:</div>
                <div style="font-weight:800;font-size:1.05rem;">{{ $plano->estado ?? '—' }}</div>
            </div>
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

@extends('layouts.app')

@section('content')
    <div style="max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:10px;">
        <!-- Top action buttons (visíveis) -->
        <div class="plano-top-actions" style="margin-bottom:18px;">
            @if($plano->cliente_id)
                <button type="button" onclick="location.href='{{ route('clientes.compensacoes', $plano->cliente_id) }}'" class="plano-top-btn">Histórico de Compensações</button>
            @else
                <button type="button" class="plano-top-btn disabled" disabled>Histórico de Compensações</button>
            @endif
            <button id="compensar-dias-btn" class="plano-top-btn">Compensar Dias</button>
            <button id="adicionar-janela-btn" class="plano-top-btn">Adicionar Janela</button>
        </div>

        <p style="margin-top:0;margin-bottom:18px;">{{ $plano->descricao }}</p>

        @php
            use App\Models\Cobranca;
            use App\Models\Compensacao;
            $cliente = $plano->cliente ?? null;
            $ultimaJanela = optional($plano->janelas)->last();
            $now = \Carbon\Carbon::now();
            $proxima = $plano->proxima_renovacao ? \Carbon\Carbon::parse($plano->proxima_renovacao) : null;
            if ($proxima) {
                $diasRestantes = $proxima->isFuture() ? $now->diffInDays($proxima) : -1 * $proxima->diffInDays($now);
            } else {
                $diasRestantes = null;
            }
            $ultimoPagamento = null;
            if ($cliente) {
                // Note: `cobrancas` table doesn't have a `plano_id` column — use last cobrança for cliente
                $ultimoPagamento = $cliente->cobrancas()->orderBy('created_at', 'desc')->first();
                $compCount = Compensacao::where('cliente_id', $cliente->id)->count();
                $equipamentos = $cliente->equipamentos()->limit(5)->get();
            } else {
                $compCount = 0;
                $equipamentos = collect();
            }
        @endphp

        <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;min-width:260px;">
                <h4 style="margin:0 0 8px 0">Resumo</h4>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px;background:#fafafa;">
                    <div><strong>Cliente:</strong>
                        @if($cliente)
                            <a href="{{ route('clientes.show', $cliente->id) }}">{{ $cliente->nome }}</a>
                        @else
                            —
                        @endif
                    </div>
                    <div><strong>Plano:</strong> {{ $plano->nome }}</div>
                    <!-- Preço e ciclo ocultados nesta vista por motivo de privacidade -->
                </div>
            </div>

            <div style="flex:1;min-width:260px;">
                <h4 style="margin:0 0 8px 0">Status & Datas</h4>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px;background:#fff;">
                    <div><strong>Ativação:</strong> {{ $plano->data_ativacao ? \Carbon\Carbon::parse($plano->data_ativacao)->format('d/m/Y') : '—' }}</div>
                    <div><strong>Próx. Renovação:</strong> {{ $proxima ? $proxima->format('d/m/Y') : '—' }}</div>
                    <div><strong>Dias restantes:</strong>
                        @if(is_null($diasRestantes)) —
                        @else
                            @if($diasRestantes < 0)
                                Expirado {{ abs($diasRestantes) }} dia(s)
                            @else
                                {{ $diasRestantes }} dia(s)
                            @endif
                        @endif
                    </div>
                    <div><strong>Estado:</strong> <span style="font-weight:700">{{ $plano->estado ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        @if($ultimaJanela)
            <div style="margin:12px 0;padding:12px;border:1px solid #eee;border-radius:8px;background:#fafafa;">
                <h4 style="margin:0 0 8px 0;font-size:1rem;">Última janela adicionada</h4>
                <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:0.95rem;color:#222;">
                    <div><strong>Descrição:</strong> {{ $ultimaJanela->descricao ?? $ultimaJanela->observacoes ?? '—' }}</div>
                    <div><strong>Início:</strong> {{ $ultimaJanela->inicio ?? $ultimaJanela->start ?? '—' }}</div>
                    <div><strong>Fim:</strong> {{ $ultimaJanela->fim ?? $ultimaJanela->end ?? '—' }}</div>
                    <div><strong>Criada em:</strong> {{ $ultimaJanela->created_at ?? '—' }}</div>
                </div>
            </div>
        @endif

        <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:12px;">
            <div style="flex:1;min-width:300px;">
                <h4 style="margin:0 0 8px 0">Faturamento</h4>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px;background:#fff;">
                    <div><strong>Último pagamento:</strong>
                        @if($ultimoPagamento)
                            {{ $ultimoPagamento->valor ? 'Kz '.number_format($ultimoPagamento->valor,2,',','.') : '—' }} • {{ \Carbon\Carbon::parse($ultimoPagamento->created_at)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </div>
                    <div style="margin-top:8px;">
                        @if($cliente)
                            <a href="{{ route('cobrancas.index', ['cliente' => $cliente->nome]) }}" class="btn btn-ghost">Ver cobranças deste cliente</a>
                        @else
                            <a class="btn btn-ghost disabled">Ver cobranças deste cliente</a>
                        @endif
                    </div>
                </div>
            </div>

            <div style="flex:1;min-width:300px;">
                <h4 style="margin:0 0 8px 0">Relações</h4>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px;background:#fafafa;">
                    <div><strong>Compensações:</strong> {{ $compCount }} — <a href="{{ route('clientes.compensacoes', $plano->cliente_id) }}">ver histórico</a></div>
                    <div style="margin-top:8px;"><strong>Equipamentos ({!! $equipamentos->count() !!}):</strong>
                        <ul style="margin:6px 0 0 16px;padding:0;">
                            @forelse($equipamentos as $eq)
                                <li>{{ $eq->nome ?? ($eq->descricao ?? '—') }}</li>
                            @empty
                                <li class="muted">Nenhum equipamento associado</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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

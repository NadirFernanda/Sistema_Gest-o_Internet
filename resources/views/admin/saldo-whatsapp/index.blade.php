@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* Alinhar o logo (e o bloco de título) à esquerda só nesta página —
           o clientes.css centra o logo (margin:auto) para todas as outras. */
        .saldo-whatsapp-page .clientes-hero .hero-left { justify-content: flex-start; }
        .saldo-whatsapp-page .clientes-hero .logo { margin: 12px 0 !important; }
        .saldo-whatsapp-page .clientes-hero .hero-titles { align-items: flex-start !important; text-align: left; }
    </style>
@endpush

<div class="estoque-container-moderna saldo-whatsapp-page">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Saldo de Mensagens WhatsApp',
        'subtitle' => 'Cada mensagem enviada custa ' . number_format(\App\Models\WhatsappLedger::CUSTO_POR_MENSAGEM, 2, ',', '.') . ' Kz. Pré-pago — sem saldo, os alertas automáticos param de sair.',
        'stackLeft' => true,
    ])

    <div class="clientes-toolbar" style="max-width:1100px;margin:18px auto;display:flex;justify-content:space-between;align-items:center;padding:0 8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Painel</a>
        <button type="button" onclick="toggleEstado()" class="btn btn-search" id="btn-estado" style="white-space:nowrap;">
            Estado WhatsApp
        </button>
    </div>

    {{-- Painel de estado / QR code --}}
    <div id="painel-estado" style="display:none;max-width:1100px;margin:0 auto 18px;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:28px;text-align:center;">
        <div id="estado-loading" style="color:#888;font-size:1.05em;">A verificar ligação...</div>
        <div id="estado-ok" style="display:none;">
            <div style="font-size:3em;">✅</div>
            <div style="font-weight:700;font-size:1.2em;color:#1c8a3c;margin-top:8px;">WhatsApp ligado</div>
            <div id="estado-texto" style="color:#888;margin-top:4px;font-size:0.9em;"></div>
        </div>
        <div id="estado-qr" style="display:none;">
            <div style="font-size:2em;">📵</div>
            <div style="font-weight:700;font-size:1.1em;color:#e74c3c;margin-top:8px;">WhatsApp desligado</div>
            <div style="color:#888;margin:8px 0 16px;font-size:0.9em;">Leia o QR code com o WhatsApp no telemóvel para reconectar</div>
            <img id="qr-img" src="" alt="QR Code" style="width:220px;height:220px;border:2px solid #eee;border-radius:12px;display:block;margin:0 auto;">
            <div style="margin-top:12px;color:#aaa;font-size:0.82em;">A actualizar automaticamente...</div>
        </div>
        <div id="estado-erro" style="display:none;color:#e74c3c;font-size:0.95em;"></div>
    </div>

    @if (session('success'))
        <div style="max-width:1100px;margin:12px auto;padding:12px 16px;background:#e6f9ec;border:1px solid #34c759;border-radius:8px;color:#136c34;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div style="max-width:1100px;margin:18px auto;display:flex;gap:16px;flex-wrap:wrap;align-items:stretch;">
        <div style="flex:1 1 260px;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:24px;text-align:center;">
            <div style="font-size:0.95em;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Saldo actual</div>
            <div style="font-size:2.4em;font-weight:800;color:{{ $saldo < \App\Models\WhatsappLedger::CUSTO_POR_MENSAGEM ? '#e74c3c' : '#1c8a3c' }};margin-top:6px;">
                {{ number_format($saldo, 2, ',', '.') }} Kz
            </div>
            @if ($saldo < \App\Models\WhatsappLedger::CUSTO_POR_MENSAGEM)
                <div style="margin-top:8px;color:#e74c3c;font-weight:600;font-size:0.92em;">
                    Saldo insuficiente — os envios automáticos estão bloqueados.
                </div>
            @endif
        </div>

        @if ($podeCarregar)
            <div style="flex:2 1 380px;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:24px;">
                <div style="font-weight:700;margin-bottom:12px;color:#333;">Carregar saldo</div>
                <form method="POST" action="{{ route('saldo-whatsapp.carregar') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                    @csrf
                    <div>
                        <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Valor (Kz)</label>
                        <input type="number" name="valor" min="1" step="0.01" required class="search-input" style="width:160px;">
                    </div>
                    <div style="flex:1;min-width:180px;">
                        <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Descrição (opcional)</label>
                        <input type="text" name="descricao" maxlength="255" placeholder="Ex.: Pagamento via Multicaixa" class="search-input" style="width:100%;">
                    </div>
                    <button type="submit" class="btn btn-search">Carregar</button>
                </form>
                @error('valor')
                    <div style="color:#e74c3c;margin-top:8px;font-size:0.9em;">{{ $message }}</div>
                @enderror
            </div>
        @else
            <div style="flex:2 1 380px;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:24px;display:flex;align-items:center;color:#888;">
                Para carregar saldo, contacte o Desenvolvedor do sistema.
            </div>
        @endif
    </div>

    <div class="estoque-tabela-moderna" style="max-width:1100px;margin:18px auto;">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;">Quando</th>
                    <th style="text-align:center;">Tipo</th>
                    <th style="text-align:center;">Descrição</th>
                    <th style="text-align:center;">Número</th>
                    <th style="text-align:center;">Nome</th>
                    <th style="text-align:center;">Valor</th>
                    <th style="text-align:center;">Saldo após</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimentos as $m)
                    @php $eEstorno = $m->tipo === 'credito' && str_starts_with($m->descricao, 'Estorno'); @endphp
                    <tr>
                        <td style="text-align:center;">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align:center;">
                            @if ($eEstorno)
                                <span style="color:#e67e22;font-weight:700;">Estorno</span>
                            @elseif ($m->tipo === 'credito')
                                <span style="color:#1c8a3c;font-weight:700;">Carregamento</span>
                            @else
                                <span style="color:#e74c3c;font-weight:700;">Mensagem{{ $m->mensagem_tipo ? ' (' . $m->mensagem_tipo . ')' : '' }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            {{ $m->descricao }}
                            @if ($m->registadoPor)
                                <div style="font-size:0.82em;color:#999;">por {{ $m->registadoPor->name }}</div>
                            @endif
                            @if ($eEstorno && $m->erro_detalhes)
                                <button type="button"
                                        onclick="verFalha(this)"
                                        data-erro="{{ e($m->erro_detalhes) }}"
                                        style="margin-top:4px;display:inline-block;padding:2px 10px;font-size:0.8em;font-weight:600;color:#fff;background:#e74c3c;border:none;border-radius:6px;cursor:pointer;">
                                    Ver falha
                                </button>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $m->destinatario ?? '—' }}</td>
                        <td style="text-align:center;">{{ $m->destinatario_nome ?? '—' }}</td>
                        <td style="text-align:center;font-weight:700;color:{{ $m->tipo === 'credito' ? ($eEstorno ? '#e67e22' : '#1c8a3c') : '#e74c3c' }};">
                            {{ $m->tipo === 'credito' ? '+' : '-' }}{{ number_format($m->valor, 2, ',', '.') }} Kz
                        </td>
                        <td style="text-align:center;">{{ number_format($m->saldo_apos, 2, ',', '.') }} Kz</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#999;padding:24px;">Ainda não há movimentos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $movimentos->links() }}
</div>

{{-- Modal: detalhe da falha de envio --}}
<div id="modal-falha"
     style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;"
     onclick="if(event.target===this)fecharModal()">
    <div style="background:#fff;border-radius:16px;padding:28px 28px 24px;max-width:620px;width:92%;max-height:80vh;overflow-y:auto;position:relative;box-shadow:0 8px 40px rgba(0,0,0,0.22);">
        <button type="button" onclick="fecharModal()"
                style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.6em;line-height:1;cursor:pointer;color:#999;"
                aria-label="Fechar">×</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <span style="font-size:1.5em;">⚠️</span>
            <span style="font-weight:700;font-size:1.1em;color:#c0392b;">Detalhe da falha</span>
        </div>
        <pre id="modal-falha-texto"
             style="white-space:pre-wrap;word-break:break-all;background:#fdf2f2;border:1px solid #f5c6c6;border-radius:8px;padding:14px;font-size:0.88em;color:#5a1a1a;margin:0;"></pre>
    </div>
</div>

@push('scripts')
<script>
function verFalha(btn) {
    document.getElementById('modal-falha-texto').textContent = btn.dataset.erro;
    document.getElementById('modal-falha').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function fecharModal() {
    document.getElementById('modal-falha').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fecharModal();
});

// ── Estado da ligação WhatsApp ──
let estadoAberto = false;
let estadoTimer  = null;

function toggleEstado() {
    estadoAberto = !estadoAberto;
    document.getElementById('painel-estado').style.display = estadoAberto ? 'block' : 'none';
    if (estadoAberto) {
        verificarEstado();
    } else {
        clearTimeout(estadoTimer);
    }
}

function verificarEstado() {
    fetch('{{ route('saldo-whatsapp.estado') }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('estado-loading').style.display = 'none';
            document.getElementById('estado-ok').style.display    = 'none';
            document.getElementById('estado-qr').style.display    = 'none';
            document.getElementById('estado-erro').style.display  = 'none';

            if (data.connected) {
                document.getElementById('estado-ok').style.display = 'block';
                document.getElementById('estado-texto').textContent = 'Estado: ' + data.status;
                estadoTimer = setTimeout(verificarEstado, 10000);
            } else if (data.qrcode) {
                document.getElementById('estado-qr').style.display = 'block';
                document.getElementById('qr-img').src = 'data:image/png;base64,' + data.qrcode;
                estadoTimer = setTimeout(verificarEstado, 5000);
            } else {
                document.getElementById('estado-erro').style.display = 'block';
                document.getElementById('estado-erro').textContent = 'Estado: ' + (data.status || 'desconhecido');
                estadoTimer = setTimeout(verificarEstado, 8000);
            }
        })
        .catch(() => {
            document.getElementById('estado-loading').style.display = 'none';
            document.getElementById('estado-erro').style.display = 'block';
            document.getElementById('estado-erro').textContent = 'Erro ao contactar o servidor.';
            estadoTimer = setTimeout(verificarEstado, 8000);
        });
}
</script>
@endpush
@endsection

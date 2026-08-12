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

    <div class="clientes-toolbar" style="max-width:1100px;margin:18px auto;display:flex;justify-content:flex-end;padding:0 8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Painel</a>
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
    </div>

    <div class="estoque-tabela-moderna" style="max-width:1100px;margin:18px auto;">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;">Quando</th>
                    <th style="text-align:center;">Tipo</th>
                    <th style="text-align:center;">Descrição</th>
                    <th style="text-align:center;">Destinatário</th>
                    <th style="text-align:center;">Valor</th>
                    <th style="text-align:center;">Saldo após</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimentos as $m)
                    <tr>
                        <td style="text-align:center;">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align:center;">
                            @if ($m->tipo === 'credito')
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
                        </td>
                        <td style="text-align:center;">{{ $m->destinatario ?? '—' }}</td>
                        <td style="text-align:center;font-weight:700;color:{{ $m->tipo === 'credito' ? '#1c8a3c' : '#e74c3c' }};">
                            {{ $m->tipo === 'credito' ? '+' : '-' }}{{ number_format($m->valor, 2, ',', '.') }} Kz
                        </td>
                        <td style="text-align:center;">{{ number_format($m->saldo_apos, 2, ',', '.') }} Kz</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:#999;padding:24px;">Ainda não há movimentos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $movimentos->links() }}
</div>
@endsection

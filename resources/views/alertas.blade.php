@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

    <div class="alertas-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Alertas Ativos',
            'subtitle' => '',
            'heroCtAs' => '<a href="' . route('dashboard') . '" class="btn btn-ghost">Voltar ao Dashboard</a><button id="btnDispararAlertas" class="btn btn-primary" style="font-weight:600;">Disparar Alertas</button>'
        ])
        {{-- Toolbar abaixo do header: busca, dias e CTAs --}}
        <div class="alertas-toolbar" style="max-width:1100px;margin:18px auto;display:flex;gap:10px;align-items:center;justify-content:space-between;">
            <div style="display:flex;gap:8px;align-items:center;">
                <label for="diasAlerta" style="margin:0 6px 0 0;">Exibir alertas até</label>
                <input type="number" id="diasAlerta" value="5" name="dias" min="1" max="30" style="width:72px;padding:8px;border-radius:12px;border:2px solid #e6a248;">
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button id="btnDispararAlertas" class="btn btn-cta" style="font-weight:600;">Disparar</button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
            </div>
        </div>
        <div class="alertas-lista" id="alertasLista">
            <p>Nenhum alerta ativo no momento.</p>
        </div>
    </div>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnDispararAlertas');
        if (btn) {
            btn.addEventListener('click', async function() {
                btn.disabled = true;
                btn.textContent = 'Enviando...';
                // Enviar apenas o parâmetro 'dias' para o backend
                const diasAlertaInput = document.getElementById('diasAlerta');
                const dias = diasAlertaInput ? parseInt(diasAlertaInput.value) : 5;
                const selecionados = Array.from(document.querySelectorAll('#alertasLista .chk-alerta:checked'))
                    .map(cb => parseInt(cb.dataset.planoId))
                    .filter(id => !isNaN(id));

                if (!selecionados.length) {
                    alert('Selecione pelo menos um cliente para disparar o alerta.');
                    btn.disabled = false;
                    btn.textContent = 'Disparar Alertas';
                    return;
                }
                try {
                    const res = await fetch('/api/alertas/disparar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ dias, planos: selecionados })
                    });

                    const data = await res.json();
                    if (data && data.success) {
                        const selecionadosElems = Array.from(document.querySelectorAll('#alertasLista .chk-alerta:checked'));
                        const destinatarios = selecionadosElems.map(cb => {
                            const nome = cb.dataset.nome || '';
                            const contato = cb.dataset.contato || '';
                            return contato ? `${nome} (${contato})` : nome;
                        }).filter(txt => txt.trim() !== '');

                        if (destinatarios.length === 1) {
                            alert(`Alertas de vencimento foram enviados com sucesso para o WhatsApp de ${destinatarios[0]}.`);
                        } else if (destinatarios.length > 1) {
                            alert(`Alertas de vencimento foram enviados com sucesso para o WhatsApp dos seguintes clientes: ${destinatarios.join(', ')}.`);
                        } else {
                            alert('Alertas de vencimento foram enviados com sucesso para o WhatsApp dos clientes selecionados.');
                        }
                    } else {
                        alert('Erro ao disparar alertas.');
                    }
                } catch {
                    alert('Erro de conexão ao disparar alertas.');
                }
                btn.disabled = false;
                btn.textContent = 'Disparar Alertas';
            });
        }
    });
    </script>
@endsection


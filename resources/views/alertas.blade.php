@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

    <div class="alertas-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Alertas Ativos',
            'subtitle' => ''
        ])
        {{-- Toolbar abaixo do header: busca, dias e CTAs --}}
        <div class="alertas-toolbar">
            <div class="alertas-toolbar-left">
                <label for="diasAlerta">Exibir alertas até</label>
                <input type="number" id="diasAlerta" value="5" name="dias" min="1" max="30" class="dias-input">
            </div>
            <div class="alertas-toolbar-actions">
                <button id="btnDispararAlertas" class="btn btn-cta">Disparar</button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
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
                        // Attempt to detect whether the backend reported WhatsApp activity in the artisan output.
                        const out = (data.output || '').toLowerCase();
                        const selecionadosElems = Array.from(document.querySelectorAll('#alertasLista .chk-alerta:checked'));
                        const destinatarios = selecionadosElems.map(cb => {
                            const nome = cb.dataset.nome || '';
                            const contato = cb.dataset.contato || '';
                            const email = cb.dataset.email || '';
                            return { nome, contato, email };
                        }).filter(o => (o.contato || o.email || o.nome));

                        if (out.includes('whatsapp enviado')) {
                            // backend did send WhatsApp messages
                            if (destinatarios.length === 1) {
                                const d = destinatarios[0];
                                alert(`Alertas de vencimento foram enviados com sucesso para o WhatsApp de ${d.nome} (${d.contato || d.email}).`);
                            } else if (destinatarios.length > 1) {
                                alert(`Alertas de vencimento foram enviados com sucesso para o WhatsApp dos seguintes clientes: ${destinatarios.map(d => d.nome + ' (' + (d.contato || d.email) + ')').join(', ')}.`);
                            } else {
                                alert('Alertas de vencimento foram enviados com sucesso para o WhatsApp dos clientes selecionados.');
                            }
                        } else if (out.includes('whatsapp skipped') || out.includes('whatsapp desativado') || out.includes('whatsapp ausente')) {
                            // WhatsApp skipped — show email recipients instead
                            if (destinatarios.length === 1) {
                                const d = destinatarios[0];
                                const emailTxt = d.email || d.contato || d.nome;
                                alert(`Alertas enviados com sucesso por e-mail para: ${emailTxt}.`);
                            } else if (destinatarios.length > 1) {
                                const list = destinatarios.map(d => d.email || d.contato || d.nome).filter(x => x).join(', ');
                                alert(`Alertas enviados com sucesso por e-mail para: ${list}.`);
                            } else {
                                alert('Alertas enviados com sucesso (e-mail).');
                            }
                        } else {
                            // Generic success when backend didn't explicitly report WhatsApp
                            alert('Alertas enviados com sucesso.');
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


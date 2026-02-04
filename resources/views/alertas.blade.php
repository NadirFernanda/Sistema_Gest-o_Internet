@extends('layouts.app')

@section('content')
    <div class="alertas-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Alertas Ativos</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
        <div style="margin: 18px 0 0 0;">
            <label for="diasAlerta">Exibir alertas para serviços que terminam em até </label>
            <input type="number" id="diasAlerta" value="5" min="1" max="30" style="width:60px;"> dias
        </div>
        <div style="margin: 24px 0 0 0; text-align: right;">
            <button id="btnDispararAlertas" class="btn" style="background:#f7b500;color:#fff;font-weight:600;">Disparar Alertas</button>
        </div>
        <h2 style="margin-top:32px;">Lista de Alertas</h2>
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


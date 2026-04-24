@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 560px;">

    <div class="card rounded-2xl border shadow-sm">
        {{-- Header --}}
        <div class="card-header text-white rounded-top text-center py-4"
             style="background: linear-gradient(135deg, #e63946, #c1121f);">
            <div id="iconProcessando">
                <div class="spinner-border text-white mb-3" role="status" style="width:3rem;height:3rem;"></div>
                <h5 class="mb-0">A aguardar autorização</h5>
                <small>Verifique a app Multicaixa Express</small>
            </div>
            <div id="iconAprovado" class="d-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="currentColor"
                     viewBox="0 0 16 16" class="mb-2 text-white">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <h5 class="mb-0 fw-bold">Pagamento Aprovado!</h5>
            </div>
            <div id="iconRecusado" class="d-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="currentColor"
                     viewBox="0 0 16 16" class="mb-2 text-white">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                </svg>
                <h5 class="mb-0 fw-bold">Pagamento Recusado</h5>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- Info da transação --}}
            <div class="bg-light rounded p-3 mb-4">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Referência</small>
                        <strong>{{ $pagamento->merchant_transaction_id }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Valor</small>
                        <strong class="text-danger">Kz {{ number_format($pagamento->valor, 2, ',', '.') }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Telemóvel</small>
                        <strong>+{{ ltrim($pagamento->telefone, '0') }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        <strong id="statusTexto" class="text-info">{{ ucfirst($pagamento->status) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Mensagem --}}
            <div id="mensagemInfo" class="alert alert-info small mb-3">
                <strong>Aguardando confirmação...</strong><br>
                Abra a app <strong>Multicaixa Express</strong> e autorize o pagamento de
                <strong>Kz {{ number_format($pagamento->valor, 2, ',', '.') }}</strong>.
                Esta página atualiza automaticamente.
            </div>
            <div id="mensagemAprovado" class="alert alert-success small mb-3 d-none">
                <strong>Pagamento confirmado!</strong> A cobrança foi marcada como paga.
            </div>
            <div id="mensagemRecusado" class="alert alert-danger small mb-3 d-none">
                <strong>Pagamento recusado.</strong> <span id="motivoRecusa"></span>
            </div>

            {{-- Ações --}}
            <div class="d-grid gap-2">
                <a href="{{ route('cobrancas.show', $cobranca->id) }}" class="btn btn-outline-secondary">
                    Ver Detalhes da Cobrança
                </a>
                <a href="{{ route('pagamentos.iniciar', $cobranca) }}" class="btn btn-danger d-none" id="btnTentarNovamente">
                    Tentar Novamente
                </a>
            </div>
        </div>

        <div class="card-footer text-muted text-center small py-2">
            Pagamento processado por <strong>Pay4All · Multicaixa Express</strong>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const statusUrl = '{{ route("pagamentos.status", $pagamento) }}';
    let tentativas = 0;
    const maxTentativas = 36; // ~3 minutos (36 × 5s)

    function atualizarStatus() {
        fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                document.getElementById('statusTexto').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                if (data.status === 'aprovado') {
                    mostrarAprovado();
                    return;
                }
                if (data.status === 'recusado' || data.status === 'erro') {
                    mostrarRecusado(data.mensagem);
                    return;
                }

                tentativas++;
                if (tentativas < maxTentativas) {
                    setTimeout(atualizarStatus, 5000);
                } else {
                    document.getElementById('mensagemInfo').innerHTML =
                        '<strong>Tempo esgotado.</strong> O pagamento ainda não foi confirmado. Verifique mais tarde.';
                    document.getElementById('btnTentarNovamente').classList.remove('d-none');
                }
            })
            .catch(() => {
                tentativas++;
                if (tentativas < maxTentativas) setTimeout(atualizarStatus, 5000);
            });
    }

    function mostrarAprovado() {
        document.getElementById('iconProcessando').classList.add('d-none');
        document.getElementById('iconAprovado').classList.remove('d-none');
        document.getElementById('mensagemInfo').classList.add('d-none');
        document.getElementById('mensagemAprovado').classList.remove('d-none');
        document.getElementById('statusTexto').className = 'text-success';
    }

    function mostrarRecusado(motivo) {
        document.getElementById('iconProcessando').classList.add('d-none');
        document.getElementById('iconRecusado').classList.remove('d-none');
        document.getElementById('mensagemInfo').classList.add('d-none');
        document.getElementById('mensagemRecusado').classList.remove('d-none');
        document.getElementById('motivoRecusa').textContent = motivo ?? '';
        document.getElementById('btnTentarNovamente').classList.remove('d-none');
        document.getElementById('statusTexto').className = 'text-danger';
    }

    @if($pagamento->isPendente())
        setTimeout(atualizarStatus, 5000);
    @elseif($pagamento->isAprovado())
        mostrarAprovado();
    @else
        mostrarRecusado('{{ addslashes($pagamento->gateway_message ?? '') }}');
    @endif
})();
</script>
@endpush

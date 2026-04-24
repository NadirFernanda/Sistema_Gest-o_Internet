@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 600px;">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cobrancas.index') }}">Cobranças</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cobrancas.show', $cobranca->id) }}">Cobrança #{{ $cobranca->id }}</a></li>
            <li class="breadcrumb-item active">Pagar via Multicaixa Express</li>
        </ol>
    </nav>

    {{-- Card principal --}}
    <div class="card rounded-2xl border shadow-sm">
        {{-- Header gradiente --}}
        <div class="card-header text-white rounded-top" style="background: linear-gradient(135deg, #e63946, #c1121f);">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3h16v5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V7zm3 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H3z"/>
                </svg>
                <strong>Pagamento via Multicaixa Express</strong>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- Resumo da cobrança --}}
            <div class="bg-light rounded p-3 mb-4">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Cliente</small>
                        <strong>{{ $cobranca->cliente->nome ?? '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Valor a Pagar</small>
                        <strong class="fs-5 text-danger">Kz {{ $cobranca->valor_formatado }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Descrição</small>
                        <span>{{ $cobranca->descricao }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Vencimento</small>
                        <span>{{ $cobranca->data_vencimento_formatada }}</span>
                    </div>
                </div>
            </div>

            {{-- Status do último pagamento (se existir) --}}
            @if($ultimoPagamento)
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                    </svg>
                    <div>
                        Já existe uma tentativa de pagamento (<strong>{{ $ultimoPagamento->status }}</strong>)
                        para o número <strong>{{ $ultimoPagamento->telefone }}</strong>.
                        Pode tentar novamente com outro número ou aguardar a confirmação.
                    </div>
                </div>
            @endif

            {{-- Erros do gateway --}}
            @if($errors->has('gateway'))
                <div class="alert alert-danger">{{ $errors->first('gateway') }}</div>
            @endif

            {{-- Formulário --}}
            <form method="POST" action="{{ route('pagamentos.processar', $cobranca) }}" id="formPagamento">
                @csrf

                <div class="mb-4">
                    <label for="telefone" class="form-label fw-semibold">
                        Número Multicaixa Express
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
                                <path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                            </svg>
                        </span>
                        <input
                            type="tel"
                            class="form-control form-control-lg @error('telefone') is-invalid @enderror"
                            id="telefone"
                            name="telefone"
                            placeholder="9XXXXXXXX"
                            value="{{ old('telefone', $cobranca->cliente->telefone ?? '') }}"
                            maxlength="15"
                            required
                            autocomplete="tel"
                        >
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        Introduza o número associado à conta Multicaixa Express.<br>
                        <strong>Números de teste:</strong>
                        <span class="text-success">244900000000 (sucesso)</span> ·
                        <span class="text-danger">244900000001 (sem saldo)</span>
                    </div>
                </div>

                {{-- Como funciona --}}
                <div class="alert alert-info small mb-4">
                    <strong>Como funciona:</strong>
                    <ol class="mb-0 ps-3 mt-1">
                        <li>Introduza o número de telemóvel registado no Multicaixa Express.</li>
                        <li>Clique em <strong>Pagar agora</strong>.</li>
                        <li>Receberá uma notificação push na app para autorizar o pagamento.</li>
                        <li>Após autorização, o sistema é atualizado automaticamente.</li>
                    </ol>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger btn-lg" id="btnPagar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3h16v5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V7zm3 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H3z"/>
                        </svg>
                        Pagar agora — Kz {{ $cobranca->valor_formatado }}
                    </button>
                    <a href="{{ route('cobrancas.show', $cobranca->id) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formPagamento').addEventListener('submit', function() {
    const btn = document.getElementById('btnPagar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>A processar...';
});
</script>
@endpush

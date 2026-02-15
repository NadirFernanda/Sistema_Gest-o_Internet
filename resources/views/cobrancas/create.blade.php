    <div style="display: flex; justify-content: center; margin-bottom: 18px;">
        <a href="{{ route('cobrancas.index') }}" class="btn-back-circle" title="Voltar" aria-label="Voltar">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="12" fill="#f7b500"/>
                <path d="M15 18l-6-6 6-6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
        .btn-back-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: none;
            border: none;
            box-shadow: 0 2px 8px rgba(247,181,0,0.10);
            transition: box-shadow 0.2s, transform 0.2s;
            padding: 0;
            cursor: pointer;
        }
        .btn-back-circle:hover {
            box-shadow: 0 4px 16px rgba(247,181,0,0.18);
            transform: scale(1.08);
        }
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 40px auto;">
    <h2>{{ isset($cobranca) ? 'Editar Cobrança' : 'Cadastrar Cobrança' }}</h2>
    <!-- Botão de voltar removido a pedido do usuário -->
    <style>
        /* .btn-back-circle removido */
        .btn-back-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: none;
            border: none;
            box-shadow: 0 2px 8px rgba(247,181,0,0.10);
            transition: box-shadow 0.2s, transform 0.2s;
            padding: 0;
            cursor: pointer;
        }
        .btn-back-circle:hover {
            box-shadow: 0 4px 16px rgba(247,181,0,0.18);
            transform: scale(1.08);
        }
        .form-modern input[type="text"],
        .form-modern input[type="number"],
        .form-modern input[type="date"],
        .form-modern select {
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 10px 14px;
            font-size: 1rem;
            margin-bottom: 12px;
            width: 100%;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-modern input[type="text"]:focus,
        .form-modern input[type="number"]:focus,
        .form-modern input[type="date"]:focus,
        .form-modern select:focus {
            border-color: #f7b500;
            box-shadow: 0 2px 8px rgba(247,181,0,0.10);
        }
        .form-modern label {
            font-size: 0.98rem;
            color: #222;
            margin-bottom: 2px;
            font-weight: 500;
        }
        .form-modern .form-group {
            margin-bottom: 18px;
            text-align: left;
        }
        .form-modern .btn-primary {
            background: #f7b500;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            padding: 12px 0;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(247,181,0,0.08);
            transition: background 0.2s;
        }
        .form-modern .btn-primary:hover {
            background: #e0a800;
        }
    </style>
    <form action="{{ isset($cobranca) ? route('cobrancas.update', $cobranca->id) : route('cobrancas.store') }}" method="POST" class="form-modern">
        @csrf
        @if(isset($cobranca))
            @method('PUT')
        @endif
        <div class="form-group">
            <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
            <select name="cliente_id" id="cliente_id" required>
                <option value="">Selecione o cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" 
                        {{ (old('cliente_id') ?? ($cobranca->cliente_id ?? null)) == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nome }}
                    </option>
                @endforeach
            </select>
            @error('cliente_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="descricao">Descrição <span class="text-danger">*</span></label>
            <input type="text" name="descricao" id="descricao" value="{{ old('descricao', $cobranca->descricao ?? '') }}" required>
            @error('descricao')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="valor">Valor (Kz) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="valor" id="valor" value="{{ old('valor', $cobranca->valor ?? '') }}" required>
            @error('valor')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="data_vencimento">Data de Vencimento <span class="text-danger">*</span></label>
            <input type="date" name="data_vencimento" id="data_vencimento" value="{{ old('data_vencimento', isset($cobranca) ? $cobranca->data_vencimento : '') }}" required>
            @error('data_vencimento')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="data_pagamento">Data de Pagamento</label>
            <input type="date" name="data_pagamento" id="data_pagamento" value="{{ old('data_pagamento', $cobranca->data_pagamento ?? '') }}">
            @error('data_pagamento')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" required>
                <option value="pendente" {{ (old('status', $cobranca->status ?? '') == 'pendente') ? 'selected' : '' }}>Pendente</option>
                <option value="pago" {{ (old('status', $cobranca->status ?? '') == 'pago') ? 'selected' : '' }}>Pago</option>
                <option value="atrasado" {{ (old('status', $cobranca->status ?? '') == 'atrasado') ? 'selected' : '' }}>Atrasado</option>
            </select>
            @error('status')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">{{ isset($cobranca) ? 'Atualizar Cobrança' : 'Salvar Cobrança' }}</button>
    </form>
</div>
@endsection

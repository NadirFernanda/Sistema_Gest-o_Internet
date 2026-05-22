@extends('layouts.app')

@section('content')
<div class="container" style="max-width:960px;margin:18px auto;">
    <h1>Cadastrar Cliente</h1>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:14px;border-radius:8px;background:#eafaf1;color:#218c5b;padding:12px 18px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:14px;border-radius:8px;background:#faeaea;color:#c0392b;padding:12px 18px;">
            {{ session('error') }}
        </div>
    @endif

    <form id="formClienteCreate" method="POST" action="{{ route('clientes.store') }}"
          style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.06);">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

            <div>
                <label for="bi_tipo"><strong>Tipo de documento *</strong></label>
                <select id="bi_tipo" name="bi_tipo" class="form-control">
                    <option value="BI"    {{ old('bi_tipo') == 'BI'    ? 'selected' : '' }}>BI</option>
                    <option value="NIF"   {{ old('bi_tipo') == 'NIF'   ? 'selected' : '' }}>NIF</option>
                    <option value="Outro" {{ old('bi_tipo') == 'Outro' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('bi_tipo') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="bi_numero" id="labelBiNumero"><strong>BI / NIF *</strong></label>
                <input id="bi_numero" name="bi_numero" type="text" value="{{ old('bi_numero') }}"
                       placeholder="Número do documento" class="form-control" required>
                @error('bi_numero') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div id="bi_tipo_outro_wrap" style="display:none;grid-column:1/-1;">
                <label for="bi_tipo_outro"><strong>Especificar documento *</strong></label>
                <input id="bi_tipo_outro" name="bi_tipo_outro" type="text"
                       value="{{ old('bi_tipo_outro') }}"
                       placeholder="Ex: Passaporte, Cartão Estrangeiro" class="form-control">
                @error('bi_tipo_outro') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div style="grid-column:1/-1;">
                <label for="nome"><strong>Nome completo *</strong></label>
                <input id="nome" name="nome" type="text" value="{{ old('nome') }}"
                       placeholder="Nome completo" class="form-control" required>
                @error('nome') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="email"><strong>Email *</strong></label>
                <input id="email" name="email" type="email"
                       placeholder="email@exemplo.com" class="form-control" required>
                @error('email') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="contato"><strong>Contacto (WhatsApp) *</strong></label>
                <input id="contato" name="contato" type="text"
                       placeholder="+244 9XX XXX XXX" class="form-control" required>
                @error('contato') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div style="grid-column:1/-1;">
                <label for="mikrotik_site_id"><strong>Site MikroTik</strong></label>
                @if($sites->isNotEmpty())
                    <select id="mikrotik_site_id" name="mikrotik_site_id" class="form-control" style="margin-top:4px;">
                        <option value="">— Seleccionar site —</option>
                        @foreach($sites as $siteId => $siteNome)
                            <option value="{{ $siteId }}" {{ old('mikrotik_site_id') == $siteId ? 'selected' : '' }}>
                                {{ $siteNome }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div style="margin-top:6px;padding:10px 14px;background:#fffbe7;border:1px solid #f7b500;border-radius:8px;font-size:0.9rem;color:#7a5c00;">
                        Nenhum site configurado.
                        <a href="{{ route('mikrotik.index') }}" target="_blank" style="font-weight:600;color:#7a5c00;">Criar site em /mikrotik</a>
                    </div>
                @endif
                @error('mikrotik_site_id') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

        </div>

        <div style="margin-top:16px;display:flex;gap:8px;align-items:center;">
            <button type="submit" class="btn btn-primary">Cadastrar Cliente</button>
            <a href="{{ route('clientes') }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const biTipo       = document.getElementById('bi_tipo');
    const biLabel      = document.getElementById('labelBiNumero');
    const outroWrap    = document.getElementById('bi_tipo_outro_wrap');

    function updateTipo() {
        if (biTipo.value === 'Outro') {
            biLabel.innerHTML = '<strong>Nº do documento *</strong>';
            outroWrap.style.display = 'block';
        } else {
            biLabel.innerHTML = '<strong>' + biTipo.value + ' *</strong>';
            outroWrap.style.display = 'none';
        }
    }

    biTipo.addEventListener('change', updateTipo);
    updateTipo();
});
</script>
@endpush
@endsection

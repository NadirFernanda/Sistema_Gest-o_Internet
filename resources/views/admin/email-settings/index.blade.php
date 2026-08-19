@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title'    => 'Configuração de Email',
        'subtitle' => 'Defina o servidor SMTP e teste o envio de emails do sistema.',
        'stackLeft' => true,
    ])

    <div class="clientes-toolbar" style="max-width:820px;margin:18px auto;padding:0 8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Painel</a>
    </div>

    @if (session('success'))
        <div style="max-width:820px;margin:0 auto 14px;padding:12px 16px;background:#e6f9ec;border:1px solid #34c759;border-radius:8px;color:#136c34;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div style="max-width:820px;margin:0 auto;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:28px;">
        <form method="POST" action="{{ route('email-settings.update') }}">
            @csrf @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div style="grid-column:1/-1;">
                    <div style="font-weight:700;font-size:1.05em;color:#333;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #F5A800;">
                        Servidor SMTP
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Host (servidor)</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $cfg['host']) }}"
                           placeholder="smtp.gmail.com" class="search-input" style="width:100%;" required>
                    @error('mail_host') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Porta</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $cfg['port']) }}"
                           placeholder="587" class="search-input" style="width:100%;" required>
                    @error('mail_port') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Utilizador (email)</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $cfg['username']) }}"
                           placeholder="noreply@angolawifi.ao" class="search-input" style="width:100%;">
                    @error('mail_username') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Password <span style="color:#aaa;font-size:0.85em;">(deixar vazio = manter)</span></label>
                    <input type="password" name="mail_password" autocomplete="new-password"
                           placeholder="••••••••" class="search-input" style="width:100%;">
                    @error('mail_password') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Encriptação</label>
                    <select name="mail_encryption" class="search-input" style="width:100%;">
                        <option value="tls"  {{ $cfg['encryption'] === 'tls'  ? 'selected' : '' }}>TLS (porta 587)</option>
                        <option value="ssl"  {{ $cfg['encryption'] === 'ssl'  ? 'selected' : '' }}>SSL (porta 465)</option>
                        <option value="none" {{ $cfg['encryption'] === 'none' ? 'selected' : '' }}>Nenhuma (porta 25)</option>
                    </select>
                </div>

                <div style="grid-column:1/-1;margin-top:8px;">
                    <div style="font-weight:700;font-size:1.05em;color:#333;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #F5A800;">
                        Remetente
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Nome do remetente</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $cfg['from_name']) }}"
                           placeholder="AngolaWiFi" class="search-input" style="width:100%;" required>
                    @error('mail_from_name') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:0.85em;color:#666;margin-bottom:4px;">Email do remetente</label>
                    <input type="email" name="mail_from_email" value="{{ old('mail_from_email', $cfg['from_email']) }}"
                           placeholder="noreply@angolawifi.ao" class="search-input" style="width:100%;" required>
                    @error('mail_from_email') <div style="color:#e74c3c;font-size:0.82em;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

            </div>

            <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="submit" class="btn btn-search">Guardar configurações</button>
            </div>
        </form>

        {{-- Testar envio --}}
        <div style="margin-top:28px;padding-top:20px;border-top:1px solid #eee;">
            <div style="font-weight:700;font-size:1em;color:#333;margin-bottom:12px;">Testar envio</div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="email" id="email-teste" placeholder="email@destino.com"
                       class="search-input" style="width:260px;">
                <button type="button" onclick="testarEmail()" class="btn btn-search" style="white-space:nowrap;">
                    Enviar email de teste
                </button>
            </div>
            <div id="teste-resultado" style="margin-top:10px;font-size:0.9em;display:none;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testarEmail() {
    const email = document.getElementById('email-teste').value.trim();
    const resultado = document.getElementById('teste-resultado');
    resultado.style.display = 'none';

    fetch('{{ route('email-settings.testar') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ email_teste: email }),
    })
    .then(r => r.json())
    .then(data => {
        resultado.style.display = 'block';
        if (data.success) {
            resultado.style.color = '#1c8a3c';
            resultado.textContent = '✅ ' + data.success;
        } else {
            resultado.style.color = '#e74c3c';
            resultado.textContent = '❌ ' + (data.error || 'Erro desconhecido.');
        }
    })
    .catch(() => {
        resultado.style.display = 'block';
        resultado.style.color = '#e74c3c';
        resultado.textContent = '❌ Erro ao contactar o servidor.';
    });
}
</script>
@endpush
@endsection

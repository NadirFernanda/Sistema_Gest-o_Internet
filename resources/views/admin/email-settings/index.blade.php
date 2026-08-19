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

            @if (session('teste_ok'))
                <div style="margin-bottom:12px;padding:10px 14px;background:#e6f9ec;border:1px solid #34c759;border-radius:8px;color:#136c34;font-size:0.9em;">
                    ✅ {{ session('teste_ok') }}
                </div>
            @endif
            @if (session('teste_erro'))
                <div style="margin-bottom:12px;padding:10px 14px;background:#fff0f0;border:1px solid #e74c3c;border-radius:8px;color:#c0392b;font-size:0.9em;">
                    ❌ {{ session('teste_erro') }}
                </div>
            @endif

            <form method="POST" action="{{ route('email-settings.testar') }}">
                @csrf
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <input type="email" name="email_teste" placeholder="email@destino.com"
                           value="{{ old('email_teste') }}"
                           class="search-input" style="width:260px;" required>
                    <button type="submit" class="btn btn-search" style="white-space:nowrap;">
                        Enviar email de teste
                    </button>
                </div>
                @error('email_teste')
                    <div style="color:#e74c3c;font-size:0.82em;margin-top:4px;">{{ $message }}</div>
                @enderror
            </form>
        </div>
    </div>
</div>
@endsection

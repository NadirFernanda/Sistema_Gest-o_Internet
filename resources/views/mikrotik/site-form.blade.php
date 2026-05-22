@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

@section('content')
<div class="estoque-container-moderna">

    <div style="max-width:600px;margin:24px auto 0;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <a href="{{ route('mikrotik.index') }}" class="btn btn-ghost" style="height:36px;display:inline-flex;align-items:center;gap:6px;font-size:0.9rem;">
                ← Voltar
            </a>
            <div>
                <h2 style="margin:0;font-size:1.2rem;font-weight:700;">{{ $site->exists ? 'Editar Site MikroTik' : 'Novo Site MikroTik' }}</h2>
                <p style="margin:0;font-size:0.85rem;color:#888;">Credenciais do RouterBoard instalado no site</p>
            </div>
        </div>
    </div>

    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;padding:28px 32px;box-shadow:0 4px 14px rgba(0,0,0,0.07);">

        <form method="POST" action="{{ $site->exists ? route('mikrotik.sites.update', $site) : route('mikrotik.sites.store') }}">
            @csrf
            @if($site->exists) @method('PUT') @endif

            @if(session('success'))
            <div style="background:#eafaf1;border-left:4px solid #3bb273;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;color:#1e7e50;">
                ✓ {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div style="background:#fdecea;border-left:4px solid #e05a4f;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:0.9rem;color:#c0392b;">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
            @endif

            <div style="display:grid;gap:14px;">

                <div>
                    <label style="font-size:0.88rem;font-weight:600;color:#444;">Nome do Site *</label>
                    <input type="text" name="nome" value="{{ old('nome', $site->nome) }}"
                           placeholder="ex: Sede, Kilamba, Bairro Azul"
                           class="form-control" style="margin-top:4px;" required>
                </div>

                <div>
                    <label style="font-size:0.88rem;font-weight:600;color:#444;">Localização</label>
                    <input type="text" name="localizacao" value="{{ old('localizacao', $site->localizacao) }}"
                           placeholder="ex: Condomínio Vida Pacífica I-11-2, Luanda"
                           class="form-control" style="margin-top:4px;">
                </div>

                <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end;">
                    <div>
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">IP do MikroTik *</label>
                        <input type="text" name="host" value="{{ old('host', $site->host) }}"
                               placeholder="ex: 192.168.1.1"
                               class="form-control" style="margin-top:4px;" required>
                    </div>
                    <div style="width:90px;">
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">Porta *</label>
                        <input type="number" name="port" value="{{ old('port', $site->port ?: 8728) }}"
                               class="form-control" style="margin-top:4px;" min="1" max="65535" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">Utilizador API *</label>
                        <input type="text" name="username" value="{{ old('username', $site->username ?: 'admin') }}"
                               class="form-control" style="margin-top:4px;" required>
                    </div>
                    <div>
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">Password API {{ $site->exists ? '(deixar vazio = manter)' : '*' }}</label>
                        <input type="password" name="password"
                               class="form-control" style="margin-top:4px;" {{ $site->exists ? '' : 'required' }}
                               autocomplete="new-password">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">Prefixo de username</label>
                        <input type="text" name="user_prefix" value="{{ old('user_prefix', $site->user_prefix) }}"
                               placeholder="(opcional)"
                               class="form-control" style="margin-top:4px;">
                        <div style="font-size:0.78rem;color:#888;margin-top:3px;">ex: "aw_" → utilizador: aw_244912345678</div>
                    </div>
                    <div>
                        <label style="font-size:0.88rem;font-weight:600;color:#444;">Perfil padrão *</label>
                        <input type="text" name="default_profile" value="{{ old('default_profile', $site->default_profile ?: 'default') }}"
                               class="form-control" style="margin-top:4px;" required>
                        <div style="font-size:0.78rem;color:#888;margin-top:3px;">Usado quando o template não tem perfil definido</div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="active" value="1" id="active"
                           {{ old('active', $site->active ?? true) ? 'checked' : '' }}
                           style="width:16px;height:16px;">
                    <label for="active" style="font-size:0.9rem;font-weight:600;color:#444;margin:0;">Site activo</label>
                </div>

            </div>

            <div style="margin-top:22px;">
                <button type="submit" class="btn btn-cta" style="height:40px;font-size:0.93rem;">
                    {{ $site->exists ? 'Guardar alterações' : 'Criar site' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Criar Usuário',
        'subtitle' => 'Adicionar novo usuário e atribuir papel',
        'stackLeft' => true,
    ])

    <div style="margin-top:8px;"> 
        <div style="position:relative; max-width:720px; margin:0 auto 12px;">
            <a href="{{ route('admin.users.index') }}" class="btn-icon btn-ghost" title="Voltar" aria-label="Voltar"
               style="display:inline-flex; margin:0 auto; position:relative; left:50%; transform:translateX(-50%); width:44px; height:44px; align-items:center; justify-content:center; border-radius:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="container" style="max-width:720px;margin:0 auto;">
        <form id="create-user-form" method="POST" action="{{ route('admin.users.store') }}" class="form-modern">
            @csrf
            <div class="form-group">
                <label>Nome</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>E-mail (local-part ou completo)</label>
                <input class="form-control" name="email" value="{{ old('email') }}" placeholder="ex: joao ou joao@" required>
                <small class="text-muted">Se apenas 'joao', anexaremos <strong>@sgmrtexas.angolawifi.ao</strong></small>
                @error('email')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" class="form-control" name="password" required>
                @error('password')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Confirmar senha</label>
                <input type="password" class="form-control" name="password_confirmation" required>
            </div>

            <div class="form-group">
                <label>Papel</label>
                <select name="role" class="form-control select" data-placeholder="-- Selecionar --">
                    <option value="">-- Selecionar --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Criar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/custom-select.js') }}?v={{ filemtime(public_path('js/custom-select.js')) }}"></script>
@endpush

@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
<div class="container">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Criar Usuário',
        'subtitle' => 'Adicionar novo usuário e atribuir papel'
    ])
    <div class="card mx-auto create-card" style="max-width:720px">
                <div class="card-accent"></div>
                <div class="card-top-actions" style="position:relative;">
                    <a href="javascript:history.back()" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar" style="position:absolute;right:16px;top:12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </a>
                </div>
            <div class="card-header">Criar Usuário</div>
            <div class="card-subtitle">Adicione um novo usuário e atribua um papel</div>
            
            <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form id="create-user-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nome</label>
                        <input class="form-control" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-mail (local-part ou completo)</label>
                        <input class="form-control" name="email" value="{{ old('email') }}" placeholder="ex: joao ou joao@" required>
                        <small class="text-muted">Se apenas 'joao', anexaremos <strong>@sgmrtexas.angolawifi.ao</strong></small>
                        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-control" name="password" required>
                        @error('password')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmar senha</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Papel</label>
                        <select name="role" class="form-control" data-placeholder="-- Selecionar --">
                            <option value="">-- Selecionar --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/custom-select.js') }}?v={{ filemtime(public_path('js/custom-select.js')) }}"></script>
@endpush

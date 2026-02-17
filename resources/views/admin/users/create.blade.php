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

    <div style="margin-top:8px;"> <!-- back button removed -->
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

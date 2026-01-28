@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <img src="{{ asset('img/logo.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Clientes</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>

        {{-- Se estiver na listagem de clientes --}}
        @if(isset($clientes))
            <form id="formCliente" class="form-cadastro" method="POST" action="{{ url('/clientes') }}">
                @csrf
                <input type="text" id="nomeCliente" name="nome" placeholder="Nome completo" required>
                <input type="email" id="emailCliente" name="email" placeholder="Email" required>
                <input type="text" id="contatoCliente" name="contato" placeholder="Contacto" required>
                <button type="submit">Cadastrar Cliente</button>
            </form>
            <h2 style="margin-top:32px;">Lista de Clientes</h2>
            <div class="clientes-lista" id="clientesLista">
                @if(count($clientes) > 0)
                    <ul>
                        @foreach($clientes as $c)
                            <li>
                                {{ $c->nome }} ({{ $c->contato }})
                                <a href="{{ route('clientes.show', $c->id) }}" class="btn btn-sm btn-info">Ver Ficha</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>Nenhum cliente cadastrado ainda.</p>
                @endif
            </div>
        @endif

        {{-- Se estiver na ficha de um cliente específico --}}
        @if(isset($cliente))
            <div class="ficha-cliente" style="margin-top:32px;">
                <h2>Ficha do Cliente: {{ $cliente->nome }}</h2>
                <p><strong>Email:</strong> {{ $cliente->email }}</p>
                <p><strong>Contato:</strong> {{ $cliente->contato }}</p>
                <a href="{{ route('clientes') }}" class="btn btn-secondary">Voltar à Lista</a>
                <h3 style="margin-top:24px;">Equipamentos Instalados</h3>
                <a href="{{ route('cliente_equipamento.create', $cliente->id) }}" class="btn btn-secondary">Vincular Equipamento do Estoque</a>
                <ul>
                    @forelse($cliente->equipamentos as $equipamento)
                        <li>
                            <strong>{{ $equipamento->nome }}</strong> - Morada: {{ $equipamento->morada }}
                            @if($equipamento->ponto_referencia)
                                (Ponto de referência: {{ $equipamento->ponto_referencia }})
                            @endif
                        </li>
                    @empty
                        <li>Nenhum equipamento cadastrado para este cliente.</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formCliente');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const nome = document.getElementById('nomeCliente').value;
                const email = document.getElementById('emailCliente').value;
                const contato = document.getElementById('contatoCliente').value;
                const token = document.querySelector('input[name="_token"]').value;

                fetch("{{ url('/clientes') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nome, email, contato })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else if (data.errors) {
                        let msg = 'Erro ao cadastrar cliente:\n';
                        for (const [campo, mensagens] of Object.entries(data.errors)) {
                            msg += `${campo}: ${Array.isArray(mensagens) ? mensagens.join(', ') : mensagens}\n`;
                        }
                        alert(msg);
                    } else {
                        alert('Erro desconhecido ao cadastrar cliente.');
                    }
                })
                .catch(() => alert('Erro ao cadastrar cliente.'));
            });
        }
    });
</script>
@endpush
@endsection

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Ciclo</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($planos as $plano)
        <tr>
            <td>{{ $plano->id }}</td>
            <td>{{ $plano->cliente->nome ?? '-' }}</td>
            <td>{{ $plano->descricao }}</td>
            <td>Kz {{ number_format($plano->preco, 2, ',', '.') }}</td>
            <td>{{ $plano->ciclo }}</td>
            <td>{{ $plano->data_inicio ? \Carbon\Carbon::parse($plano->data_inicio)->format('d/m/Y') : '' }}</td>
            <td>{{ $plano->data_fim ? \Carbon\Carbon::parse($plano->data_fim)->format('d/m/Y') : '' }}</td>
            <td>{{ ucfirst($plano->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

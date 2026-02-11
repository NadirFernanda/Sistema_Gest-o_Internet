<!-- Exportação Excel: tabela simples, sem layout -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Descrição</th>
            <th>Valor</th>
            <th>Vencimento</th>
            <th>Pagamento</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cobrancas as $cobranca)
        <tr>
            <td>{{ $cobranca->id }}</td>
            <td>{{ $cobranca->cliente->nome ?? '-' }}</td>
            <td>{{ $cobranca->descricao }}</td>
            <td>Kz {{ number_format($cobranca->valor, 2, ',', '.') }}</td>
            <td>{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : '' }}</td>
            <td>{{ $cobranca->data_pagamento ?? '-' }}</td>
            <td>{{ ucfirst($cobranca->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


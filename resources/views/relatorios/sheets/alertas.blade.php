<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Plano</th>
            <th>Data Vencimento</th>
            <th>Dias Restantes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alertas as $alerta)
        <tr>
            <td>{{ $alerta->id }}</td>
            <td>{{ $alerta->cliente->nome ?? '-' }}</td>
            <td>{{ $alerta->plano->descricao ?? '-' }}</td>
            <td>{{ $alerta->data_vencimento ? \Carbon\Carbon::parse($alerta->data_vencimento)->format('d/m/Y') : '' }}</td>
            <td>{{ $alerta->dias_restantes ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

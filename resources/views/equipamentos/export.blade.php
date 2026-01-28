<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Modelo</th>
            <th>Nº Série</th>
            <th>Morada</th>
            <th>Ponto de Referência</th>
            <th>Quantidade em Estoque</th>
        </tr>
    </thead>
    <tbody>
        @foreach($equipamentos as $equipamento)
        <tr>
            <td>{{ $equipamento->id }}</td>
            <td>{{ $equipamento->nome }}</td>
            <td>{{ $equipamento->descricao ?? '-' }}</td>
            <td>{{ $equipamento->modelo ?? '-' }}</td>
            <td>{{ $equipamento->numero_serie ?? '-' }}</td>
            <td>{{ $equipamento->morada ?? '-' }}</td>
            <td>{{ $equipamento->ponto_referencia ?? '-' }}</td>
            <td>{{ $equipamento->quantidade ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Modelo</th>
            <th>Nº Série</th>
            <th>Quantidade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($equipamentos as $equipamento)
        <tr>
            <td>{{ $equipamento->nome }}</td>
            <td>{{ $equipamento->descricao }}</td>
            <td>{{ $equipamento->modelo }}</td>
            <td>{{ $equipamento->numero_serie }}</td>
            <td>{{ $equipamento->quantidade }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

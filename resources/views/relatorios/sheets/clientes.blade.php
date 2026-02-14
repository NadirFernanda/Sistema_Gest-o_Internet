<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>BI</th>
            <th>Contato</th>
            <th>Email</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clientes as $cliente)
        <tr>
            <td>{{ $cliente->id }}</td>
            <td>{{ $cliente->nome }}</td>
            <td>{{ $cliente->bi }}</td>
            <td>{{ $cliente->contato }}</td>
            <td>{{ $cliente->email }}</td>
            <td>{{ $cliente->status ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

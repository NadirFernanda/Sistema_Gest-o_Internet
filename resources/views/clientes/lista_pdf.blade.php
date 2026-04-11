<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 3px solid #f7b500;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
            color: #333333;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead tr {
            background: #f7b500;
            color: #fff;
        }
        thead th {
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background: #fffbe7;
        }
        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e8e8e8;
            font-size: 10px;
        }
        .footer {
            margin-top: 18px;
            font-size: 9px;
            color: #888;
            text-align: right;
        }
        .badge-ativo {
            background: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .badge-inativo {
            background: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lista de Clientes</h1>
        <p>Total: {{ $clientes->count() }} cliente(s) &nbsp;|&nbsp; Emitido em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>BI / NIF</th>
                <th>Contato</th>
                <th>Email</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $i => $cliente)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cliente->nome }}</td>
                <td>{{ $cliente->bi ?? '—' }}</td>
                <td>{{ $cliente->contato ?? '—' }}</td>
                <td>{{ $cliente->email ?? '—' }}</td>
                <td>
                    @php $estado = strtolower(trim($cliente->estado ?? '')); @endphp
                    @if($estado === 'ativo' || $estado === 'ativa')
                        <span class="badge-ativo">Ativo</span>
                    @elseif($estado)
                        <span class="badge-inativo">{{ $cliente->estado }}</span>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado pelo sistema SGA &mdash; {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

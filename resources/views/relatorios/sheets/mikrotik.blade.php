<table>
    <thead>
        <tr>
            <th>N.º</th>
            <th>Cliente</th>
            <th>Site</th>
            <th>Plano</th>
            <th>Username MikroTik</th>
            <th>Estado</th>
            <th>Renovação</th>
            <th>Última Sync</th>
        </tr>
    </thead>
    <tbody>
        @foreach($planos as $i => $plano)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $plano->cliente?->nome ?? '—' }}</td>
            <td>{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
            <td>{{ $plano->nome }}</td>
            <td>{{ $plano->mikrotik_username }}</td>
            <td>{{ $plano->estado }}</td>
            <td>{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

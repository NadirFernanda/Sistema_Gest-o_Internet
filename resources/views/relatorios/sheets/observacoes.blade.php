<table>
    <tr>
        <td><strong>Relatório gerado em</strong></td>
        <td>{{ $meta['generated_at'] ?? now()->toDateTimeString() }}</td>
    </tr>
    <tr>
        <td><strong>Período</strong></td>
        <td>{{ $meta['period'] ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Arquivo</strong></td>
        <td>{{ $meta['filename'] ?? '-' }}</td>
    </tr>
</table>

<p>
    Observações:
</p>
<p>
    {{ $meta['note'] ?? 'Este relatório foi gerado automaticamente. Ele contém várias abas com dados extraídos do sistema. O arquivo é somente leitura e não deve ser editado.' }}
</p>

<p>
    Contagens:
</p>
<ul>
    <li>Cobranças: {{ $meta['counts']['cobrancas'] ?? 0 }}</li>
    <li>Clientes: {{ $meta['counts']['clientes'] ?? 0 }}</li>
    <li>Planos: {{ $meta['counts']['planos'] ?? 0 }}</li>
    <li>Equipamentos: {{ $meta['counts']['equipamentos'] ?? 0 }}</li>
    <li>Alertas: {{ $meta['counts']['alertas'] ?? 0 }}</li>
</ul>

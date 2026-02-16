<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Plano</th>
            <th>Dias</th>
            <th>Anterior</th>
            <th>Novo</th>
            <th>Usuário</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>
        @foreach($compensacoes as $c)
            @php
                $planoObj = $planoMap->get((string)$c->plano_id) ?? $planoMap->get((int)$c->plano_id);
                $planoNome = trim(optional($planoObj)->nome ?? '');
                if (!$planoNome) {
                    $planoNome = 'Plano #' . $c->plano_id;
                }

                $userObj = $users->get($c->user_id);
                $userName = $userObj->name ?? ($c->user_id ? 'Usuário #' . $c->user_id : '-');
                $roleName = null;
                if ($userObj && isset($userObj->roles)) {
                    $roleName = $userObj->roles->pluck('name')->first();
                }
                $displayUser = $userName;
                if ($roleName) {
                    $displayUser = ucfirst($roleName) . ': ' . $userName;
                }
            @endphp
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $planoNome }}</td>
                <td>{{ $c->dias_compensados }}</td>
                <td>{{ $c->anterior }}</td>
                <td>{{ $c->novo }}</td>
                <td>{{ $displayUser }}</td>
                <td>{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

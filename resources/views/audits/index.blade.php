@extends('layouts.app')

@section('content')
<div class="container" style="max-width:1100px;margin:28px auto;">
    <h1>Auditoria de Exclusões</h1>

    <form method="GET" class="row" style="gap:8px;align-items:center;margin:12px 0 18px 0;">
        <div style="flex:1;display:flex;gap:8px;">
            <input name="user_id" placeholder="User ID" value="{{ request('user_id') }}" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;">
            <input name="entity_type" placeholder="Entity Type" value="{{ request('entity_type') }}" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;">
            <input type="date" name="from" value="{{ request('from') }}" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;">
            <input type="date" name="to" value="{{ request('to') }}" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;">
        </div>
        <div>
            <button class="btn btn-cta">Filtrar</button>
        </div>
    </form>

    <div style="background:#fff;border-radius:12px;padding:12px;border:1px solid #e5e7eb;box-shadow:0 6px 20px rgba(17,24,39,0.04);">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid #f3f4f6;">
                    <th style="padding:10px">ID</th>
                    <th style="padding:10px">Entidade</th>
                    <th style="padding:10px">Entity ID</th>
                    <th style="padding:10px">Usuário</th>
                    <th style="padding:10px">Motivo</th>
                    <th style="padding:10px">Payload</th>
                    <th style="padding:10px">Quando</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $a)
                <tr style="border-bottom:1px solid #f7f7f7;">
                    <td style="padding:10px;vertical-align:top;">{{ $a->id }}</td>
                    <td style="padding:10px;vertical-align:top;">{{ class_basename($a->entity_type) }}</td>
                    <td style="padding:10px;vertical-align:top;">{{ $a->entity_id }}</td>
                    <td style="padding:10px;vertical-align:top;">{{ $a->user_id ?? '—' }}</td>
                    <td style="padding:10px;vertical-align:top;">{{ $a->reason ?? '—' }}</td>
                    <td style="padding:10px;vertical-align:top;max-width:360px;overflow:auto;font-family:monospace;font-size:0.85em;">{{ json_encode($a->payload) }}</td>
                    <td style="padding:10px;vertical-align:top;">{{ $a->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $audits->links() }}</div>
    </div>
</div>
@endsection

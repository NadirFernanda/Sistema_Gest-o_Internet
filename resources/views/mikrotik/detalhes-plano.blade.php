@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('mikrotik.index') }}" class="text-blue-600 hover:underline">← Voltar</a>
    </div>

    <!-- Cabeçalho com info do cliente -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $cliente->nome }}</h1>
                <p class="text-gray-600">
                    <strong>Site:</strong> {{ $cliente->mikrotikSite->nome ?? 'N/A' }} |
                    <strong>Username:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ $plano->mikrotik_username ?? 'N/A' }}</code> |
                    <strong>Plano:</strong> {{ $plano->nome ?? 'Sem plano' }}
                </p>
            </div>
            <div class="text-right">
                @if($statusOnline)
                    @if($statusOnline->is_online)
                        <div class="bg-green-100 border border-green-400 rounded-lg p-4">
                            <div class="text-green-700 font-bold text-lg">✅ ONLINE</div>
                            <div class="text-sm text-green-600">
                                Desde {{ $statusOnline->last_seen_online_at->diffForHumans() }}
                            </div>
                        </div>
                    @else
                        <div class="bg-red-100 border border-red-400 rounded-lg p-4">
                            <div class="text-red-700 font-bold text-lg">❌ OFFLINE</div>
                            <div class="text-sm text-red-600">
                                Desde {{ $statusOnline->last_seen_offline_at->diffForHumans() }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-gray-100 border border-gray-400 rounded-lg p-4">
                        <div class="text-gray-700 font-bold text-lg">⏳ Aguardando...</div>
                        <div class="text-sm text-gray-600">Primeira verificação</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    @if($statusOnline)
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-orange-600">{{ $totalOfflineEvents }}</div>
            <div class="text-sm text-gray-600">Eventos Offline</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $totalOnlineEvents }}</div>
            <div class="text-sm text-gray-600">Eventos Online</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-purple-600">
                @php
                    $days = intdiv($totalDowntime, 86400);
                    $hours = intdiv($totalDowntime % 86400, 3600);
                @endphp
                {{ $days }}d {{ $hours }}h
            </div>
            <div class="text-sm text-gray-600">Downtime Total</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-blue-600">
                @php
                    $avgDays = intdiv($avgDowntime, 86400);
                    $avgHours = intdiv($avgDowntime % 86400, 3600);
                @endphp
                {{ $avgDays }}d {{ $avgHours }}h
            </div>
            <div class="text-sm text-gray-600">Média por Evento</div>
        </div>
    </div>
    @endif

    <!-- Histórico de Eventos -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Histórico de Eventos</h2>

        @if($eventos->isEmpty())
            <div class="bg-gray-50 border border-gray-200 rounded p-4 text-center text-gray-600">
                Sem eventos registados. O cliente ainda não foi verificado.
            </div>
        @else
            <!-- Filtros -->
            <div class="flex gap-4 mb-4">
                <button onclick="filterEvents('todos')" class="filter-btn px-4 py-2 rounded bg-blue-600 text-white font-semibold" data-filter="todos">
                    Todos ({{ $eventos->count() }})
                </button>
                <button onclick="filterEvents('offline')" class="filter-btn px-4 py-2 rounded bg-gray-200 text-gray-800 font-semibold" data-filter="offline">
                    Offline ({{ $eventos->where('event_type', 'offline')->count() }})
                </button>
                <button onclick="filterEvents('online')" class="filter-btn px-4 py-2 rounded bg-gray-200 text-gray-800 font-semibold" data-filter="online">
                    Online ({{ $eventos->where('event_type', 'online')->count() }})
                </button>
            </div>

            <!-- Tabela de Eventos -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-300 bg-gray-50">
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Data/Hora</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Evento</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Duração</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventos as $evento)
                            <tr class="border-b border-gray-200 event-row" data-type="{{ $evento->event_type }}">
                                <td class="px-4 py-3 text-gray-800">
                                    <div class="font-mono text-xs">
                                        {{ $evento->occurred_at->format('d/m/Y H:i:s') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $evento->occurred_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($evento->event_type === 'online')
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold text-xs">
                                            ✅ ONLINE
                                        </span>
                                    @else
                                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full font-semibold text-xs">
                                            ❌ OFFLINE
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($evento->event_type === 'offline' && $evento->duration_seconds)
                                        @php
                                            $days = intdiv($evento->duration_seconds, 86400);
                                            $hours = intdiv($evento->duration_seconds % 86400, 3600);
                                            $mins = intdiv($evento->duration_seconds % 3600, 60);
                                        @endphp
                                        <span class="text-orange-600 font-semibold">
                                            {{ $days }}d {{ $hours }}h {{ $mins }}m
                                        </span>
                                    @elseif($evento->event_type === 'online' && $evento->duration_seconds)
                                        <span class="text-blue-600 text-xs">
                                            Recuperado após {{ $evento->getReadableDuration() }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $evento->disconnect_reason ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Timeline dos últimos 7 dias -->
            @if($eventosUltimaSemana->isNotEmpty())
            <div class="mt-8 pt-6 border-t border-gray-300">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📅 Últimos 7 Dias</h3>
                <div class="space-y-3">
                    @foreach($eventosUltimaSemana as $evento)
                        <div class="flex gap-4 items-start">
                            <div class="min-w-max">
                                <div class="text-xs font-mono text-gray-600">
                                    {{ $evento->occurred_at->format('d/m H:i') }}
                                </div>
                            </div>
                            <div class="flex-1">
                                @if($evento->event_type === 'online')
                                    <div class="bg-green-50 border-l-4 border-green-500 pl-3 py-2">
                                        <span class="text-green-700 font-semibold">✅ Voltou Online</span>
                                        @if($evento->duration_seconds)
                                            <span class="text-xs text-green-600 ml-2">
                                                Após {{ $evento->getReadableDuration() }} offline
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="bg-red-50 border-l-4 border-red-500 pl-3 py-2">
                                        <span class="text-red-700 font-semibold">❌ Saiu Offline</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </div>
</div>

<script>
function filterEvents(type) {
    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-800');
    });
    event.target.classList.remove('bg-gray-200', 'text-gray-800');
    event.target.classList.add('bg-blue-600', 'text-white');

    // Filter rows
    document.querySelectorAll('.event-row').forEach(row => {
        if (type === 'todos' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<style>
.filter-btn {
    transition: all 0.3s ease;
}
</style>
@endsection

<div class="btn-group" role="group" aria-label="Relatórios automáticos">
    <a href="{{ route('relatorios.download', 'diario') }}" class="btn btn-sm btn-primary">Relatório Diário</a>
    <a href="{{ route('relatorios.download', 'semanal') }}" class="btn btn-sm btn-secondary">Relatório Semanal</a>
    <a href="{{ route('relatorios.download', 'mensal') }}" class="btn btn-sm btn-success">Relatório Mensal</a>
</div>

@push('scripts')
<script>
// Optional: could add JS handling for login-redirect detection, but links use same-origin auth cookies.
</script>
@endpush

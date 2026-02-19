<div style="display:flex;gap:8px;align-items:center;">
    <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="btn btn-cta" title="Baixar relatório diário">Diário</a>
    <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="btn btn-ghost" title="Baixar relatório semanal">Semanal</a>
    <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="btn btn-ghost" title="Baixar relatório mensal">Mensal</a>
</div>
{{-- Relatórios automáticos: botões removidos por solicitação --}}

{{-- Se precisar restaurar os botões, reveja resources/views/relatorios/buttons.blade.php histórico no Git. --}}

<form method="POST" action="{{ route('relatorios.gerais.gerar') }}" style="display:inline;">
	@csrf
	<button type="submit" class="btn btn-block" title="Gerar agora" style="padding:10px 14px;">Gerar agora</button>
</form>

{{-- Se preferir, o botão pode enfileirar a geração em vez de executar sincronamente. --}}

@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">

        // wire modal to old and new delete buttons
        document.querySelectorAll('.btn-excluir-cliente, .actions-delete').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                if (!id) return;
                openDeleteModal(id);
            });
        });
</script>
@endpush
@endsection

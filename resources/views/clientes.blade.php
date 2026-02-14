@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">

        <style>
        /* Page-specific override: show labels next to icon buttons on Clientes page */
        .cliente-botoes-moderna .btn-icon,
        .ficha-actions .btn-icon,
        .clientes-lista .btn-icon {
            width: auto !important;
            min-width: 84px !important;
            padding: 6px 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .cliente-botoes-moderna .btn-icon span,
        .ficha-actions .btn-icon span,
        .clientes-lista .btn-icon span {
            display: inline-block !important;
            margin-left: 8px !important;
            vertical-align: middle !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // wire modal to old and new delete buttons
            document.querySelectorAll('.btn-excluir-cliente, .actions-delete').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    if (!id) return;
                    if (typeof openDeleteModal === 'function') {
                        openDeleteModal(id);
                    }
                });
            });
        });
        </script>

@endsection

Fluxo de Devolução de Equipamentos

Visão geral

O sistema deve permitir que equipamentos emprestados a clientes sejam marcados como "devolução solicitada" quando o cliente estiver inadimplente. Depois de recebida a devolução, o equipamento é reintroduzido no estoque.

Principais elementos implementados

- Migração: adiciona colunas em `cliente_equipamento` (`status`, `devolucao_solicitada_at`, `devolucao_prazo`, `motivo_requisicao`).
- Model: `app/Models/ClienteEquipamento.php` atualizado com constantes de status e casts.
- Comando agendado: `notificacao:devolucao` (já existente) agora marca equipamentos como `devolucao_solicitada`, define prazo e dispatcha audit logs.
- Rotas/UI:
  - `POST /clientes/{cliente}/solicitar-devolucao` (nome `cliente.solicitar_devolucao`) — ação admin para solicitar devolução para todos os equipamentos emprestados do cliente.
  - `POST /clientes/{cliente}/vincular-equipamento/{vinculo}/registrar-devolucao` (nome `cliente_equipamento.registrar_devolucao`) — registrar devolução recebida e restaurar estoque.
- Notificações: `ClienteDevolucaoEquipamentoEmail` e WhatsApp são disparadas quando a solicitação ocorre.
- Auditoria: `WriteAuditLogJob` é enfileirado para registrar mudanças em `cliente_equipamento`.

Boas práticas operacionais

- Enviar 2-3 avisos (e-mail, WhatsApp, SMS) antes de ação física.
- Definir prazos claros no contrato (ex.: 7 dias úteis para devolver após notificação).
- Registrar todas as tentativas de contato no `AuditLog`.
- Ao receber o equipamento, fotografe e registre o número de série e condição.
- Se o equipamento for devolvido danificado, registrar e iniciar processo de cobrança pelos danos conforme contrato.

Teste e execução local

- Rodar migrations:

```bash
php artisan migrate
```

- Processar fila de jobs (auditoria/notificações):

```bash
php artisan queue:work --sleep=3
```

- Forçar execução do comando para teste:

```bash
php artisan notificacao:devolucao
```

Notas

- Antes de iniciar retiradas físicas, consultar departamento jurídico para garantir conformidade com legislação local.
- Ajuste as mensagens das notificações para o tom e conteúdo legais desejados.

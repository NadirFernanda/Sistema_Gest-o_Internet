Auditoria (Opção 1 — Spatie + Opção 2 — Custom)

Resumo rápido
- Opção 1 (rápido): instalar e usar `spatie/laravel-activitylog` para registar atividades.
- Opção 2 (custom): usar tabela `audit_logs`, `AuditLog` model, um `AuditObserver` e middleware para garantir contexto de ator.

Opção 1 — Spatie (instalação rápida)

1. Instalar o pacote:

```bash
composer require spatie/laravel-activitylog
```

2. Publicar e migrar:

```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

3. Uso básico (em controllers/observers):

```php
activity()
  ->causedBy(auth()->user())
  ->performedOn($model)
  ->withProperties(['role' => auth()->user()->role ?? null])
  ->log('updated');
```

Observações:
- Rápido de integrar; bom para começar.
- Para ver logs: `activity()` helper, ou consultar a tabela `activity_log`.

Opção 2 — Custom (mais controle)

Arquivos adicionados ao projeto (local):
- `database/migrations/2026_02_17_000000_create_audit_logs_table.php` — migration da tabela `audit_logs`.
- `app/Models/AuditLog.php` — modelo Eloquent para `audit_logs`.
- `app/Observers/AuditObserver.php` — observer genérico (created/updated/deleted).
- `app/Http/Middleware/SetAuditActor.php` — middleware que expõe `app('audit.actor')` com `id` e `role`.
- `app/Providers/AuditServiceProvider.php` — registra observers para modelos centrais (Cliente, Cobranca, Plano, Equipamento).

Passos para ativar a solução custom

1. Migrar a tabela de auditoria:

```bash
php artisan migrate
```

2. Adicionar o middleware `SetAuditActor` ao Kernel (para requests web):

Edite `app/Http/Kernel.php` e, por exemplo, adicione em `$middlewareGroups['web']`:

```php
\App\Http\Middleware\SetAuditActor::class,
```

3. Registrar o provider (se não estiver auto-registrado): já foi adicionado dinamicamente em `AppServiceProvider::boot()` pelo código entregue. Alternativamente adicione em `config/app.php` em `providers`.

4. Ajustar quais modelos são observados:

O `AuditServiceProvider` já tenta registrar observers para `Cliente`, `Cobranca`, `Plano` e `Equipamento`. Para adicionar outros modelos, edite `app/Providers/AuditServiceProvider.php` e inclua-os.

5. Restrições e higiene:
- Não registre valores sensíveis (passwords, tokens). O observer faz `REDACTED` para campos comuns.
- Configure políticas de retenção/backup: exporte logs para S3/ELK se precisar de imutabilidade.

Exemplo de consulta rápida (auditoria por role):

```php
use App\Models\AuditLog;

$adminActions = AuditLog::where('role','Administrador')->orderBy('created_at','desc')->limit(200)->get();
```

Notas finais
- Comece com a Opção 1 para cobertura imediata, e ative a Opção 2 (custom) quando precisar de maior controle (HMACs, redaction, export/archival automatizado).
- Se quiser, eu posso: executar as mudanças para registrar observers adicionais, criar uma página administrativa de leitura de logs ou adicionar entrega para S3/ELK.

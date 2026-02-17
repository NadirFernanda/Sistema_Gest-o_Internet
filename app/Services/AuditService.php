<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Lang;

class AuditService
{
    public static function formatHumanReadable(AuditLog $audit, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $tplKey = 'audit.' . ($audit->action ?? 'generic');

        $resourceType = $audit->resource_type ?? $audit->auditable_type ?? null;
        $resourceId = $audit->resource_id ?? $audit->auditable_id ?? null;

        $params = [
            'actor' => $audit->actor_name ?? 'Sistema',
            'role' => $audit->actor_role ?? $audit->role ?? '',
            'module' => $audit->module ?? class_basename($resourceType ?? ''),
            'resource' => class_basename($resourceType ?? ''),
            'resource_id' => $resourceId ?? '',
            'when' => $audit->created_at ? $audit->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
        ];

        if (Lang::has($tplKey, $locale)) {
            return trans($tplKey, $params, $locale);
        }

        // Fallback human readable (Portuguese friendly)
        $actor = $params['actor'];
        $action = $audit->action ?? 'ação';
        $module = $params['module'];
        $time = $params['when'];

        if ($action === 'update' && is_array($audit->before ?? null) && is_array($audit->after ?? null)) {
            $changes = [];
            foreach (($audit->after ?? []) as $k => $v) {
                $before = $audit->before[$k] ?? null;
                if ($before !== $v) {
                    $beforeStr = is_scalar($before) ? (string)$before : json_encode($before, JSON_UNESCAPED_UNICODE);
                    $afterStr = is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE);
                    $changes[] = "$k: '$beforeStr' → '$afterStr'";
                }
            }
            $changeStr = implode(', ', $changes);
            return "$actor atualizou $module ({$params['resource_id']}) — $changeStr às $time";
        }

        return "$actor realizou '$action' em $module ({$params['resource_id']}) às $time";
    }

    public static function translateAction(?string $action): string
    {
        if (!$action) {
            return 'ação';
        }
        $map = [
            'created' => 'criado',
            'create' => 'criado',
            'updated' => 'atualizado',
            'update' => 'atualizado',
            'deleted' => 'excluído',
            'delete' => 'excluído',
            'restored' => 'restaurado',
            'restore' => 'restaurado',
            'login' => 'login',
            'logout' => 'logout',
            'import' => 'importado',
            'export' => 'exportado',
        ];

        $key = strtolower(trim($action));
        return $map[$key] ?? $action;
    }

    public static function translateRole(?string $role): string
    {
        if (!$role) {
            return '';
        }
        $map = [
            'administrator' => 'Administrador',
            'admin' => 'Administrador',
            'manager' => 'Gestor',
            'gestor' => 'Gestor',
            'user' => 'Utilizador',
            'cliente' => 'Cliente',
        ];

        $key = strtolower(trim($role));
        return $map[$key] ?? $role;
    }
}

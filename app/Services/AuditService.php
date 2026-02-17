<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Lang;

class AuditService
{
    public static function formatHumanReadable(AuditLog $a, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $tplKey = 'audit.' . ($a->action ?? 'generic');

        $params = [
            'actor' => $a->actor_name ?? 'Sistema',
            'role' => $a->actor_role ?? '',
            'module' => $a->module ?? '',
            'resource' => class_basename($a->resource_type ?? ''),
            'resource_id' => $a->resource_id ?? '',
            'when' => $a->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
        ];

        if (Lang::has($tplKey, $locale)) {
            return trans($tplKey, $params, $locale);
        }

        return sprintf('%s %s %s #%s at %s', $params['actor'], $a->action, $params['resource'], $params['resource_id'], $params['when']);
    }
}
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Config;

class AuditService
{
    public static function formatHumanReadable(AuditLog $audit, string $locale = 'pt'): string
    {
        // Basic example formatter; extend with i18n templates later
        $actor = $audit->actor_name ?? 'Sistema';
        $action = $audit->action;
        $module = $audit->module ?? $audit->resource_type;
        $time = $audit->created_at ? $audit->created_at->format('Y-m-d H:i:s') : '';

        if ($action === 'update' && is_array($audit->before) && is_array($audit->after)) {
            $changes = [];
            foreach ($audit->after as $k => $v) {
                $before = $audit->before[$k] ?? null;
                if ($before !== $v) {
                    $changes[] = "$k: '$before' → '$v'";
                }
            }
            $changeStr = implode(', ', $changes);
            return "$actor atualizou $module ({$audit->resource_id}) — $changeStr às $time";
        }

        return "$actor realizou '$action' em $module ({$audit->resource_id}) às $time";
    }
}

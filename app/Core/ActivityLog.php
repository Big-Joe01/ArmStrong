<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Records administrative actions and system events.
 */
final class ActivityLog
{
    public static function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $meta = []
    ): void {
        try {
            Database::insert('activity_logs', [
                'admin_id'    => Auth::id(),
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'description' => $description,
                'meta'        => $meta === [] ? null : $meta,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to write activity log: ' . $e->getMessage());
        }
    }
}

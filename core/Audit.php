<?php
/**
 * STONE ENERGY INT'L LTD
 * Audit Logging Subsystem
 */

declare(strict_types=1);

class Audit {
    /**
     * Log an administrative action
     */
    public static function log(string $action, string $module, ?string $recordId, string $description): void {
        try {
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            Database::insert('audit_logs', [
                'user_id' => $userId,
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'description' => $description,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Never break business execution if audit logging encounters an issue
            error_log("Audit Log Failure: " . $e->getMessage());
        }
    }

    /**
     * Fetch recent audit logs with pagination and user join
     */
    public static function getRecent(int $limit = 50, int $offset = 0, ?string $module = null): array {
        $sql = "SELECT a.*, u.username, u.full_name 
                FROM `audit_logs` a 
                LEFT JOIN `users` u ON a.user_id = u.id ";
        $params = [];

        if ($module) {
            $sql .= "WHERE a.module = :mod ";
            $params[':mod'] = $module;
        }

        $sql .= "ORDER BY a.id DESC LIMIT {$limit} OFFSET {$offset}";

        return Database::fetchAll($sql, $params);
    }
}

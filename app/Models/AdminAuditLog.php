<?php
namespace App\Models;

use App\Database;

class AdminAuditLog extends Database
{
    /** Registra una acción del back office. Nunca lanza: la auditoría no
     *  debe tumbar la operación que audita. */
    public static function log(int $adminId, string $action, array $detail = [], ?string $ip = null): void
    {
        try {
            $instance = new self;
            $action = $instance->dbConnection->real_escape_string($action);
            $json = $instance->dbConnection->real_escape_string(
                json_encode($detail, JSON_UNESCAPED_UNICODE)
            );
            $ipSql = $ip !== null
                ? "'" . $instance->dbConnection->real_escape_string($ip) . "'"
                : 'NULL';
            $instance->query(
                "INSERT INTO admin_audit_log (admin_id, action, detail, ip)
                 VALUES ({$adminId}, '{$action}', '{$json}', {$ipSql})"
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

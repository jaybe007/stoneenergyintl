<?php
/**
 * STONE ENERGY INT'L LTD
 * Secure PDO Database Architecture
 */

declare(strict_types=1);

class Database {
    private static ?PDO $instance = null;

    /**
     * Get singleton PDO connection instance
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');
            $db   = env('DB_NAME', 'stoneenergy_db');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log critical connection failure without leaking credentials
                error_log("Database Connection Error: " . $e->getMessage());
                if (env('APP_DEBUG', false)) {
                    die("Database connection failed. Please check your database credentials in .env. Technical detail: " . htmlspecialchars($e->getMessage()));
                } else {
                    http_response_code(500);
                    include ROOT_PATH . '500.php';
                    exit;
                }
            }
        }

        return self::$instance;
    }

    /**
     * Prepare and execute a parameterized query with arguments
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch all matching rows
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single column value
     */
    public static function fetchColumn(string $sql, array $params = [], int $col = 0): mixed {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn($col);
    }

    /**
     * Insert row and return last inserted ID
     */
    public static function insert(string $table, array $data): int {
        $columns = array_keys($data);
        $fields = implode(', ', array_map(fn($col) => "`$col`", $columns));
        $placeholders = implode(', ', array_map(fn($col) => ":$col", $columns));
        
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        
        $params = [];
        foreach ($data as $col => $val) {
            $params[":$col"] = $val;
        }

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$pdo->lastInsertId();
    }

    /**
     * Update rows by condition
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $setClauses = [];
        $params = [];
        
        foreach ($data as $col => $val) {
            $setClauses[] = "`{$col}` = :set_{$col}";
            $params[":set_{$col}"] = $val;
        }

        foreach ($whereParams as $k => $v) {
            $params[$k] = $v;
        }

        $setString = implode(', ', $setClauses);
        $sql = "UPDATE `{$table}` SET {$setString} WHERE {$where}";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    /**
     * Delete rows by condition
     */
    public static function delete(string $table, string $where, array $whereParams = []): int {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($whereParams);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}

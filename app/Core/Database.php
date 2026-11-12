<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use Throwable;

/**
 * Thin PDO wrapper. Every query goes through prepared statements.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host    = (string) Config::get('db.host', 'localhost');
        $port    = (int) Config::get('db.port', 3306);
        $name    = (string) Config::get('db.name', '');
        $charset = (string) Config::get('db.charset', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        try {
            self::$pdo = new PDO($dsn, (string) Config::get('db.user'), (string) Config::get('db.password'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            Logger::error('Database connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed.', 0, $e);
        }

        return self::$pdo;
    }

    /** @param array<string,mixed> $params */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * @param array<string,mixed> $params
     * @return array<string,mixed>|null
     */
    public static function first(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array<string,mixed> $params
     * @return list<array<string,mixed>>
     */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @param array<string,mixed> $params */
    public static function scalar(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** @param array<string,mixed> $data */
    public static function insert(string $table, array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(static fn ($c) => ':' . $c, $columns);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(static fn ($c) => '`' . $c . '`', $columns)),
            implode(', ', $placeholders)
        );
        self::run($sql, self::bindable($data));
        return (int) self::connection()->lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $where
     */
    public static function update(string $table, array $data, array $where): int
    {
        if ($data === [] || $where === []) {
            return 0;
        }
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = '`' . $column . '` = :set_' . $column;
        }
        $cond = [];
        foreach (array_keys($where) as $column) {
            $cond[] = '`' . $column . '` = :where_' . $column;
        }
        $params = [];
        foreach ($data as $k => $v) {
            $params['set_' . $k] = $v;
        }
        foreach ($where as $k => $v) {
            $params['where_' . $k] = $v;
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $set),
            implode(' AND ', $cond)
        );
        return self::run($sql, self::bindable($params))->rowCount();
    }

    /** @param array<string,mixed> $where */
    public static function delete(string $table, array $where): int
    {
        if ($where === []) {
            return 0;
        }
        $cond   = [];
        $params = [];
        foreach ($where as $column => $value) {
            $cond[]              = '`' . $column . '` = :' . $column;
            $params[$column]     = $value;
        }
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, implode(' AND ', $cond));
        return self::run($sql, self::bindable($params))->rowCount();
    }

    /** Run a callback inside a transaction, rolling back on any exception. */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        if ($pdo->inTransaction()) {
            return $callback($pdo);
        }
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Normalise values for binding (arrays/objects -> JSON, booleans -> int).
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private static function bindable(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = $value ? 1 : 0;
            } elseif (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($value instanceof \DateTimeInterface) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        return $data;
    }
}

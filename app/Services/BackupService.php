<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A pure PHP/PDO backup/restore implementation — no shell_exec, no dependency on mysqldump
 * being installed or in PATH. Statements are separated in the dump file by a sentinel line
 * rather than plain ";" so restore never has to parse SQL (which would otherwise need to
 * distinguish a statement-ending ";" from a ";" that legitimately appears inside a quoted
 * string value).
 */
final class BackupService
{
    private const STATEMENT_DELIMITER = "\n-- ###SOCIETYOS_STMT_END###\n";

    public static function dump(): string
    {
        $pdo = db();
        $sql = "-- SocietyOS database backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= 'SET FOREIGN_KEY_CHECKS=0;' . self::STATEMENT_DELIMITER;

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createRow = $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch();
            $createSql = $createRow['Create Table'] ?? null;
            if ($createSql === null) {
                continue;
            }

            $sql .= '-- Table: ' . $table . "\n";
            $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;' . self::STATEMENT_DELIMITER;
            $sql .= $createSql . ';' . self::STATEMENT_DELIMITER;

            $rows = $pdo->query('SELECT * FROM `' . $table . '`');
            foreach ($rows as $row) {
                $columns = array_map(fn ($col) => '`' . $col . '`', array_keys($row));
                $values = array_map(
                    fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                    array_values($row)
                );
                $sql .= 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES ('
                    . implode(',', $values) . ');' . self::STATEMENT_DELIMITER;
            }
        }

        $sql .= 'SET FOREIGN_KEY_CHECKS=1;' . self::STATEMENT_DELIMITER;

        return $sql;
    }

    /**
     * Executes every statement in a dump file sequentially. MySQL implicitly commits on
     * DDL (DROP/CREATE TABLE), so this cannot be a single atomic transaction — a failure
     * partway through can leave the database in a mixed state. Callers are expected to
     * take a fresh safety backup immediately before calling this, so a bad restore is
     * itself recoverable.
     */
    public static function restore(string $sqlDump): void
    {
        $pdo = db();
        $statements = explode(self::STATEMENT_DELIMITER, $sqlDump);

        foreach ($statements as $statement) {
            // Strip leading full-line comments (e.g. "-- Table: x") that share a delimited
            // chunk with the DDL/DML statement that follows them, rather than skipping the
            // whole chunk — a chunk can be "comment line(s) + real statement", not just one
            // or the other.
            $lines = array_filter(
                explode("\n", $statement),
                fn ($line) => !str_starts_with(trim($line), '--')
            );
            $statement = trim(implode("\n", $lines));

            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
        }
    }
}

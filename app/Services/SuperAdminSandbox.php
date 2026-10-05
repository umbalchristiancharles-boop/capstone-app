<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SuperAdminSandbox
{
    public function createDatabase(int $userId, string $sessionId): string
    {
        $default = DB::getDefaultConnection();
        $sourceConfig = config("database.connections.{$default}");
        $driver = $sourceConfig['driver'] ?? null;

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Super admin sandbox requires a MySQL or MariaDB connection.');
        }

        $sourceDatabase = $sourceConfig['database'] ?? null;
        if (! is_string($sourceDatabase) || $sourceDatabase === '') {
            throw new RuntimeException('The configured source database is missing.');
        }

        $sandboxHash = substr(hash_hmac('sha256', $userId . ':' . $sessionId, (string) config('app.key')), 0, 32);
        $sandboxDatabase = 'sa_sandbox_' . $sandboxHash;
        $lockName = 'sa-sandbox-' . substr(hash('sha256', $sandboxDatabase), 0, 40);
        $adminConnection = $this->adminConnectionName();
        $targetConnection = $this->targetConnectionName();
        $databaseCreated = false;

        try {
            $admin = $this->connectWithoutDatabase($default, $sourceConfig, $adminConnection);
            $lock = $admin->selectOne('SELECT GET_LOCK(?, 30) AS acquired', [$lockName]);
            if ((int) ($lock->acquired ?? 0) !== 1) {
                throw new RuntimeException('Could not acquire the super admin sandbox creation lock.');
            }

            if ($this->databaseExists($admin, $sandboxDatabase)
                && $this->hasCompleteMarker($admin, $sandboxDatabase)) {
                return $sandboxDatabase;
            }

            $admin->statement('DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($sandboxDatabase));
            $charset = $sourceConfig['charset'] ?? 'utf8mb4';
            $collation = $sourceConfig['collation'] ?? 'utf8mb4_unicode_ci';

            $admin->statement(
                'CREATE DATABASE ' . $this->quoteIdentifier($sandboxDatabase)
                . ' CHARACTER SET ' . $this->quoteIdentifier($charset)
                . ' COLLATE ' . $this->quoteIdentifier($collation)
            );
            $databaseCreated = true;

            $tables = DB::connection($default)->select(
                'SHOW FULL TABLES WHERE Table_type = ?',
                ['BASE TABLE']
            );

            $target = $this->connectToDatabase($default, $sourceConfig, $targetConnection, $sandboxDatabase);
            $target->statement('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $row) {
                $table = (string) array_values((array) $row)[0];
                $quotedTable = $this->quoteIdentifier($table);

                $target->statement(
                    'CREATE TABLE ' . $quotedTable . ' LIKE '
                    . $this->quoteIdentifier($sourceDatabase) . '.' . $quotedTable
                );
                $target->statement(
                    'INSERT INTO ' . $quotedTable . ' SELECT * FROM '
                    . $this->quoteIdentifier($sourceDatabase) . '.' . $quotedTable
                );
            }

            $target->statement('SET FOREIGN_KEY_CHECKS = 1');
            $this->copyForeignKeys($default, $sourceDatabase, $target);
            $this->copyTriggers($default, $sourceDatabase, $sandboxDatabase, $target);
            $this->copyViews($default, $sourceDatabase, $sandboxDatabase, $target);
            $target->statement(
                'CREATE TABLE `__superadmin_sandbox_meta` '
                . '(id TINYINT UNSIGNED PRIMARY KEY, completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)'
            );
            $target->statement('INSERT INTO `__superadmin_sandbox_meta` (`id`) VALUES (1)');

            return $sandboxDatabase;
        } catch (\Throwable $exception) {
            Log::error('Failed to create super admin sandbox database.', [
                'exception' => $exception->getMessage(),
            ]);
            if ($databaseCreated) {
                try {
                    $admin->statement('DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($sandboxDatabase));
                } catch (\Throwable $cleanupException) {
                    Log::error('Failed to remove an incomplete super admin sandbox database.', [
                        'exception' => $cleanupException->getMessage(),
                    ]);
                }
            }
            throw new RuntimeException('Could not create an isolated super admin sandbox.', 0, $exception);
        } finally {
            try {
                DB::connection($adminConnection)->select('SELECT RELEASE_LOCK(?)', [$lockName]);
            } catch (\Throwable) {
            }
            $this->forgetConnection($adminConnection);
            $this->forgetConnection($targetConnection);
        }
    }

    public function activate(string $database): array
    {
        if (! preg_match('/\Asa_sandbox_[a-f0-9]{32}\z/', $database)) {
            throw new RuntimeException('Invalid super admin sandbox database identifier.');
        }

        $default = DB::getDefaultConnection();
        $configKey = "database.connections.{$default}.database";
        $originalDatabase = config($configKey);

        Config::set($configKey, $database);
        DB::purge($default);

        return [$default, $originalDatabase];
    }

    public function restore(array $connectionState): void
    {
        [$default, $originalDatabase] = $connectionState;

        DB::purge($default);
        Config::set("database.connections.{$default}.database", $originalDatabase);
        DB::purge($default);
    }

    public function dropDatabase(string $database): void
    {
        if (! preg_match('/\Asa_sandbox_[a-f0-9]{32}\z/', $database)) {
            throw new RuntimeException('Invalid super admin sandbox database identifier.');
        }

        $default = DB::getDefaultConnection();
        $sourceConfig = config("database.connections.{$default}");
        $adminConnection = $this->adminConnectionName();

        try {
            $admin = $this->connectWithoutDatabase($default, $sourceConfig, $adminConnection);
            $admin->statement('DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($database));
        } finally {
            $this->forgetConnection($adminConnection);
        }
    }

    private function copyForeignKeys(string $default, string $sourceDatabase, $target): void
    {
        $foreignKeys = DB::connection($default)->select(
            'SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME, '
            . 'k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, k.ORDINAL_POSITION, '
            . 'r.UPDATE_RULE, r.DELETE_RULE '
            . 'FROM information_schema.KEY_COLUMN_USAGE k '
            . 'JOIN information_schema.REFERENTIAL_CONSTRAINTS r '
            . 'ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA '
            . 'AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME '
            . 'AND r.TABLE_NAME = k.TABLE_NAME '
            . 'WHERE k.CONSTRAINT_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL '
            . 'ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION',
            [$sourceDatabase]
        );

        $groups = [];
        foreach ($foreignKeys as $key) {
            $index = $key->TABLE_NAME . "\0" . $key->CONSTRAINT_NAME;
            $groups[$index]['table'] = $key->TABLE_NAME;
            $groups[$index]['name'] = $key->CONSTRAINT_NAME;
            $groups[$index]['columns'][] = $key->COLUMN_NAME;
            $groups[$index]['referenced_table'] = $key->REFERENCED_TABLE_NAME;
            $groups[$index]['referenced_columns'][] = $key->REFERENCED_COLUMN_NAME;
            $groups[$index]['update_rule'] = $key->UPDATE_RULE;
            $groups[$index]['delete_rule'] = $key->DELETE_RULE;
        }

        foreach ($groups as $foreignKey) {
            $columns = implode(', ', array_map([$this, 'quoteIdentifier'], $foreignKey['columns']));
            $referencedColumns = implode(', ', array_map([$this, 'quoteIdentifier'], $foreignKey['referenced_columns']));
            $updateRule = $this->foreignKeyRule($foreignKey['update_rule']);
            $deleteRule = $this->foreignKeyRule($foreignKey['delete_rule']);

            $target->statement(
                'ALTER TABLE ' . $this->quoteIdentifier($foreignKey['table'])
                . ' ADD CONSTRAINT ' . $this->quoteIdentifier($foreignKey['name'])
                . ' FOREIGN KEY (' . $columns . ') REFERENCES '
                . $this->quoteIdentifier($foreignKey['referenced_table'])
                . ' (' . $referencedColumns . ')'
                . ' ON DELETE ' . $deleteRule . ' ON UPDATE ' . $updateRule
            );
        }
    }

    private function databaseExists($connection, string $database): bool
    {
        return (bool) $connection->selectOne(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$database]
        );
    }

    private function hasCompleteMarker($connection, string $database): bool
    {
        $markerTableExists = $connection->selectOne(
            'SELECT TABLE_NAME FROM information_schema.TABLES '
            . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, '__superadmin_sandbox_meta']
        );
        if (! $markerTableExists) {
            return false;
        }

        return (bool) $connection->selectOne(
            'SELECT id FROM ' . $this->quoteIdentifier($database)
            . '.`__superadmin_sandbox_meta` WHERE id = 1'
        );
    }

    private function copyTriggers(string $default, string $sourceDatabase, string $sandboxDatabase, $target): void
    {
        $triggers = DB::connection($default)->select(
            'SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ?',
            [$sourceDatabase]
        );

        foreach ($triggers as $trigger) {
            $definition = DB::connection($default)->selectOne(
                'SHOW CREATE TRIGGER '
                . $this->quoteIdentifier($sourceDatabase) . '.'
                . $this->quoteIdentifier($trigger->TRIGGER_NAME)
            );
            $statement = $this->findDefinition($definition);
            if (! $statement) {
                throw new RuntimeException('Unable to read a source database trigger definition.');
            }

            $statement = $this->rewriteSchemaReferences($statement, $sourceDatabase, $sandboxDatabase);
            $statement = preg_replace('/\s+DEFINER\s*=\s*`[^`]+`@`[^`]+`/i', '', $statement);
            $target->unprepared($statement);
        }
    }

    private function copyViews(string $default, string $sourceDatabase, string $sandboxDatabase, $target): void
    {
        $views = DB::connection($default)->select(
            'SHOW FULL TABLES WHERE Table_type = ?',
            ['VIEW']
        );

        foreach ($views as $row) {
            $view = (string) array_values((array) $row)[0];
            $definition = DB::connection($default)->selectOne(
                'SHOW CREATE VIEW '
                . $this->quoteIdentifier($sourceDatabase) . '.'
                . $this->quoteIdentifier($view)
            );
            $statement = $this->findDefinition($definition);
            if (! $statement) {
                throw new RuntimeException('Unable to read a source database view definition.');
            }

            $statement = $this->rewriteSchemaReferences($statement, $sourceDatabase, $sandboxDatabase);
            $statement = preg_replace('/\s+DEFINER\s*=\s*`[^`]+`@`[^`]+`/i', '', $statement);
            $target->unprepared($statement);
        }
    }

    private function findDefinition(?object $definition): ?string
    {
        foreach ((array) $definition as $key => $value) {
            if (is_string($value) && (str_contains(strtolower((string) $key), 'create')
                || str_contains(strtolower((string) $key), 'statement'))) {
                return $value;
            }
        }

        return null;
    }

    private function rewriteSchemaReferences(string $statement, string $sourceDatabase, string $sandboxDatabase): string
    {
        return str_replace(
            ['`' . $sourceDatabase . '`.'],
            ['`' . $sandboxDatabase . '`.'],
            $statement
        );
    }

    private function foreignKeyRule(string $rule): string
    {
        $rule = strtoupper($rule);
        if (! in_array($rule, ['CASCADE', 'SET NULL', 'NO ACTION', 'RESTRICT', 'SET DEFAULT'], true)) {
            throw new RuntimeException('Unsupported foreign key rule in source database.');
        }

        return $rule;
    }

    private function connectWithoutDatabase(string $default, array $sourceConfig, string $connectionName)
    {
        $config = $sourceConfig;
        $config['database'] = null;
        unset($config['url']);

        Config::set("database.connections.{$connectionName}", $config);

        return DB::connection($connectionName);
    }

    private function connectToDatabase(string $default, array $sourceConfig, string $connectionName, string $database)
    {
        $config = $sourceConfig;
        $config['database'] = $database;
        unset($config['url']);

        Config::set("database.connections.{$connectionName}", $config);
        DB::purge($connectionName);

        return DB::connection($connectionName);
    }

    private function adminConnectionName(): string
    {
        return 'superadmin_sandbox_admin';
    }

    private function targetConnectionName(): string
    {
        return 'superadmin_sandbox_target';
    }

    private function forgetConnection(string $connectionName): void
    {
        DB::purge($connectionName);

        $connections = config('database.connections', []);
        unset($connections[$connectionName]);
        Config::set('database.connections', $connections);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}

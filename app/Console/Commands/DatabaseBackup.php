<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup 
                            {--path= : Custom path for the backup file} 
                            {--compress : Compress the backup with gzip}';

    protected $description = 'Create a MySQL database backup';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('backups');
        $compress = $this->option('compress');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', 3306);

        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$database}_backup_{$timestamp}.sql";
        if ($compress) {
            $filename .= '.gz';
        }
        $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $envPassword = $password ? '--password=' . escapeshellarg($password) : '--no-password';

        $mysqldump = 'mysqldump';
        $command = "{$mysqldump} --user=" . escapeshellarg($username) . " {$envPassword} --host=" . escapeshellarg($host) . " --port={$port} " . escapeshellarg($database);

        if ($compress) {
            $command .= " | gzip > " . escapeshellarg($fullPath);
        } else {
            $command .= " > " . escapeshellarg($fullPath);
        }

        $this->info("Creating backup: {$filename}");

        $exitCode = null;
        $output = [];
        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup failed!');
            $this->error(implode("\n", $output));

            $this->info('Trying alternative method via PHP...');

            return $this->backupViaPhp($path, $database);
        }

        if (file_exists($fullPath) && filesize($fullPath) > 0) {
            $size = $this->formatBytes(filesize($fullPath));
            $this->info("Backup created successfully: {$fullPath} ({$size})");
            return self::SUCCESS;
        }

        $this->error('Backup file is empty or was not created.');
        return self::FAILURE;
    }

    private function backupViaPhp(string $path, string $database): int
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$database}_backup_{$timestamp}.sql";
        $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = config('database.connections.mysql.database');
            $tableKey = "Tables_in_{$databaseName}";

            $sql = "-- CondoPro Database Backup\n-- Generated: " . now()->toDateTimeString() . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createKey = "Create Table";
                $sql .= "-- Table: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->$createKey . ";\n\n";

                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ((array) $row as $value) {
                            $values[] = $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                        }
                        $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($fullPath, $sql);
            $size = $this->formatBytes(filesize($fullPath));
            $this->info("PHP backup created: {$fullPath} ({$size})");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("PHP backup failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TenantBackup extends Command
{
    protected $signature = 'tenant:backup {id : Tenant ID}';
    protected $description = 'Export a tenant database to a SQL backup file';

    public function handle(): int
    {
        $tenant = Tenant::find($this->argument('id'));
        if (! $tenant) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        $dbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
        $file = "backups/{$dbName}-" . now()->format('Ymd-His') . '.sql';
        $fullPath = storage_path('app/' . $file);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        $cmd = sprintf(
            'mysqldump -h%s -P%s -u%s %s %s > %s 2>&1',
            escapeshellarg($host), escapeshellarg($port), escapeshellarg($user),
            $pass ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($dbName),
            escapeshellarg($fullPath)
        );

        exec($cmd, $out, $code);

        if ($code !== 0) {
            $this->error('mysqldump failed: ' . implode("\n", $out));

            return self::FAILURE;
        }

        $this->info("Backup created: {$file} (" . round(filesize($fullPath) / 1024, 1) . ' KB)');

        return self::SUCCESS;
    }
}

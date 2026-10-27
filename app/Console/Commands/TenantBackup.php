<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TenantBackup extends Command
{
    protected $signature = 'tenant:backup {id? : Tenant ID} {--all : Backup every tenant} {--s3 : Upload to the s3 disk (requires AWS_* env)}';
    protected $description = 'Export a tenant database to a SQL backup file';

    public function handle(): int
    {
        if ($this->option('all')) {
            $fail = 0;
            Tenant::all()->each(fn ($t) => $fail += $this->backupTenant($t) ? 0 : 1);

            return $fail ? self::FAILURE : self::SUCCESS;
        }

        $tenant = Tenant::find($this->argument('id'));
        if (! $tenant) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        return $this->backupTenant($tenant) ? self::SUCCESS : self::FAILURE;
    }

    protected function backupTenant(Tenant $tenant): bool
    {
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

            return false;
        }

        $this->info("Backup created: {$file} (" . round(filesize($fullPath) / 1024, 1) . ' KB)');

        if ($this->option('s3')) {
            try {
                Storage::disk('s3')->put($file, fopen($fullPath, 'r'));
                $this->info("Uploaded to s3: {$file}");
            } catch (\Throwable $e) {
                $this->warn("S3 upload failed (kept local copy): {$e->getMessage()}");
            }
        }

        return true;
    }
}

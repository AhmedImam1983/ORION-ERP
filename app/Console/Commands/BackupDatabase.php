<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database {--keep=7 : عدد النسخ المحتفظ بها}';
    protected $description = 'عمل نسخة احتياطية من قاعدة البيانات';

    public function handle(): void
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);

        $filename  = 'backup_' . $database . '_' . now()->format('Y_m_d_H_i_s') . '.sql';
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($filepath)) {
            $size = round(filesize($filepath) / 1024, 2);
            $this->info("✅ تم عمل النسخة الاحتياطية: {$filename} ({$size} KB)");

            // حذف النسخ القديمة
            $keep  = (int) $this->option('keep');
            $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
            if (count($files) > $keep) {
                usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
                $toDelete = array_slice($files, 0, count($files) - $keep);
                foreach ($toDelete as $old) {
                    unlink($old);
                    $this->line('🗑️ تم حذف: ' . basename($old));
                }
            }
        } else {
            $this->error('❌ فشل عمل النسخة الاحتياطية!');
        }
    }
}
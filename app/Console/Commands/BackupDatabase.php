<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';
    protected $description = 'Backup the database to a SQL file';

    public function handle()
    {
        $filename = "backup-" . now()->format('Y-m-d-H-i-s') . ".sql";
        $path = storage_path('app/backups/');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $command = sprintf(
            'mysqldump --user=%s --password=%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $path . $filename
        );

        $returnVar = NULL;
        $output  = NULL;
        exec($command, $output, $returnVar);

        if($returnVar === 0) {
            $this->info("Backup successfully created: " . $filename);
        } else {
            $this->error("Backup failed. Make sure mysqldump is installed and in your PATH.");
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear logs (./storage/logs/laravel.log)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info($this->description);
//        exec('echo "" > ' . storage_path('logs/laravel.log'));
        file_put_contents(storage_path('logs/laravel.log'), '');
        $this->info('Logs have been cleared');
    }
}

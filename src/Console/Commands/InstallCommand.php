<?php

namespace YourVendor\ESadad\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esadad:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the e-SADAD payment package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Installing e-SADAD Payment Package...');
        $this->info('----------------------------------------');

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'YourVendor\\ESadad\\Providers\\ESadadServiceProvider',
            '--tag' => 'esadad-config',
            '--force' => $this->option('force'),
        ]);

        // Publish views
        if ($this->confirm('Publish package views?', true)) {
            $this->call('vendor:publish', [
                '--provider' => 'YourVendor\\ESadad\\Providers\\ESadadServiceProvider',
                '--tag' => 'esadad-views',
                '--force' => $this->option('force'),
            ]);
        }

        // Publish assets
        if ($this->confirm('Publish package assets?', true)) {
            $this->call('vendor:publish', [
                '--provider' => 'YourVendor\\ESadad\\Providers\\ESadadServiceProvider',
                '--tag' => 'esadad-assets',
                '--force' => $this->option('force'),
            ]);
        }

        // Run migrations
        if ($this->confirm('Run database migrations?', true)) {
            $this->call('migrate');
        }

        $this->newLine();
        $this->info('e-SADAD Payment Package installed successfully!');
        $this->info('Next steps:');
        $this->line('1. Update your .env file with your e-SADAD credentials');
        $this->line('2. Add the route `Route::esadad();` to your routes/web.php file');
        $this->line('3. Visit /esadad/form to test the payment flow');

        return 0;
    }
}

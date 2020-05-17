<?php

namespace Haruncpi\LaravelSimpleFilemanager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FilemanagerInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filemanager:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will publish assets & run a migration for filemanager';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $migrationFile = "2020_05_02_100001_create_filemanager_table.php";
        //config
        if (File::exists(config_path('filemanager.php'))) {
            $confirm = $this->confirm("filemanager.php already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishConfig();
                $this->info("config overwrite finished");
            } else {
                $this->info("skipped config publish");
            }
        } else {
            $this->publishConfig();
            $this->info("config published");
        }

        //assets
        if (File::exists(public_path('filemanager'))) {
            $confirm = $this->confirm("filemanager directory already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishAssets();
                $this->info("assets overwrite finished");
            } else {
                $this->info("skipped assets publish");
            }
        } else {
            $this->publishAssets();
            $this->info("assets published");
        }

        //migration
        if (File::exists(database_path("migrations/$migrationFile"))) {
            $confirm = $this->confirm("migration file already exist. Do you want to overwrite?");
            if ($confirm) {
                $this->publishMigration();
                $this->info("migration overwrite finished");
            } else {
                $this->info("skipped migration publish");
            }
        } else {
            $this->publishMigration();
            $this->info("migration published");
        }

        $this->call('migrate');
    }

    private function publishConfig()
    {
        $this->call('vendor:publish', [
            '--provider' => "Haruncpi\LaravelSimpleFilemanager\ServiceProvider",
            '--tag'      => 'config',
            '--force'    => true
        ]);
    }

    private function publishMigration()
    {
        $this->call('vendor:publish', [
            '--provider' => "Haruncpi\LaravelSimpleFilemanager\ServiceProvider",
            '--tag'      => 'migrations',
            '--force'    => true
        ]);
    }

    private function publishAssets()
    {
        $this->call('vendor:publish', [
            '--provider' => "Haruncpi\LaravelSimpleFilemanager\ServiceProvider",
            '--tag'      => 'assets',
            '--force'    => true
        ]);
    }
}

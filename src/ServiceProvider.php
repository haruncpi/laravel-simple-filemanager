<?php

namespace Haruncpi\LaravelSimpleFilemanager;

use Haruncpi\LaravelSimpleFilemanager\Console\FilemanagerInstall;
use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config';
    const MIGRATION_PATH = __DIR__ . '/../migrations';
    const ROUTE_PATH = __DIR__ . '/../routes';
    const VIEW_PATH = __DIR__ . '/views';
    const ASSET_PATH = __DIR__ . '/../assets';
    const TRANSLATION_PATH = __DIR__ . '/../translations';


    private function publish()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path()
        ], 'config');

        $this->publishes([
            self::MIGRATION_PATH => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            self::ASSET_PATH => public_path('filemanager')
        ], 'assets');
    }

    public function boot()
    {

        $this->publish();
        $this->loadRoutesFrom(self::ROUTE_PATH . '/web.php');
        $this->loadViewsFrom(self::VIEW_PATH, 'filemanager');
        $this->loadTranslationsFrom(self::TRANSLATION_PATH,'filemanager');

        Blade::directive('FilemanagerScript', function ($expression) {
            $output= "<script src=\"{{asset('filemanager/bundle/filemanager.min.js')}}\"></script>";
            $output .= "<script> filemanager.baseUrl = '{{route('filemanager.base_route')}}';</script>";
            return $output;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH . '/filemanager.php',
            'filemanager'
        );

        $this->commands([FilemanagerInstall::class]);
    }
}
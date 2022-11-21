<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $helperFileNames = ['StringFunctions'];
        foreach ($helperFileNames as $helperFileName) {
            $file = app_path("Helpers/$helperFileName.php");
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    public function boot(): void
    {
    }
}

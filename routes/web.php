<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrate', function () {
    Artisan::call('migrate --force');
    return ' Migration Done!';
});

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return ' Storage Linked!';
});

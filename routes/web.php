<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrate', function () {
    try {
        Artisan::call('migrate --force');
        return ' Migration Done!';
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

Route::get('/link-storage', function () {
    try {
        Artisan::call('storage:link');
        return response('Storage linked!', 200);
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

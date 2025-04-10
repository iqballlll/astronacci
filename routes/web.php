<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/create-session-table', function () {
    try {
        \Artisan::call('session:table');
        \Artisan::call('migrate', ['--force' => true]);
        return 'Session table created and migrated!';
    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Gagal',
            'error' => $th->getMessage()
        ], 500);
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

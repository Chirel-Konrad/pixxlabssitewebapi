<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/_debug/phpinfo', function() {
    if (app()->environment('production')) {
        return response('disabled in production', 403);
    }
    return phpinfo();
});

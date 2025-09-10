<?php

use Illuminate\Support\Facades\Route;

Route::get(uri: '/', action: function () {
    return [
        'framework' => app()->version(),
        'php' => PHP_VERSION,
    ];
});

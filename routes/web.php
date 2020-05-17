<?php

Route::group([
    'namespace'  => '\Haruncpi\LaravelSimpleFilemanager\Controllers',
    'middleware' => config('filemanager.middleware')
], function () {
    Route::get(config('filemanager.base_route'), 'FilemanagerController@getIndex')
        ->name('filemanager.base_route');

    Route::post(config('filemanager.base_route'), 'FilemanagerController@postAction')
        ->name('filemanager.action_route');
});
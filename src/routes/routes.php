<?php

Route::group(['middleware' => 'web'], function () {

    Route::get('/plugisto', 'Gerpo\Plugisto\Controllers\PlugistoController@index');
    Route::put('/plugisto', 'Gerpo\Plugisto\Controllers\PlugistoController@update');
    Route::delete('/plugisto/{plugisto}', 'Gerpo\Plugisto\Controllers\PlugistoController@destroy');

    Route::get('/dashboard', 'Gerpo\Plugisto\Controllers\DashboardController@index');
});



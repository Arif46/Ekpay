<?php

use Illuminate\Support\Facades\Route;

//Route::group(['middleware'  =>  'token'], function () {

    Route::group(['prefix' => '/stall-participant', 'namespace' => 'StallParticipant'], function () {

        //apply stall routes
        Route::group(['prefix' => '/apply-stall'], function () {
            Route::get('/profile-info', 'ApplyForStallController@profileInfo');
            Route::post('/store', 'ApplyForStallController@store');
            Route::put('/update/{id}', 'ApplyForStallController@update');
            Route::get('/list', 'ApplyForStallController@index');
            Route::get('/show/{id}', 'ApplyForStallController@show');
            Route::get('/stall-info/{id}', 'ApplyForStallController@stallInfo');
        });

         // Payment Status Routes...
         Route::group(['prefix' => '/payment-status'], function() {
            Route::get('/success', 'StallPaymentController@success');
            Route::get('/cancel', 'StallPaymentController@cancel');
            Route::get('/decline', 'StallPaymentController@decline');
            Route::get('/pending-payment', 'StallPaymentController@onlinePaymentPending');
         });

          // Payment Routes...
          Route::group(['prefix' => '/payment'], function() {
            Route::get('/stall-list', 'StallPaymentController@getIndex');
            Route::post('/online', 'StallPaymentController@onlinePayment');
         });
    }); 

//});
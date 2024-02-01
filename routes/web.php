<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoCompleteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(array('prefix' => 'searchpersons'), function($route) {

    Route::get('/', function () {
        return view('welcome');
    });

    $route->get('search', [AutoCompleteController::class, 'index']);

    $route->get('suggest', 'App\Http\Controllers\Person\SuggestController@suggest');

    //Route::get('saveuwperson', 'App\Http\Controllers\Person\SuggestController@saveuwperson');


    

    $route->get('suggest-uw',    [App\Http\Controllers\Person\UwsuggestController::class, 'suggest']  );
    
    $route->get('import-uw',    [App\Http\Controllers\Person\UwimportController::class, 'import']  );


});


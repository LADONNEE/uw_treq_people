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

    Route::get('search', [AutoCompleteController::class, 'index']);

    Route::get('suggest',    [App\Http\Controllers\Person\SuggestController::class, 'suggest']  );

});
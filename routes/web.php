<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Enums\UserActive;
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

Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'prefix' => 'auth',
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'jwt.vertifyJWTToken'], function () {
    Route::post('sendMessage', [HomeController::class, 'sendMessage']);
    Route::get('userUnreadMessagesCount', [HomeController::class, 'getUnReadMessageCount']);
    Route::get('/fetch/message/conversation/{id}', [HomeController::class, 'getMessageByConversationId']);
    Route::get('/fetch/conversationWithNewMessage', [HomeController::class, 'getConversationWithNewMessage']);
    Route::get('cuser/fetch/user', [HomeController::class, 'getUserByCUser']);
    Route::get('refreshToken', [HomeController::class, 'refresh']);
    Route::put('/user/online', function(){
        return App::call('App\Http\Controllers\HomeController@setUserActivityStatus', ['status' => UserActive::ACTIVE]);
    });
    Route::put('/user/offine/', function(){
        return App::call('App\Http\Controllers\HomeController@ ', ['status' => UserActive::INACTIVE]);
    });
    Route::put('user/offline', [HomeController::class, 'setUserActivityStatus'], ['status' => UserActive::INACTIVE]);
    Route::post('createConversation', [HomeController::class, 'createConversation']);
    Route::get('user-profile', [HomeController::class, 'userProfile']);
});


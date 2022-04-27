<?php

use App\Http\Controllers\Custom\ShortResponse;
use App\Http\Controllers\GateController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\Tourniquet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Roles Routing
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'roles');
    Route::get('/roles/{role:name}', 'usersByRole')->middleware('auth:sanctum', 'abilities:role-admin')->missing(fn() => ShortResponse::json(false, 'Role not found', Role::all()));
    Route::post('/roles/new', 'create')->middleware('auth:sanctum', 'abilities:role-admin');
    Route::patch('/roles/{role}', 'update')->middleware('auth:sanctum', 'abilities:role-admin')->missing(fn() => ShortResponse::json(false, 'Role not found', Role::all()));
    Route::delete('/roles/{role}', 'delete')->middleware('auth:sanctum', 'abilities:role-admin');
});

// User Routing
Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'users')->middleware('auth:sanctum', 'ability:role-admin');
    Route::get('/users/me', 'getSelf')->middleware('auth:sanctum');
    Route::get('/users/{userid:id}', 'userById')->whereNumber('userid')->middleware('auth:sanctum', 'ability:role-admin')->missing(fn() => ShortResponse::errorMessage('User not found'));
    Route::post('/users/register', 'register');
    Route::post('/users/login', 'login');
    Route::patch('/users/{user:id}/role', 'editRole')->whereNumber('user')->middleware('auth:sanctum', 'abilities:role-admin')->missing(fn() => ShortResponse::errorMessage('User not found'));
    Route::patch('/users/{user:id}/edit', 'update')->whereNumber('user')->middleware('auth:sanctum')->missing(fn() => ShortResponse::errorMessage('User not found'));
    Route::patch('/users/{user:id}/password', 'changePassword')->whereNumber('user')->middleware('auth:sanctum')->missing(fn() => ShortResponse::errorMessage('User not found'));
    Route::delete('/users/{user}', 'delete')->whereNumber('user')->middleware('auth:sanctum');
});


// Gate entry
Route::controller(GateController::class)->group(function () {
    Route::get('/getTtid/{key}', function (string $key) {
        if ($key != Tourniquet::first()->key)
            return ShortResponse::errorMessage('error');

        return ShortResponse::json(['code' => GateController::createTtId(Tourniquet::first())->ttid]);
    });

    Route::get('/records', 'records')->middleware('auth:sanctum');
    Route::get('/qr/{ttid}', 'qr_scan')->middleware('auth:sanctum');
    Route::post('/scan', 'scan');
    Route::post('/records/interval', 'interval')->middleware('auth:sanctum');
});

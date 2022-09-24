<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SkillTypeController;
use App\Http\Controllers\UserTypeController;
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

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/user-type/become', [UserTypeController::class, 'become']);
    Route::post('/user-type/remove', [UserTypeController::class, 'remove']);

    Route::post('/job/create', [JobController::class, 'create']);
    Route::post('/job/{job_id}/update', [JobController::class, 'update']);
    Route::delete('/job/{job_id}', [JobController::class, 'delete']);
    Route::get('/job', [JobController::class, 'getJob']);
    Route::get('/jobs', [JobController::class, 'getJobs']);

    Route::post('/skill_type/create', [SkillTypeController::class, 'create']);
    Route::post('/skill_type/{skill_type_id}/update', [SkillTypeController::class, 'update']);
    Route::delete('/skill_type/{skill_type_id}', [SkillTypeController::class, 'delete']);
    Route::get('/skill_type', [SkillTypeController::class, 'getSkillType']);
    Route::get('/skill_types', [SkillTypeController::class, 'getSkillTypes']);

    Route::post('/skill/create', [SkillController::class, 'create']);
    Route::post('/skill/{skill_id}/update', [SkillController::class, 'update']);
    Route::delete('/skill/{skill_id}', [SkillController::class, 'delete']);
    Route::get('/skill', [SkillController::class, 'getSkill']);
    Route::get('/skills', [SkillController::class, 'getSkills']);

    Route::group([
        'prefix' => 'admin',
        'middleware' => "isadmin"
    ], function() {

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/generalinfo', [AdminController::class, 'getGeneralInfo']);
        Route::get('/verify', [AdminController::class, 'verify']);
    });
    
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/user/{username}', [AuthController::class, 'getAUser']);


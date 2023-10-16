<?php

use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LevelCollectionController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SkillTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTypeController;
use App\Models\Company;
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

// todo add rate limiting
Route::get('/projects/{project_id}/{type}', [ProjectController::class, 'getParticipants'])
    ->whereIn("type", ProjectParticipantEnum::types());
Route::get('/projects/{project_id}', [ProjectController::class, 'getProject']);
Route::get('/projects', [ProjectController::class, 'getProjects']);

Route::get('/companies/{company_id}/{type}', [CompanyController::class, 'getMembers'])
    ->whereIn("type", RelationshipTypeEnum::types());
Route::get('/companies/{company_id}/projects/{type}', [CompanyController::class, 'getCompanyProjects'])
    ->whereIn("type", Company::PROJECTTYPES);
Route::get('/companies/{company_id}', [CompanyController::class, 'getCompany']);
Route::get('/companies', [CompanyController::class, 'getCompanies']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/user/{username}', [UserController::class, 'getAUser']);

Route::middleware('auth:sanctum')->group( function() {

    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/user-type/become', [UserTypeController::class, 'become']);
    Route::post('/user-type/remove', [UserTypeController::class, 'remove']);

    Route::post('/user/{id}/edit-info', [UserController::class, 'editInfo']);
    Route::post('/user/{id}/reset-password', [UserController::class, 'resetPassword']);

    Route::post('/job/create', [JobController::class, 'create']);
    Route::post('/job/{job_id}/update', [JobController::class, 'update']);
    Route::post('/job/{job_id}/attach', [JobController::class, 'attach']);
    Route::post('/job/{job_id}/detach', [JobController::class, 'detach']);
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

    Route::post('/level/create', [LevelController::class, 'create']);
    Route::post('/level/{level_id}/update', [LevelController::class, 'update']);
    Route::delete('/level/{level_id}', [LevelController::class, 'delete']);
    Route::get('/level', [LevelController::class, 'getLevel']);
    Route::get('/levels', [LevelController::class, 'getLevels']);

    Route::post('/level_collection/create', [LevelCollectionController::class, 'create']);
    Route::post('/level_collection/{level_collection_id}/update', [LevelCollectionController::class, 'update']);
    Route::delete('/level_collection/{level_collection_id}', [LevelCollectionController::class, 'delete']);
    Route::get('/level_collection', [LevelCollectionController::class, 'getLevelCollection']);
    Route::get('/level_collections', [LevelCollectionController::class, 'getLevelCollections']);

    Route::post('/company/create', [CompanyController::class, 'create']);
    Route::post('/company/{company_id}/update', [CompanyController::class, 'update']);
    Route::delete('/company/{company_id}', [CompanyController::class, 'delete']);
    Route::post('/company/{company_id}/add_members', [CompanyController::class, 'addMembers']);
    Route::post('/company/{company_id}/remove_members', [CompanyController::class, 'removeMembers']);
    Route::post('/company/{company_id}/leave', [CompanyController::class, 'leave']);
    

    Route::post('/request/create', [RequestController::class, 'create']);
    Route::post('/request/{request_id}/update', [RequestController::class, 'update']);

    Route::group([
        'prefix' => 'admin',
        'middleware' => "isadmin"
    ], function() {

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/generalinfo', [AdminController::class, 'getGeneralInfo']);
        Route::get('/verify', [AdminController::class, 'verify']);
    });

    Route::get('profile/{type}/{id}', [ProfileController::class, 'getUserProfile'])
        ->whereIn('type', ['user', 'company']);

    Route::post('/project', [ProjectController::class, 'create']);
    Route::post('/project/{project_id}', [ProjectController::class, 'update']);
    Route::delete('/project/{project_id}', [ProjectController::class, 'delete']);
    Route::post('/project/{project_id}/invite_participants', [ProjectController::class, 'sendParticipationInvitation']);
    Route::post('/project/{project_id}/remove_participants', [ProjectController::class, 'removeParticipants']);
    Route::post('/project/{project_id}/become', [ProjectController::class, 'becomeParticipant']);
    Route::post('/project/{project_id}/add_skills', [ProjectController::class, 'addSkills']);
    Route::post('/project/{project_id}/remove_skills', [ProjectController::class, 'removeSkills']);
    Route::post('/project/{project_id}/leave', [ProjectController::class, 'leave']);
    

    Route::get('/authorizations', [AuthorizationController::class, 'getAuthorizations']);
    Route::post('/authorizations', [AuthorizationController::class, 'createAuthorization']);
    Route::delete('/authorizations/{authorization_id}', [AuthorizationController::class, 'deleteAuthorization']);

    Route::get('/permissions', [PermissionController::class, 'getPermissions']);
    Route::post('/permissions/{permission_id}', [PermissionController::class, 'updatePermission']);
    Route::post('/permissions', [PermissionController::class, 'createPermission']);
    Route::delete('/permissions/{permission_id}', [PermissionController::class, 'deletePermission']);

    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::post('/roles/{role_id}/sync', [RoleController::class, 'syncPermissionsAndRole']);
    Route::post('/roles/{role_id}', [RoleController::class, 'updateRole']);
    Route::post('/roles', [RoleController::class, 'createRole']);
    Route::delete('/roles/{role_id}', [RoleController::class, 'deleteRole']);

});

Route::any("{any}", function (Request $request) {
    return response()->json([
        "status" => false,
        "message" => "this is an unknown request."
    ], 404);
})->where("any", ".*");


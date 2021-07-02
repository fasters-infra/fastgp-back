<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With,Authorization, Content-Type, Accept');

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OccupationController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanyGoalController;
use App\Http\Controllers\Api\LegendController;
use App\Http\Controllers\Api\OccupationLevelsController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RatingLegendController;
use App\Http\Controllers\Api\RatingResponseController;
use App\Http\Controllers\Api\RatingSessionController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SubSessionController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\SmtpController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\UserOccupationController;
use App\Http\Controllers\Api\SocialMediaController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\VacationScheduleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\FieldsRequiredController;
use App\Http\Controllers\Api\EletronicPointMarkingController;
use App\Http\Controllers\Api\EletronicPointProfileController;
use App\Http\Controllers\Api\FieldValidatorController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Api\StackController;
use App\Http\Controllers\Api\UploadFilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\TextUI\XmlConfiguration\Group;


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

Route::post("auth/login", [AuthController::class, 'login']);
Route::post("auth/recovery", [AuthController::class, 'recovery']);
Route::post("auth/recovery/test", [AuthController::class, 'testRecoveryCode']);
Route::put("auth/recovery", [AuthController::class, 'updatePassword']);

/**
 * Rotas úteis
 *
 */
Route::get("cities/", [CityController::class, 'cities']);
Route::get("cities/states", [CityController::class, 'states']);

// Rotas protegidas
Route::group(["middleware" => ["apiJwt", "logUpdate"]], function () {

    Route::group(['middleware' => ['role:administrador']], function () {

        // Roles
        Route::get('/roles', [RolesController::class, 'index']);
        Route::get('/roles/{id}', [RolesController::class, 'show']);
        Route::post('/roles', [RolesController::class, 'store']);
        Route::post('/roles/{permission}', [RolesController::class, 'update']);
        Route::delete('/roles/{role}', [RolesController::class, 'destroy']);

        // Permissions
        Route::get('/permissions', [PermissionsController::class, 'index']);
        Route::post('/permissions', [PermissionsController::class, 'store']);
        Route::post('/permissions/role', [PermissionsController::class, 'create']);
        Route::get('/permissions/user', [PermissionsController::class, 'show']);
    });

    Route::get("auth/logout", [AuthController::class, 'logout']);
    Route::get("auth/me", [AuthController::class, 'me']);
    /**
     * Rotas para o CRUD de Usuário
     */
    Route::get("users", [UserController::class, 'index'])->middleware('permission:index_user');
    Route::post("user", [UserController::class, 'store'])->middleware('permission:store_user');
    Route::get("user/{id}", [UserController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_user');
    Route::post("user/{id}", [UserController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_user');
    Route::get("users/birthdays/{month}", [UserController::class, 'birthdays'])->where('month', '[0-9]+')->middleware('permission:birthdays_user');
    Route::put("user/update-status/{id}", [UserController::class, 'updateStatus'])->where('id', '[0-9]+')->middleware('permission:updateStatus_user');
    Route::delete("user/{id}", [UserController::class, 'destroy'])->middleware('permission:destroy_user');

    Route::get("companies", [CompanyController::class, 'index'])->middleware('permission:index_companies');
    Route::post("company", [CompanyController::class, 'store'])->middleware('permission:store_companies');
    Route::get("company/{id}", [CompanyController::class, 'show'])->middleware('permission:show_companies');
    Route::put("company/{id}", [CompanyController::class, 'update'])->middleware('permission:update_companies');
    Route::delete("company/{id}", [CompanyController::class, 'destroy'])->middleware('permission:destroy_companies');

    // Ticket Routes
    Route::get('/ticket', [TicketController::class, 'index'])->middleware('permission:view_all_ticket');
    Route::post('/ticket', [TicketController::class, 'store'])->middleware('permission:store_ticket');
    Route::get('/ticket/user', [TicketController::class, 'view'])->middleware('permission:view_ticket');
    Route::get('/ticket/{ticket}', [TicketController::class, 'show'])->where('ticket', '[0-9]+')->middleware('permission:show_ticket');
    Route::get('/ticket/{ticket}', [TicketController::class, 'update'])->where('ticket', '[0-9]+')->middleware('permission:update_ticket');
    Route::delete('/ticket/{ticket}', [TicketController::class, 'destroy'])->where('ticket', '[0-9]+')->middleware('permission:delete_ticket');

    /**
     * Rotas para o CRUD de SMTP
     */
    Route::get("smtps", [UserController::class, 'index'])->middleware('permission:index_smtp');
    Route::post("smtp", [UserController::class, 'store'])->middleware('permission:store_smtp');
    Route::get("smtp/{id}", [UserController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_smtp');
    Route::put("smtp/{id}", [UserController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_smtp');
    Route::delete("smtp/{id}", [UserController::class, 'destroy'])->middleware('permission:destroy_smtp');

    /**
     * Rotas para o CRUD de Cargos
     */
    Route::get("occupations", [OccupationController::class, 'index'])->middleware('permission:index_occupations');
    Route::post("occupation", [OccupationController::class, 'store'])->middleware('permission:store_occupations');
    Route::get("occupation/{id}", [OccupationController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_occupations');
    Route::put("occupation/{id}", [OccupationController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_occupations');
    Route::delete("occupation/{id}", [OccupationController::class, 'destroy'])->middleware('permission:destroy_occupations');

    /**
     * Rotas para o CRUD de Níveis
     */
    Route::get("levels", [LevelController::class, 'index'])->middleware('permission:index_level');
    Route::post("level", [LevelController::class, 'store'])->middleware('permission:store_level');
    Route::get("level/{id}", [LevelController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_level');
    Route::put("level/{id}", [LevelController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_level');
    Route::delete("level/{id}", [LevelController::class, 'destroy'])->middleware('permission:destroy_level');

    /**
     * Rotas para o CRUD de CargosNíveis
     */
    Route::get("occupation-levels", [OccupationLevelsController::class, 'index'])->middleware('permission:index_occupation_levels');
    Route::post("occupation-levels", [OccupationLevelsController::class, 'store'])->middleware('permission:store_occupation_levels');
    Route::get("occupation-levels/{id}", [OccupationLevelsController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_occupation_levels');
    Route::put("occupation-levels/{id}", [OccupationLevelsController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_occupation_levels');
    Route::delete("occupation-levels/{id}", [OccupationLevelsController::class, 'destroy'])->middleware('permission:destroy_occupation_levels');

    /**
     * Rotas para o CRUD de Cargos do Usuário
     */
    Route::get("user-occupation", [UserOccupationController::class, 'index'])->middleware('permission:index_user_occupation');
    Route::post("user-occupation", [UserOccupationController::class, 'store'])->middleware('permission:store_user_occupation');
    Route::get("user-occupation/{id}", [UserOccupationController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_user_occupation');
    Route::put("user-occupation/{id}", [UserOccupationController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_user_occupation');
    Route::delete("user-occupation/{id}", [UserOccupationController::class, 'destroy'])->middleware('permission:destroy_user_occupation');

    /**
     * Rotas para o CRUD de Validação de Fields
     */

    Route::get("field-required", [FieldsRequiredController::class, 'index'])->middleware('permission:index_field_required');
    Route::post("field-required", [FieldsRequiredController::class, 'store'])->middleware('permission:store_field_required');
    Route::put("field-required/{id}", [FieldsRequiredController::class, 'update'])->middleware('permission:update_field_required');
    Route::delete("field-required/{id}", [FieldsRequiredController::class, 'destroy'])->middleware('permission:destroy_field_required');

    /**
     * Rotas para o CRUD de Avaliações
     */
    Route::apiResource("ratings", RatingController::class);
    Route::get("ratings-by-appraiser", [RatingController::class, 'ratingByAppriser']);
    /**
     * Rotas para o CRUD de Legendas
     */
    Route::apiResource("legends", LegendController::class);

    /**
     * Rotas para o CRUD para associação das legendas com as avaliações
     */
    Route::apiResource("rating-legends", RatingLegendController::class);

    /**
     * Rotas para o CRUD Sessões
     */
    Route::apiResource("sessions", SessionController::class);

    /**
     * Rotas para o CRUD para associação das sessões com as avaliações
     */
    Route::apiResource("rating-sessions", RatingSessionController::class);

    /**
     * Rotas para o CRUD sub-sessões
     */
    Route::apiResource("sub-sessions", SubSessionController::class);

    /**
     * Rotas para o CRUD para associação das respostas com as avaliações
     */
    Route::apiResource("rating-responses", RatingResponseController::class);

    /**
     * Rotas para o CRUD de Redes Sociais
     */
    Route::get("social-medias", [SocialMediaController::class, 'index'])->middleware('permission:index_social_medias');
    Route::post("social-medias", [SocialMediaController::class, 'store'])->middleware('permission:store_social_medias');
    Route::get("social-medias/{id}", [SocialMediaController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_social_medias');
    Route::put("social-medias/{id}", [SocialMediaController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_social_medias');
    Route::delete("social-medias/{id}", [SocialMediaController::class, 'destroy'])->middleware('permission:destroy_social_medias');
    Route::post("user-social-medias/", [SocialMediaController::class, 'setUserSocialMedia'])->middleware('permission:setUserSocialMedia');
    Route::delete("user-social-medias/{id}", [SocialMediaController::class, 'deleteUserSocialMedia'])->where('id', '[0-9]+')->middleware('permission:deleteUserSocialMedia');
    //Posts
    Route::get("posts", [PostController::class, 'index'])->middleware('permission:index_posts');
    Route::post("posts", [PostController::class, 'store'])->middleware('permission:store_posts');
    Route::get("posts/{id}", [PostController::class, 'show'])->middleware('permission:show_posts');
    Route::post("posts/{id}", [PostController::class, 'update'])->middleware('permission:update_posts');
    Route::delete("posts/{id}", [PostController::class, 'destroy'])->middleware('permission:destroy_posts');

    /**
     * Rotas para o CRUD de Solicitação de Horas Extras
     */
    Route::get("overtime-requests/my-team", [OvertimeController::class, 'indexMyTeam'])->middleware('permission:indexMyTeam');
    Route::post("overtime-request/{status}/{id}", [OvertimeController::class, 'changeStatus'])->middleware('permission:myTeamChangeStatus');
    Route::apiResource("overtime-requests", OvertimeController::class);

    /**
     * Rotas para o CRUD de time
     */
    Route::apiResource("teams", TeamController::class);

    /**
     * Rotas para o CRUD para associação de um membro para um time
     */
    Route::apiResource("team-members", TeamMemberController::class);

    /**
     * Rotas para o CRUD de agendamento de férias
     */
    Route::apiResource("vacation-schedules", VacationScheduleController::class);

    /**
     * Rotas para o CRUD para de notificações
     */
    Route::get("notifications", [NotificationController::class, 'index'])->middleware('permission:index_notifications');
    Route::post("notifications", [NotificationController::class, 'store'])->middleware('permission:store_notifications');
    Route::get("notifications/{id}", [NotificationController::class, 'show'])->where('id', '[0-9]+')->middleware('permission:show_notifications');
    Route::post("notifications/{id}", [NotificationController::class, 'update'])->where('id', '[0-9]+')->middleware('permission:update_notifications');
    Route::delete("notifications/{id}", [NotificationController::class, 'destroy'])->where('id', '[0-9]+')->middleware('permission:destroy_notifications');
    Route::put("change-vacation-schedule-status/{id}", [VacationScheduleController::class, 'changeStatus'])->middleware('permission:changeStatus');

    /**
     * Rotas para o CRUD de meta de empresa
     */
    Route::apiResource("company-goals", CompanyGoalController::class);

    /**
     * Rotas para o CRUD de stacks
     */
    Route::apiResource("stacks", StackController::class);
    Route::get("stack-users", [StackController::class, 'showStacks']);

    /**
     * Rota para update de endereço
     */

    Route::put("addresses/{id}", [AddressController::class, 'update']);
    Route::post("addresses", [AddressController::class, 'store']);
    Route::post('field-validator', [FieldValidatorController::class, 'isValidFields']);

    /**
     * Rotas para o CRUD de upĺoad documentos
     */
    Route::get('list-files', [UploadFilesController::class, 'index'])->middleware('permission:index_documents');
    Route::get('list-files/{user}', [UploadFilesController::class, 'show'])->middleware('permission:show_documents');
    Route::post('upload-files/upload', [UploadFilesController::class, 'upload'])->middleware('permission:upload_documents');
    Route::get('download-file/{id}', [UploadFilesController::class, 'download'])->middleware('permission:upload_documents');
    Route::delete('upload-files/{id}', [UploadFilesController::class, 'destroy'])->middleware('permission:download_documents');

    /**
     * Rotas para CRUD de perfil de ponto eletronico
     */

    Route::apiResource('eletronic-point-profiles', EletronicPointProfileController::class);

    /**
     * Rotas para marcação de ponto
     */
    Route::post('eletronic-point-markings', [EletronicPointMarkingController::class, 'mark']);
    Route::post('eletronic-point-markings/justify/{id}', [EletronicPointMarkingController::class, 'justify']);
    Route::get('eletronic-point-markings/period', [EletronicPointMarkingController::class, 'period']);
});

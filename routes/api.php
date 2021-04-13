<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\LoginController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GroupController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\OriginController;
use App\Http\Controllers\api\AgencyController;
use App\Http\Controllers\api\PublisherController;
use App\Http\Controllers\api\BokalController;
use App\Http\Controllers\api\CommitteeController;
use App\Http\Controllers\api\ForReferralController;
use App\Http\Controllers\api\CommitteeReportController;
use App\Http\Controllers\api\SecondReadingController;


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

Route::prefix('auth')->group(function() {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout']);
});

/**
 * Users
 */
Route::apiResources([
    'users' => UserController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'user' => UserController::class,
],[
    'except' => ['index']
]);

/**
 * Groups
 */
Route::apiResources([
    'groups' => GroupController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'group' => GroupController::class,
],[
    'except' => ['index']
]);

/**
 * Origins
 */
Route::apiResources([
    'origins' => OriginController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'origin' => OriginController::class,
],[
    'except' => ['index']
]);

/**
 * Agencies
 */
Route::apiResources([
    'agencies' => AgencyController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'agency' => AgencyController::class,
],[
    'except' => ['index']
]);

/**
 * Publishers
 */
Route::apiResources([
    'publishers' => PublisherController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'publisher' => PublisherController::class,
],[
    'except' => ['index']
]);

/**
 * Bokals
 */
Route::apiResources([
    'bokals' => BokalController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'bokal' => BokalController::class,
],[
    'except' => ['index']
]);

/**
 * Committees
 */
Route::apiResources([
    'committees' => CommitteeController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'committee' => CommitteeController::class,
],[
    'except' => ['index']
]);

/**
 * Categories
 */
Route::apiResources([
    'categories' => CategoryController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'category' => CategoryController::class,
],[
    'except' => ['index']
]);

/**
 * For Referral
 */
Route::apiResources([
    'for_referrals' => ForReferralController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'for_referral' => ForReferralController::class,
],[
    'except' => ['index']
]);


/**
 * Committeee Reports
 */
Route::apiResources([
    'committee_reports' => CommitteeReportController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'committee_report' => CommitteeReportController::class,
],[
    'except' => ['index']
]);

/**
 * Second Reading
 */
Route::apiResources([
    'second_readings' => SecondReadingController::class,
],[
    'only' => ['index']
]);

Route::apiResources([
    'second_reading' => SecondReadingController::class,
],[
    'except' => ['index']
]);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/test/{id}', function () {
        return "Hello, World!";
    });     
});
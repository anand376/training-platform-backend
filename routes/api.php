<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TrainingOptController;
use App\Http\Controllers\Api\TrainingScheduleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Protected routes example
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('students', StudentController::class);
    Route::get('students/user/{userId}', [StudentController::class, 'getByUserId']);
    Route::post('students/user/{userId}', [StudentController::class, 'createForUser']);
    Route::apiResource('training-schedules', TrainingScheduleController::class);
    Route::post('training-opt-in-out', [TrainingOptController::class, 'optInOut']);
    Route::get('/student-training-statuses', [TrainingOptController::class, 'statusList']);

});

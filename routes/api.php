<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaveController;
use App\Http\Controllers\Api\SegmentController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TrainingPlanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// App Version Check
Route::get('/app-version', function () {
    return response()->json([
        'version' => '1.0.0', // Versão atual no servidor
        'build' => 1,
        'url' => 'https://mere-app.com.br/download', // Onde baixar a nova versão
        'message' => 'Uma nova versão do MERE está disponível!',
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/password', [PasswordResetController::class, 'update']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('profile');
        $data = $user->toArray();
        // Append additional fields expected by mobile app
        $data['image_url'] = $user->image_url;
        $data['cover_url'] = $user->cover_url;
        $data['surname'] = $user->profile->last_name ?? '';
        $data['bio'] = $user->profile->bio ?? '';
        $data['city'] = $user->profile->city ?? '';
        $data['phone'] = $user->profile->phone ?? '';
        $data['gender'] = $user->profile->gender ?? '';
        $data['birthDate'] = $user->profile->birth_date ?? '';
        $data['height'] = $user->profile->height ?? '';
        $data['weight'] = $user->profile->weight ?? '';
        $data['settings'] = $user->profile->settings ?? [];

        return $data;
    });

    // Mobile App Subscription Routes
    Route::get('/plans', [SubscriptionController::class, 'index']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);

    // Stories API
    Route::get('/stories', [StoryController::class, 'index']);
    Route::post('/stories', [StoryController::class, 'store']);

    // Polls API
    Route::post('/polls', [PollController::class, 'store']);
    Route::put('/polls/{id}', [PollController::class, 'update']);
    Route::delete('/polls/{id}', [PollController::class, 'destroy']);
    Route::post('/polls/{id}/vote', [PollController::class, 'vote']);
    Route::post('/polls/{id}/like', [LikeController::class, 'toggleItemLike']);
    Route::post('/polls/{id}/save', [SaveController::class, 'toggleSave']);
    Route::get('/polls/{id}/comments', [CommentController::class, 'index']);

    // Posts API
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [LikeController::class, 'toggleItemLike']);
    Route::post('/posts/{id}/save', [SaveController::class, 'toggleSave']);
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{id}/comment', [CommentController::class, 'store']); // Para compatibilidade
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']); // Para compatibilidade
    Route::post('/polls/{id}/comments', [CommentController::class, 'store']); // Para compatibilidade
    Route::post('/post-comments/{id}/like', [LikeController::class, 'toggleCommentLike']); // Para compatibilidade
    Route::delete('/post-comments/{id}', [CommentController::class, 'destroy']); // Para compatibilidade

    // Activities API
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::post('/activities/sync', [ActivityController::class, 'sync']);
    Route::post('/activities/upload', [ActivityController::class, 'upload']);
    Route::get('/activities/history', [ActivityController::class, 'history']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);
    Route::put('/activities/{id}', [ActivityController::class, 'update']);
    Route::delete('/activities/{id}', [ActivityController::class, 'destroy']);
    Route::post('/activities/{id}/like', [LikeController::class, 'toggleItemLike']);
    Route::post('/activities/{id}/save', [SaveController::class, 'toggleSave']);
    Route::post('/activities/{id}/vote', [ActivityController::class, 'vote']);
    Route::get('/activities/{id}/comments', [CommentController::class, 'index']);
    Route::post('/activities/{id}/comment', [CommentController::class, 'store']);
    Route::post('/comments/{id}/like', [LikeController::class, 'toggleCommentLike']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // User/Social API
    Route::get('/users/suggested', [UserController::class, 'suggested']);
    Route::post('/users/{id}/follow', [UserController::class, 'toggleFollow']);
    Route::post('/users/{id}/block', [UserController::class, 'toggleBlock']);
    Route::post('/report', [ReportController::class, 'store']);
    Route::get('/users/following', [UserController::class, 'following']);
    Route::get('/users/{id}', [UserController::class, 'profile']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);

    // Chat/Messages API
    Route::get('/messages/{userId}', [MessageController::class, 'getMessages']);
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::post('/messages/{userId}/read', [MessageController::class, 'markAsRead']);
    Route::get('/conversations', [MessageController::class, 'getConversations']);
    Route::delete('/conversations/all', [MessageController::class, 'destroyAll']);
    Route::post('/conversations/archive/all', [MessageController::class, 'archiveAll']);
    Route::delete('/conversations/{userId}', [MessageController::class, 'destroy']);
    Route::post('/conversations/{userId}/archive', [MessageController::class, 'archive']);
    Route::delete('/conversations/{userId}/everyone', [MessageController::class, 'clearForEveryone']);

    // Stats & Dashboard API
    Route::get('/stats/dashboard', [StatsController::class, 'dashboard']);
    Route::get('/stats/challenges/active', [StatsController::class, 'activeChallenges']);
    Route::get('/stats/challenges/available', [StatsController::class, 'availableChallenges']);
    Route::get('/stats/tier', [StatsController::class, 'userTier']);
    Route::post('/challenges/{challenge}/join', [StatsController::class, 'joinChallenge']);
    Route::post('/challenges/{challenge}/leave', [StatsController::class, 'leaveChallenge']);

    // Training Plans API
    Route::get('/training-plans', [TrainingPlanController::class, 'index']);
    Route::get('/training-plans/today', [TrainingPlanController::class, 'todayWorkout']);
    Route::post('/training-plans/complete-today', [TrainingPlanController::class, 'completeToday']);
    Route::get('/training-plans/{trainingPlan}', [TrainingPlanController::class, 'show']);
    Route::post('/training-plans/{trainingPlan}/enroll', [TrainingPlanController::class, 'enroll']);
    Route::post('/training-plans/{trainingPlan}/unenroll', [TrainingPlanController::class, 'unenroll']);

    // Segments API
    Route::get('/segments', [SegmentController::class, 'index']);
    Route::post('/segments', [SegmentController::class, 'store']);
    Route::get('/segments/{segment}', [SegmentController::class, 'show']);

    // Clubs API
    Route::get('/clubs', [ClubController::class, 'index']);
    Route::get('/clubs/my', [ClubController::class, 'myClubs']);
    Route::get('/clubs/categories', [ClubController::class, 'categories']);
    Route::get('/clubs/{id}', [ClubController::class, 'show']);
    Route::post('/clubs', [ClubController::class, 'store']);
    Route::put('/clubs/{id}', [ClubController::class, 'update']);
    Route::delete('/clubs/{id}', [ClubController::class, 'destroy']);
    Route::post('/clubs/{id}/join', [ClubController::class, 'join']);
    Route::post('/clubs/{id}/leave', [ClubController::class, 'leave']);
    Route::get('/clubs/{id}/members', [ClubController::class, 'members']);
});

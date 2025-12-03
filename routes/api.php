<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TechnologyController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\VisitorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Projects API
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    
    // Technologies API
    Route::get('/technologies', [TechnologyController::class, 'index']);
    Route::get('/technologies/{id}', [TechnologyController::class, 'show']);
    
    // Certificates API
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}', [CertificateController::class, 'show']);
    
    // Categories API
    Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/categories/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'show']);
    
    // Messages API (Public - untuk submit pesan)
    Route::post('/messages', [MessageController::class, 'store']);
    
    // Settings API (Public - untuk get settings frontend)
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    
    // Experiences API
    Route::get('/experiences', [ExperienceController::class, 'index']);
    Route::get('/experiences/{id}', [ExperienceController::class, 'show']);
    
    // Testimonials API
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::get('/testimonials/{id}', [TestimonialController::class, 'show']);
    Route::post('/testimonials', [TestimonialController::class, 'store']); // Public POST for form submission
    
    // Articles API
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']); // Can use slug or id
    
    // Visitors API (Public - untuk tracking)
    Route::post('/visitors/track', [VisitorController::class, 'track']);
});

// Admin routes (protected by auth middleware and require 2FA)
Route::middleware(['auth', 'require.2fa'])->prefix('v1/admin')->group(function () {
    // Projects Admin API
    Route::apiResource('projects', ProjectController::class);
    
    // Technologies Admin API
    Route::apiResource('technologies', TechnologyController::class);
    
    // Certificates Admin API
    Route::apiResource('certificates', CertificateController::class);
    
    // Categories Admin API
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class);
    
    // Messages Admin API
    Route::apiResource('messages', MessageController::class);
    
    // Settings Admin API
    Route::put('settings', [SettingController::class, 'update']);
    
    // Experiences Admin API
    Route::apiResource('experiences', ExperienceController::class);
    
    // Testimonials Admin API
    Route::apiResource('testimonials', TestimonialController::class);
    
    // Articles Admin API
    Route::apiResource('articles', ArticleController::class);
    
    // Visitors Admin API
    Route::get('/visitors/stats', [VisitorController::class, 'stats']);
});


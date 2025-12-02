<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Serve storage files with CORS headers (for development server)
// This route ensures CORS headers are added even when PHP built-in server serves files directly
Route::match(['GET', 'OPTIONS'], '/storage/{path}', function (string $path) {
    // Handle preflight OPTIONS request
    if (request()->getMethod() === 'OPTIONS') {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Access-Control-Max-Age', '86400');
    }
    
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');

Route::middleware(['auth', 'verified', 'password.confirm'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');
    Volt::route('profile', 'settings.profile')->name('profile.edit');
    Volt::route('two-factor', 'settings.two-factor')->name('two-factor.show');

    // Admin Dashboard Routes (Require 2FA)
    Route::prefix('admin')->name('admin.')->middleware('require.2fa')->group(function () {
        Volt::route('projects', 'admin.projects')->name('projects.index');
        Volt::route('technologies', 'admin.technologies')->name('technologies.index');
        Volt::route('certificates', 'admin.certificates')->name('certificates.index');
        Volt::route('categories', 'admin.categories')->name('categories.index');
        Volt::route('messages', 'admin.messages')->name('messages.index');
        Volt::route('settings', 'admin.settings')->name('settings.index');
        Volt::route('experiences', 'admin.experiences')->name('experiences.index');
        Volt::route('testimonials', 'admin.testimonials')->name('testimonials.index');
        Volt::route('articles', 'admin.articles')->name('articles.index');
    });
});

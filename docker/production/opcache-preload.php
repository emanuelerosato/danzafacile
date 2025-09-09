<?php

/**
 * OPcache Preloader for Laravel Scuola Danza
 * 
 * This file preloads frequently used classes to improve performance
 * in production environment.
 */

if (!function_exists('opcache_compile_file') || !ini_get('opcache.enable')) {
    return;
}

$appPath = dirname(__DIR__);

// Laravel Core Classes
$coreClasses = [
    // Framework Core
    $appPath . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Container/Container.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php',
    
    // HTTP Layer
    $appPath . '/vendor/laravel/framework/src/Illuminate/Http/Request.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Http/Response.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Routing/Router.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Routing/Route.php',
    
    // Database
    $appPath . '/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php',
    
    // Views
    $appPath . '/vendor/laravel/framework/src/Illuminate/View/View.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/View/Factory.php',
    
    // Cache
    $appPath . '/vendor/laravel/framework/src/Illuminate/Cache/CacheManager.php',
    $appPath . '/vendor/laravel/framework/src/Illuminate/Redis/RedisManager.php',
];

// Application Classes
$appClasses = [
    // Models
    $appPath . '/app/Models/User.php',
    $appPath . '/app/Models/School.php',
    $appPath . '/app/Models/Course.php',
    $appPath . '/app/Models/CourseEnrollment.php',
    $appPath . '/app/Models/Payment.php',
    $appPath . '/app/Models/Document.php',
    $appPath . '/app/Models/MediaGallery.php',
    $appPath . '/app/Models/MediaItem.php',
    
    // Controllers
    $appPath . '/app/Http/Controllers/Controller.php',
    $appPath . '/app/Http/Controllers/SuperAdmin/SuperAdminController.php',
    $appPath . '/app/Http/Controllers/Admin/AdminDashboardController.php',
    $appPath . '/app/Http/Controllers/Student/StudentDashboardController.php',
    
    // Middleware
    $appPath . '/app/Http/Middleware/RoleMiddleware.php',
    $appPath . '/app/Http/Middleware/SchoolOwnershipMiddleware.php',
    
    // Services
    $appPath . '/app/Services/CacheService.php',
    $appPath . '/app/Services/DatabaseOptimizationService.php',
    $appPath . '/app/Services/FileUploadService.php',
    $appPath . '/app/Services/NotificationService.php',
];

$preloadFiles = array_merge($coreClasses, $appClasses);

$loadedCount = 0;
$errorCount = 0;

foreach ($preloadFiles as $file) {
    if (file_exists($file)) {
        try {
            opcache_compile_file($file);
            $loadedCount++;
        } catch (Throwable $e) {
            $errorCount++;
            error_log("OPcache preload error for $file: " . $e->getMessage());
        }
    }
}

// Log preload statistics
error_log("OPcache preload completed: {$loadedCount} files loaded, {$errorCount} errors");

// Preload config cache if it exists
$configCache = $appPath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    try {
        opcache_compile_file($configCache);
        error_log("OPcache: Config cache preloaded");
    } catch (Throwable $e) {
        error_log("OPcache preload error for config cache: " . $e->getMessage());
    }
}

// Preload route cache if it exists
$routeCache = $appPath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache)) {
    try {
        opcache_compile_file($routeCache);
        error_log("OPcache: Route cache preloaded");
    } catch (Throwable $e) {
        error_log("OPcache preload error for route cache: " . $e->getMessage());
    }
}
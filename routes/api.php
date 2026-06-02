<?php

use App\Http\Controllers\Api\Admin\AdminTokenController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\BuyerController;
use App\Http\Controllers\Api\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Consumer\AuthController as ConsumerAuthController;
use App\Http\Controllers\Api\Consumer\BannerController as ConsumerBannerController;
use App\Http\Controllers\Api\Consumer\BusinessCategoryController as ConsumerBusinessCategoryController;
use App\Http\Controllers\Api\Consumer\CatalogImageController as ConsumerCatalogImageController;
use App\Http\Controllers\Api\Consumer\FavoriteController as ConsumerFavoriteController;
use App\Http\Controllers\Api\Consumer\FilterController as ConsumerFilterController;
use App\Http\Controllers\Api\Consumer\InteractionController as ConsumerInteractionController;
use App\Http\Controllers\Api\Consumer\SellerController as ConsumerSellerController;
use App\Http\Controllers\Api\Consumer\SellerDocumentController as ConsumerSellerDocumentController;
use App\Http\Controllers\Api\Seller\AuthController as SellerAuthController;
use App\Http\Controllers\Api\Seller\BusinessCategoryController;
use App\Http\Controllers\Api\Seller\CatalogImageController;
use App\Http\Controllers\Api\Seller\MetricsController;
use App\Http\Controllers\Api\Seller\ProfileController as SellerProfileController;
use App\Http\Controllers\Api\Seller\SettingsController;
use App\Http\Controllers\Api\Seller\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->middleware(['admin.token'])->group(function (): void {
    Route::get('stats', [StatsController::class, 'index'])->name('api.admin.stats');

    Route::get('buyers', [BuyerController::class, 'index'])->name('api.admin.buyers.index');
    Route::delete('buyers/{user}', [BuyerController::class, 'destroy'])->name('api.admin.buyers.destroy');

    Route::get('sellers', [AdminSellerController::class, 'index'])->name('api.admin.sellers.index');
    Route::patch('sellers/{user}/profile', [AdminSellerController::class, 'updateProfile'])->name('api.admin.sellers.profile.update');
    Route::delete('sellers/{user}', [AdminSellerController::class, 'destroy'])->name('api.admin.sellers.destroy');

    Route::get('banners', [BannerController::class, 'index'])->name('api.admin.banners.index');
    Route::post('banners', [BannerController::class, 'store'])->name('api.admin.banners.store');
    Route::post('banners/{banner}/move-up', [BannerController::class, 'moveUp'])->name('api.admin.banners.move-up');
    Route::post('banners/{banner}/move-down', [BannerController::class, 'moveDown'])->name('api.admin.banners.move-down');
    Route::patch('banners/{banner}', [BannerController::class, 'update'])->name('api.admin.banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('api.admin.banners.destroy');

    Route::get('tokens', [AdminTokenController::class, 'index'])->name('api.admin.tokens.index');
    Route::post('tokens', [AdminTokenController::class, 'store'])->name('api.admin.tokens.store');
    Route::delete('tokens/{adminToken}', [AdminTokenController::class, 'destroy'])->name('api.admin.tokens.destroy');
});

/*
|--------------------------------------------------------------------------
| App 2 — Mayoristas (React Native)
|--------------------------------------------------------------------------
| Autenticación: Bearer token (Laravel Sanctum). No usar token de panel admin.
*/
Route::prefix('v1/seller')->group(function (): void {
    Route::post('register', [SellerAuthController::class, 'register'])->name('api.seller.register');
    Route::post('login', [SellerAuthController::class, 'login'])->name('api.seller.login');
    Route::post('forgot-password', [SellerAuthController::class, 'forgotPassword'])->name('api.seller.forgot-password');

    Route::get('business-categories', [BusinessCategoryController::class, 'index'])->name('api.seller.business-categories.index');

    Route::middleware(['auth:sanctum', 'seller.api'])->group(function (): void {
        Route::post('logout', [SellerAuthController::class, 'logout'])->name('api.seller.logout');
        Route::get('me', [SellerAuthController::class, 'me'])->name('api.seller.me');
        Route::get('subscription', [SubscriptionController::class, 'show'])->name('api.seller.subscription.show');

        Route::middleware(['seller.active'])->group(function (): void {
            Route::get('profile', [SellerProfileController::class, 'show'])->name('api.seller.profile.show');
            Route::match(['put', 'patch'], 'profile', [SellerProfileController::class, 'update'])->name('api.seller.profile.update');
            Route::delete('profile/pdf', [SellerProfileController::class, 'destroyPdf'])->name('api.seller.profile.pdf.destroy');
            Route::delete('profile/excel', [SellerProfileController::class, 'destroyExcel'])->name('api.seller.profile.excel.destroy');

            Route::get('catalog-images', [CatalogImageController::class, 'index'])->name('api.seller.catalog-images.index');
            Route::post('catalog-images', [CatalogImageController::class, 'store'])->name('api.seller.catalog-images.store');
            Route::get('catalog-images/{catalogImage}/file', [CatalogImageController::class, 'file'])->name('api.seller.catalog-images.file');
            Route::delete('catalog-images/{catalogImage}', [CatalogImageController::class, 'destroy'])->name('api.seller.catalog-images.destroy');

            Route::get('metrics', [MetricsController::class, 'index'])->name('api.seller.metrics.index');

            Route::get('settings', [SettingsController::class, 'show'])->name('api.seller.settings.show');
            Route::patch('settings/password', [SettingsController::class, 'updatePassword'])->name('api.seller.settings.password');
        });
    });
});

/*
|--------------------------------------------------------------------------
| App 1 — Compradores (React Native)
|--------------------------------------------------------------------------
| Navegación invitado sin token; favoritos y sesión requieren Sanctum (comprador).
*/
Route::prefix('v1/consumer')->group(function (): void {
    Route::post('auth/social', [ConsumerAuthController::class, 'socialLogin'])->name('api.consumer.auth.social');

    Route::get('business-categories', [ConsumerBusinessCategoryController::class, 'index'])->name('api.consumer.business-categories.index');
    Route::get('banners', [ConsumerBannerController::class, 'index'])->name('api.consumer.banners.index');
    Route::post('banners/{banner}/click', [ConsumerBannerController::class, 'recordClick'])->name('api.consumer.banners.click');

    Route::get('filters/countries', [ConsumerFilterController::class, 'countries'])->name('api.consumer.filters.countries');
    Route::get('filters/states', [ConsumerFilterController::class, 'states'])->name('api.consumer.filters.states');

    Route::middleware(['sanctum.optional'])->group(function (): void {
        Route::get('sellers', [ConsumerSellerController::class, 'index'])->name('api.consumer.sellers.index');
        Route::get('sellers/{seller}', [ConsumerSellerController::class, 'show'])->name('api.consumer.sellers.show');
        Route::get('sellers/{seller}/catalog-images/{catalogImage}/file', [ConsumerCatalogImageController::class, 'file'])
            ->name('api.consumer.sellers.catalog-images.file');
        Route::get('sellers/{seller}/pdf/file', [ConsumerSellerDocumentController::class, 'pdf'])
            ->name('api.consumer.sellers.pdf.file');
        Route::get('sellers/{seller}/excel/file', [ConsumerSellerDocumentController::class, 'excel'])
            ->name('api.consumer.sellers.excel.file');
        Route::post('sellers/{seller}/interactions', [ConsumerInteractionController::class, 'store'])->name('api.consumer.sellers.interactions.store');
    });

    Route::middleware(['auth:sanctum', 'consumer.api'])->group(function (): void {
        Route::post('logout', [ConsumerAuthController::class, 'logout'])->name('api.consumer.logout');
        Route::get('me', [ConsumerAuthController::class, 'me'])->name('api.consumer.me');

        Route::get('favorites', [ConsumerFavoriteController::class, 'index'])->name('api.consumer.favorites.index');
        Route::post('favorites/{seller}', [ConsumerFavoriteController::class, 'store'])->name('api.consumer.favorites.store');
        Route::delete('favorites/{seller}', [ConsumerFavoriteController::class, 'destroy'])->name('api.consumer.favorites.destroy');
    });
});

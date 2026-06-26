<?php

use App\Http\Controllers\IsiPlaza\AccessController;
use App\Http\Controllers\IsiPlaza\BannersPanelController;
use App\Http\Controllers\IsiPlaza\BuyersPanelController;
use App\Http\Controllers\IsiPlaza\DataManagementPanelController;
use App\Http\Controllers\IsiPlaza\TextosNumerosPanelController;
use App\Http\Controllers\IsiPlaza\TextosPacientePanelController;
use App\Http\Controllers\IsiPlaza\TokensPanelController;
use App\Http\Controllers\IsiPlaza\TreatmentsPanelController;
use App\Http\Controllers\IsiPlaza\VendedoresPanelController;
use Illuminate\Support\Facades\Route;

Route::prefix('isi-plaza')->name('isi-plaza.')->group(function (): void {
    Route::middleware(['isi-plaza.guest'])->group(function (): void {
        Route::get('access', [AccessController::class, 'create'])->name('access');
        Route::post('access', [AccessController::class, 'store'])->name('access.store');
    });

    Route::middleware(['isi-plaza.web'])->group(function (): void {
        Route::post('sign-out', [AccessController::class, 'destroy'])->name('sign-out');

        Route::get('/', function () {
            return redirect()->route('isi-plaza.gestion');
        })->name('panel');

        Route::get('gestion', DataManagementPanelController::class)->name('gestion');

        Route::get('buyers', [BuyersPanelController::class, 'index'])->name('buyers.index');
        Route::delete('buyers/{user}', [BuyersPanelController::class, 'destroy'])->name('buyers.destroy');

        Route::get('vendedores', [VendedoresPanelController::class, 'index'])->name('vendedores.index');
        Route::patch('vendedores/{user}/perfil', [VendedoresPanelController::class, 'update'])->name('vendedores.update');
        Route::delete('vendedores/{user}', [VendedoresPanelController::class, 'destroy'])->name('vendedores.destroy');

        Route::get('banners', [BannersPanelController::class, 'index'])->name('banners.index');
        Route::post('banners', [BannersPanelController::class, 'store'])->name('banners.store');
        Route::post('banners/{banner}/move-up', [BannersPanelController::class, 'moveUp'])->name('banners.move-up');
        Route::post('banners/{banner}/move-down', [BannersPanelController::class, 'moveDown'])->name('banners.move-down');
        Route::patch('banners/{banner}', [BannersPanelController::class, 'update'])->name('banners.update');
        Route::delete('banners/{banner}', [BannersPanelController::class, 'destroy'])->name('banners.destroy');

        Route::get('textos-numeros', [TextosNumerosPanelController::class, 'index'])->name('textos-numeros.index');
        Route::patch('textos-numeros', [TextosNumerosPanelController::class, 'update'])->name('textos-numeros.update');

        Route::get('textos-paciente', [TextosPacientePanelController::class, 'index'])->name('textos-paciente.index');
        Route::patch('textos-paciente', [TextosPacientePanelController::class, 'update'])->name('textos-paciente.update');

        Route::get('vendedores/{user}', [VendedoresPanelController::class, 'show'])->name('vendedores.show');

        Route::get('tratamientos', [TreatmentsPanelController::class, 'index'])->name('tratamientos.index');
        Route::post('tratamientos/sections', [TreatmentsPanelController::class, 'storeSection'])->name('tratamientos.sections.store');
        Route::patch('tratamientos/sections/{section}', [TreatmentsPanelController::class, 'updateSection'])->name('tratamientos.sections.update');
        Route::delete('tratamientos/sections/{section}', [TreatmentsPanelController::class, 'destroySection'])->name('tratamientos.sections.destroy');
        Route::post('tratamientos/treatments', [TreatmentsPanelController::class, 'storeTreatment'])->name('tratamientos.treatments.store');
        Route::patch('tratamientos/treatments/{treatment}', [TreatmentsPanelController::class, 'updateTreatment'])->name('tratamientos.treatments.update');
        Route::delete('tratamientos/treatments/{treatment}', [TreatmentsPanelController::class, 'destroyTreatment'])->name('tratamientos.treatments.destroy');

        Route::prefix('ajustes-acceso')->name('ajustes-acceso.')->group(function (): void {
            Route::get('/', [TokensPanelController::class, 'index'])->name('index');
            Route::post('/', [TokensPanelController::class, 'store'])->name('store');
            Route::delete('{adminToken}', [TokensPanelController::class, 'destroy'])->name('destroy');
        });
    });
});

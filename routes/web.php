<?php

// filepath: /home/fabri/Documentos/tienda/routes/web.php

use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    // /dashboard redirige al dashboard correcto según rol
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboards por rol — URLs separadas para evitar bfcache cross-user
    Route::get('/dashboard/admin', [DashboardController::class, 'adminIndex'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('/dashboard/caja', [DashboardController::class, 'cajaIndex'])
        ->middleware('role:caja')
        ->name('dashboard.caja');

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('role:admin')
        ->name('products.index');

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware('role:admin,caja')
        ->name('inventory.index');

    Route::post('/inventory/sales', [InventoryController::class, 'storeSale'])
        ->middleware('role:admin,caja')
        ->name('inventory.sales.store');

    Route::get('/sales', [SaleController::class, 'index'])
        ->middleware('role:admin,caja')
        ->name('sales.index');

    Route::get('/sales/create', [SaleController::class, 'create'])
        ->middleware('role:admin,caja')
        ->name('sales.create');

    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware(['role:admin,caja', 'throttle:sales'])
        ->name('sales.store');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->middleware('role:admin,caja')
        ->name('sales.show');

    Route::get('/cash-registers', [CashRegisterController::class, 'index'])
        ->middleware('role:admin,caja')
        ->name('cash-registers.index');

    Route::post('/cash-registers/open', [CashRegisterController::class, 'open'])
        ->middleware('role:admin,caja')
        ->name('cash-registers.open');

    Route::post('/cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close'])
        ->middleware('role:admin,caja')
        ->name('cash-registers.close');

    Route::get('/reports/sales', [SalesReportController::class, 'index'])
        ->middleware('role:admin,caja')
        ->name('reports.sales');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);

        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::post('/inventory/entries', [InventoryController::class, 'storeEntry'])->name('inventory.entries.store');
        Route::post('/inventory/adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

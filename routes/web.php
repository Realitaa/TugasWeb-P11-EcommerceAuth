<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Product CRUD routes for Admin & Editor (Controller -> Service separation of concerns)
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::apiResource('products', ProductController::class);
});

// Demo Eager Loading vs Lazy Loading (N+1 Problem)
Route::get('/demo/eager-loading', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $lazyProducts = Product::take(10)->get();
    foreach ($lazyProducts as $p) {
        $c = $p->category?->name;
        $t = $p->tags->pluck('name')->all();
    }
    $lazyCount = count(DB::getQueryLog());

    DB::flushQueryLog();
    $eagerProducts = Product::with(['category', 'tags'])->take(10)->get();
    foreach ($eagerProducts as $p) {
        $c = $p->category?->name;
        $t = $p->tags->pluck('name')->all();
    }
    $eagerCount = count(DB::getQueryLog());

    return response()->json([
        'lazy_loading' => [
            'description' => 'Tanpa eager loading (terjadi N+1 query issue)',
            'query_count' => $lazyCount,
        ],
        'eager_loading' => [
            'description' => 'Dengan eager loading with([category, tags])',
            'query_count' => $eagerCount,
        ],
        'efficiency' => "Berhasil mengoptimalkan query dari {$lazyCount} query menjadi {$eagerCount} query.",
    ]);
});

require __DIR__.'/auth.php';

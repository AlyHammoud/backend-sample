<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\ItemsController;
use App\Http\Controllers\Api\V1\ProductsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    //
    // Routes for Users and Authentication
    //
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return response('verified');
    })->middleware(['signed'])->name('verification.verify')->withoutMiddleware('verified');


    Route::put('/update/user/{user}', [AuthController::class, 'update']);
    Route::delete('/delete/{user}', [AuthController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/all-users', [AuthController::class, 'getAllUsers']);
    //
    //End of Routes for Users and Authentication
    //

    //
    // Routes for Categories
    //
    Route::post('/category', [CategoriesController::class, 'store']);
    Route::put('category/{category}', [CategoriesController::class, 'update']);
    Route::delete('category/{category}', [CategoriesController::class, 'destroy']);
    //
    //

    //
    // Items
    //
    Route::post('/item', [ItemsController::class, 'store']);
    Route::put('/item/{item}', [ItemsController::class, 'update']);
    Route::delete('/item/{item}', [ItemsController::class, 'destroy']);
    Route::delete('deleteAllItems', [ItemsController::class, 'deleteAllItems']); //all categories and relations
    //
    // End Items
    //

    //
    // Products
    //
    Route::post('/product', [ProductsController::class, 'store']);
    Route::put('/product/{product}', [ProductsController::class, 'update']);
    Route::delete('/product/{product}', [ProductsController::class, 'destroy']);
    //
    // End Products
    //
});

//
// Auth
//
Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
//
// End Auth
//


//
//Categories
//
//some fields in dashboard must be shown: array of translations, where in clients must not
Route::get('category', [CategoriesController::class, 'index'])->name('category.allCategories'); //for dashboard
Route::get('category/allAvailable', [CategoriesController::class, 'allAvailable']); //for clients show

Route::get('category/{category}', [CategoriesController::class, 'show'])->name('category.singleCategory'); //for dashboard
Route::get('category/client/{category}', [CategoriesController::class, 'showOneForClients']); // for clients
//
//End Categories
//


//
// Items
//

Route::get('items', [ItemsController::class, 'index'])->name('item.allItems');
Route::get('items/allAvailable', [ItemsController::class, 'allAvailable']); //for clients show

Route::get('item/{item}', [ItemsController::class, 'show'])->name('item.singleitem'); //for dashboard
Route::get('item/client/{item}', [ItemsController::class, 'showOneForClients']); // for clients
//
// End Items
//


//
// Products
//

Route::get('products', [ProductsController::class, 'index'])->name('product.allProducts');
Route::get('products/allAvailable', [ProductsController::class, 'allAvailable']); //for clients show

Route::get('product/{product}', [ProductsController::class, 'show'])->name('product.singleproduct'); //for dashboard
Route::get('product/client/{product}', [ProductsController::class, 'showOneForClients']); // for clients
//
// End Products
//

<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\backend\BrandController;
use App\Http\Controllers\backend\CustomerController;
use App\Http\Controllers\backend\ProductCategoryController;
use App\Http\Controllers\backend\ProductController;
use App\Http\Controllers\backend\PurchaseController;
use App\Http\Controllers\backend\PurchaseReturnController;
use App\Http\Controllers\backend\SaleController;
use App\Http\Controllers\backend\SupplierController;
use App\Http\Controllers\backend\WarehouseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', function () {
    return view('admin.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
        Route::get('/admin/logout', [AdminController::class, 'destroy'])
        ->name('admin.logout');
        Route::post('/admin/login', [AdminController::class, 'AdminLogin'])
        ->name('admin.login');

        //2step verification routes//
        Route::get('/verify',[AdminController::class,'showVerification'])
        ->name('custom.verification.Form');
        Route::post('/verify',[AdminController::class,'verificationVerify'])
        ->name('custom.verification.verify');


Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

//profile auth route
Route::middleware('auth')->group(function () {
          Route::get('/profile', [AdminController::class, 'AdminProfile'])
        ->name('admin.admin_profile');

         Route::post('/profile/store', [AdminController::class, 'ProfileStore'])
        ->name('profile.store');

        Route::post('/admin/password/update', [AdminController::class, 'AdminPasswordUpdate'])
        ->name('Admin.password.update');


});

Route::middleware('auth')->group(function () {
         Route::controller(BrandController::class)->group(function(){
            Route::get('/all/brand','allBrand')->name('all.brand');
            Route::get('/add/brand','addBrand')->name('add.brand');
            Route::get('/edit/brand/{id}','editBrand')->name('edit.brand');
            Route::post('/store/brand','storeBrand')->name('store.brand');
           Route::put('/update/brand/{id}','updateBrand')->name('update.brand');
           Route::get('/delete/brand/{id}','deleteBrand')->name('delete.brand');


         });
         //WareHouse Routes//
           Route::controller(WarehouseController::class)->group(function(){
            Route::get('/all/warehouse','allWarehouse')->name('all.warehouse');
            Route::get('/add/warehouse','addWarehouse')->name('add.warehouse');
            Route::get('/edit/warehouse/{id}','edit')->name('edit.warehouse');
            Route::post('/store/warehouse','store')->name('store.warehouse');
            Route::put('/update/warehouse/{id}','update')->name('update.warehouse');
            Route::get('/delete/warehouse/{id}','destroy')->name('delete.warehouse');
             Route::post('/check-email', 'checkEmail')->name('check.email');

               });

            //Supplier Routes//
            Route::controller(SupplierController::class)->group(function(){
                Route::get('/all/supplier','allSupplier')->name('all.supplier');
                Route::get('/add/supplier','addSupplier')->name('add.supplier');
                Route::get('/edit/supplier/{id}','edit')->name('edit.supplier');
                Route::post('/store/supplier','store')->name('store.supplier');
                Route::put('/update/supplier/{id}','update')->name('update.supplier');
                Route::get('/delete/supplier/{id}','destroy')->name('delete.supplier');
                Route::post('/check-email', 'checkEmail')->name('check.email');

            });

            //Customer Routes//
            Route::resource('customers', CustomerController::class);
            Route::post('/check-email', [CustomerController::class, 'checkEmail'])->name('check.email');

             //Category Routes//
            Route::resource('category', ProductCategoryController::class);

              //Product Routes//
            Route::resource('product', ProductController::class);

              //Purchase Routes//
            Route::controller(PurchaseController::class)->group(function(){
                Route::get('/purchase/index','index')->name('purchase.index');
                Route::get('/purchase/create','create')->name('purchase.create');
                Route::get('/purchase/product/search','purchaseProductSearch')
                ->name('purchase.product.search');

                Route::post('/purchase/store','store')->name('purchase.store');
                Route::get('/purchase/edit/{id}','edit')->name('purchase.edit');
                Route::put('/purchase/update/{id}','update')->name('purchase.update');
                Route::get('/purchase/show/{id}','show')->name('purchase.show');
                Route::get('/purchase/invoice/{id}','invoice')->name('purchase.invoice');
                Route::delete('/purchase/delete/{id}','destroy')->name('purchase.delete');


            });

            //Return Purchase Routes//
            Route::controller(PurchaseReturnController::class)->group(function(){
                Route::get('/returnpurchase/index','returnPurchaseIndex')
                ->name('return-purchase.index');
                 Route::get('/returnpurchase/create','returnPurchaseCreate')
                ->name('return-purchase.create');
                 Route::post('/returnpurchase/store','returnPurchaseStore')
                ->name('return-purchase.store');
                  Route::get('/returnpurchase/create','returnPurchaseCreate')
                ->name('return-purchase.create');
                  Route::get('/returnpurchase/detail/{id}','returnPurchaseDetail')
                ->name('return-purchase.detail');
                 Route::get('/returnpurchase/invoice/{id}','returnPurchaseInvoice')
                ->name('return-purchase.invoice');
                Route::get('/returnpurchase/edit/{id}','returnPurchaseEdit')
                ->name('return-purchase.edit');
                Route::put('/returnpurchase/update/{id}','returnPurchaseUpdate')
                ->name('return-purchase.update');
                 Route::delete('/returnpurchase/delete/{id}','returnPurchaseDestroy')
                 ->name('return-purchase.delete');
            });


            //Sale Routes//
            Route::controller(SaleController::class)->group(function(){
                Route::get('/sale/index','index')
                ->name('sale.index');
                Route::get('/sale/create','create')
                ->name('sale.create');
                Route::post('/sale/store','store')
                ->name('sale.store');
                  Route::get('/sale/edit/{id}','edit')
                ->name('sale.edit');
                  Route::put('/sale/update/{id}','update')
                ->name('sale.update');
                 Route::delete('/sale/delete/{id}','Delete')
                ->name('sale.delete');
                  Route::get('/sale/details/{id}','Details')
                ->name('sale.details');
                  Route::get('/sale/invoice/{id}','Invoice')
                ->name('sale.invoice');
            });

});

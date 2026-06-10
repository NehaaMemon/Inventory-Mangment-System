<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\backend\BrandController;
use App\Http\Controllers\backend\CustomerController;
use App\Http\Controllers\backend\DueController;
use App\Http\Controllers\backend\ProductCategoryController;
use App\Http\Controllers\backend\ProductController;
use App\Http\Controllers\backend\PurchaseController;
use App\Http\Controllers\backend\PurchaseReturnController;
use App\Http\Controllers\backend\ReportController;
use App\Http\Controllers\backend\ReturnSaleController;
use App\Http\Controllers\backend\RoleController;
use App\Http\Controllers\backend\SaleController;
use App\Http\Controllers\backend\SupplierController;
use App\Http\Controllers\backend\TransferController;
use App\Http\Controllers\backend\WarehouseController;
use App\Http\Controllers\ProfileController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\WareHouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', function () {
    $startMonth = now()->startOfMonth()->subMonths(11);

    $monthKeys = collect(range(0, 11))->map(fn ($month) => $startMonth->copy()->addMonths($month)->format('Y-m'));
    $monthLabels = $monthKeys->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'));

    $monthlySales = Sale::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(grand_total) as total")
        ->where('date', '>=', $startMonth->toDateString())
        ->groupBy('month')
        ->pluck('total', 'month');

    $monthlySaleReturns = SaleReturn::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(grand_total) as total")
        ->where('date', '>=', $startMonth->toDateString())
        ->groupBy('month')
        ->pluck('total', 'month');

    $dashboard = [
        'sales_count' => Sale::count(),
        'sales_total' => Sale::sum('grand_total'),
        'sale_returns_count' => SaleReturn::count(),
        'sale_returns_total' => SaleReturn::sum('grand_total'),
        'purchases_count' => Purchase::count(),
        'purchases_total' => Purchase::sum('grand_total'),
        'purchase_returns_count' => PurchaseReturn::count(),
        'purchase_returns_total' => PurchaseReturn::sum('grand_total'),
        'sale_due_total' => Sale::sum('due_amount'),
        'sale_due_count' => Sale::where('due_amount', '>', 0)->count(),
        'sale_return_due_total' => SaleReturn::sum('due_amount'),
        'suppliers_count' => Supplier::count(),
        'customers_count' => Customer::count(),
        'products_count' => Product::count(),
        'stock_units' => Product::sum('product_qty'),
        'warehouses_count' => WareHouse::count(),
        'low_stock_count' => Product::whereColumn('product_qty', '<=', 'stock_alert')->count(),
    ];

    $chart = [
        'months' => $monthLabels->values(),
        'sales' => $monthKeys->map(fn ($month) => (float) ($monthlySales[$month] ?? 0))->values(),
        'sale_returns' => $monthKeys->map(fn ($month) => (float) ($monthlySaleReturns[$month] ?? 0))->values(),
    ];

    $topProducts = SaleItem::select('product_id', DB::raw('SUM(quantity) as sold_qty'), DB::raw('SUM(subtotal) as sold_amount'))
        ->with('product:id,name,code,product_qty')
        ->groupBy('product_id')
        ->orderByDesc('sold_qty')
        ->take(5)
        ->get();

    $lowStockProducts = Product::with(['category:id,category_name', 'warehouse:id,name'])
        ->select('id', 'name', 'code', 'category_id', 'warehouse_id', 'product_qty', 'stock_alert')
        ->whereColumn('product_qty', '<=', 'stock_alert')
        ->orderBy('product_qty')
        ->take(5)
        ->get();

    $latestSales = Sale::with(['customer:id,name', 'warehouse:id,name'])
        ->latest()
        ->take(5)
        ->get();

    return view('admin.index', compact('dashboard', 'chart', 'topProducts', 'lowStockProducts', 'latestSales'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__ . '/auth.php';
Route::get('/admin/logout', [AdminController::class, 'destroy'])
    ->name('admin.logout');
Route::post('/admin/login', [AdminController::class, 'AdminLogin'])
    ->name('admin.login');

//2step verification routes//
Route::get('/verify', [AdminController::class, 'showVerification'])
    ->name('custom.verification.Form');
Route::post('/verify', [AdminController::class, 'verificationVerify'])
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
    Route::controller(BrandController::class)->group(function () {
        Route::get('/all/brand', 'allBrand')->name('all.brand');
        Route::get('/add/brand', 'addBrand')->name('add.brand');
        Route::get('/edit/brand/{id}', 'editBrand')->name('edit.brand');
        Route::post('/store/brand', 'storeBrand')->name('store.brand');
        Route::put('/update/brand/{id}', 'updateBrand')->name('update.brand');
        Route::get('/delete/brand/{id}', 'deleteBrand')->name('delete.brand');
    });
    //WareHouse Routes//
    Route::controller(WarehouseController::class)->group(function () {
        Route::get('/all/warehouse', 'allWarehouse')->name('all.warehouse');
        Route::get('/add/warehouse', 'addWarehouse')->name('add.warehouse');
        Route::get('/edit/warehouse/{id}', 'edit')->name('edit.warehouse');
        Route::post('/store/warehouse', 'store')->name('store.warehouse');
        Route::put('/update/warehouse/{id}', 'update')->name('update.warehouse');
        Route::get('/delete/warehouse/{id}', 'destroy')->name('delete.warehouse');
        Route::post('/check-email', 'checkEmail')->name('check.email');
    });

    //Supplier Routes//
    Route::controller(SupplierController::class)->group(function () {
        Route::get('/all/supplier', 'allSupplier')->name('all.supplier');
        Route::get('/add/supplier', 'addSupplier')->name('add.supplier');
        Route::get('/edit/supplier/{id}', 'edit')->name('edit.supplier');
        Route::post('/store/supplier', 'store')->name('store.supplier');
        Route::put('/update/supplier/{id}', 'update')->name('update.supplier');
        Route::get('/delete/supplier/{id}', 'destroy')->name('delete.supplier');
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
    Route::controller(PurchaseController::class)->group(function () {
        Route::get('/purchase/index', 'index')->name('purchase.index');
        Route::get('/purchase/create', 'create')->name('purchase.create');
        Route::get('/purchase/product/search', 'purchaseProductSearch')
            ->name('purchase.product.search');

        Route::post('/purchase/store', 'store')->name('purchase.store');
        Route::get('/purchase/edit/{id}', 'edit')->name('purchase.edit');
        Route::put('/purchase/update/{id}', 'update')->name('purchase.update');
        Route::get('/purchase/show/{id}', 'show')->name('purchase.show');
        Route::get('/purchase/invoice/{id}', 'invoice')->name('purchase.invoice');
        Route::delete('/purchase/delete/{id}', 'destroy')->name('purchase.delete');
    });

    //Return Purchase Routes//
    Route::controller(PurchaseReturnController::class)->group(function () {
        Route::get('/returnpurchase/index', 'returnPurchaseIndex')
            ->name('return-purchase.index');
        Route::get('/returnpurchase/create', 'returnPurchaseCreate')
            ->name('return-purchase.create');
        Route::post('/returnpurchase/store', 'returnPurchaseStore')
            ->name('return-purchase.store');
        Route::get('/returnpurchase/create', 'returnPurchaseCreate')
            ->name('return-purchase.create');
        Route::get('/returnpurchase/detail/{id}', 'returnPurchaseDetail')
            ->name('return-purchase.detail');
        Route::get('/returnpurchase/invoice/{id}', 'returnPurchaseInvoice')
            ->name('return-purchase.invoice');
        Route::get('/returnpurchase/edit/{id}', 'returnPurchaseEdit')
            ->name('return-purchase.edit');
        Route::put('/returnpurchase/update/{id}', 'returnPurchaseUpdate')
            ->name('return-purchase.update');
        Route::delete('/returnpurchase/delete/{id}', 'returnPurchaseDestroy')
            ->name('return-purchase.delete');
    });


    //Sale Routes//
    Route::controller(SaleController::class)->group(function () {
        Route::get('/sale/index', 'index')
            ->name('sale.index');
        Route::get('/sale/create', 'create')
            ->name('sale.create');
        Route::post('/sale/store', 'store')
            ->name('sale.store');
        Route::get('/sale/edit/{id}', 'edit')
            ->name('sale.edit');
        Route::put('/sale/update/{id}', 'update')
            ->name('sale.update');
        Route::delete('/sale/delete/{id}', 'Delete')
            ->name('sale.delete');
        Route::get('/sale/details/{id}', 'Details')
            ->name('sale.details');
        Route::get('/sale/invoice/{id}', 'Invoice')
            ->name('sale.invoice');
    });


    //Sale Return Routes//
    Route::controller(ReturnSaleController::class)->group(function () {
        Route::get('/sale-return/index', 'index')
            ->name('sale-return.index');
        Route::get('/sale-return/create', 'create')
            ->name('sale-return.create');
        Route::post('/sale-return/store', 'store')
            ->name('sale-return.store');
        Route::get('/sale-return/edit/{id}', 'edit')
            ->name('sale-return.edit');
        Route::put('/sale-return/update/{id}', 'update')
            ->name('sale-return.update');
        Route::get('/sale-return/detail/{id}', 'detail')
            ->name('sale-return.detail');
        Route::get('/sale-return/invoice/{id}', 'invoice')
            ->name('sale-return.invoice');
        Route::delete('/sale-return/delete/{id}', 'destroy')
            ->name('sale-return.delete');
    });

    //Due Routes//
    Route::controller(DueController::class)->group(function () {
        Route::get('/due/sale_due', 'dueSale')
            ->name('due.sale_due');
        Route::get('/due/sale-return_due', 'dueSaleReturn')
            ->name('due.sale-return_due');
    });

    //Transfer Routes//
    Route::controller(TransferController::class)->group(function () {
        Route::get('/transfer/index', 'index')
            ->name('transfer.index');
        Route::get('/transfer/create', 'create')
            ->name('transfer.create');
        Route::post('/transfer/store', 'store')
            ->name('transfer.store');
        Route::get('/transfer/edit/{id}', 'edit')
            ->name('transfer.edit');
        Route::put('/transfer/update/{id}', 'update')
            ->name('transfer.update');
        Route::delete('/transfer/delete/{id}', 'destroy')
            ->name('transfer.delete');
        Route::get('/transfer/details/{id}', 'details')
            ->name('transfer.details');
    });

    // Report routes
    Route::controller(ReportController::class)->group(function () {
        Route::get('/report/index', 'index')
            ->name('report.index');
        Route::get('/report/purchase-return', 'purchaseReturnReport')
            ->name('report.purchase-return');
        Route::get('/report/sale', 'saleReport')
            ->name('report.sale');
        Route::get('/report/sale-return', 'saleReturnReport')
            ->name('report.sale-return');
        Route::get('/report/stock', 'stockReport')
            ->name('report.stock');
        Route::get('/filter-purchases', 'filterPurchases')
            ->name('filter-purchases');
        Route::get('/filter-sale', 'filterSales')
            ->name('filter-sales');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/permission/index', 'index')
            ->name('permission.index');
        Route::get('/permission/add', 'create')
            ->name('add.permission');
        Route::post('/permission/store', 'store')
            ->name('store.permission');
        Route::get('/permission/edit/{id}', 'edit')
            ->name('edit.permission');
        Route::put('/permission/update/{id}', 'update')
            ->name('update.permission');
        Route::get('/permission/delete/{id}', 'destroy')
            ->name('delete.permission');
    });

        Route::controller(RoleController::class)->group(function () {
        Route::get('/role/index', 'roleIndex')
            ->name('role.index');
         Route::get('/role/add', 'rolecreate')
            ->name('add.role');
        Route::post('/role/store', 'rolestore')
            ->name('store.role');
        Route::get('/role/edit/{id}', 'roleedit')
            ->name('edit.role');
        Route::put('/role/update/{id}', 'roleupdate')
            ->name('update.role');
        Route::get('/role/delete/{id}', 'roledestroy')
            ->name('delete.role');
    });


           Route::controller(RoleController::class)->group(function () {
        Route::get('/role/all/permission', 'allPermissionToRole')
            ->name('all.permission.role');
            Route::get('/role/add/permission', 'addPermissionToRole')
            ->name('add.permission.role');
                Route::post('/role/store/permission', 'storePermissionToRole')
            ->name('store.role.permission');
           Route::get('/role/edit/permission/{id}', 'editPermissionToRole')
            ->name('edit.permission.role');
           Route::put('/role/update/permission/{id}', 'updatePermissionToRole')
            ->name('update.permission.role');
                Route::get('/role/delete/permission/{id}', 'destroyPermissionToRole')
            ->name('delete.permission.role');
    });


     Route::controller(RoleController::class)->group(function () {
        Route::get('/all/admin', 'allAdmin')
            ->name('admin.index');
        Route::get('/add/admin', 'addAdmin')
            ->name('add.admin');
        Route::post('/store/admin', 'storeAdmin')
            ->name('store.admin');
        Route::get('/edit/admin/{id}', 'editAdmin')
            ->name('edit.admin');
        Route::put('/update/admin/{id}', 'updateAdmin')
            ->name('update.admin');
        Route::get('/delete/admin/{id}', 'destroyAdmin')
            ->name('delete.admin');
          
    });
});

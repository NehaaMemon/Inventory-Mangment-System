@extends('admin.admin_master')

@section('admin')
@php
    $db = app('db');
    $money = fn ($amount) => 'Rs ' . number_format((float) $amount, 2);
    $num = fn ($value) => number_format((float) $value);

    $salesCount = $db->table('sales')->count();
    $salesTotal = $db->table('sales')->sum('grand_total');
    $saleReturnsCount = $db->table('sale_returns')->count();
    $saleReturnsTotal = $db->table('sale_returns')->sum('grand_total');
    $purchasesCount = $db->table('purchases')->count();
    $purchasesTotal = $db->table('purchases')->sum('grand_total');
    $purchaseReturnsCount = $db->table('purchase_returns')->count();
    $purchaseReturnsTotal = $db->table('purchase_returns')->sum('grand_total');
    $saleDueTotal = $db->table('sales')->sum('due_amount');
    $saleDueCount = $db->table('sales')->where('due_amount', '>', 0)->count();
    $saleReturnDueTotal = $db->table('sale_returns')->sum('due_amount');
    $productsCount = $db->table('products')->count();
    $stockUnits = $db->table('products')->sum('product_qty');
    $lowStockCount = $db->table('products')->whereColumn('product_qty', '<=', 'stock_alert')->count();
    $suppliersCount = $db->table('suppliers')->count();
    $customersCount = $db->table('customers')->count();
    $warehousesCount = $db->table('ware_houses')->count();

    $startMonth = now()->startOfMonth()->subMonths(11);
    $monthKeys = collect(range(0, 11))->map(fn ($month) => $startMonth->copy()->addMonths($month)->format('Y-m'));
    $monthLabels = $monthKeys->map(fn ($month) => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'));

    $monthlySales = $db->table('sales')
        ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(grand_total) as total")
        ->where('date', '>=', $startMonth->toDateString())
        ->groupBy('month')
        ->pluck('total', 'month');

    $monthlyReturns = $db->table('sale_returns')
        ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(grand_total) as total")
        ->where('date', '>=', $startMonth->toDateString())
        ->groupBy('month')
        ->pluck('total', 'month');

    $chartMonths = $monthLabels->values();
    $chartSales = $monthKeys->map(fn ($month) => (float) ($monthlySales[$month] ?? 0))->values();
    $chartReturns = $monthKeys->map(fn ($month) => (float) ($monthlyReturns[$month] ?? 0))->values();

    $topProducts = $db->table('sale_items')
        ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
        ->select('products.name', 'products.code')
        ->selectRaw('SUM(sale_items.quantity) as sold_qty, SUM(sale_items.subtotal) as sold_amount')
        ->groupBy('sale_items.product_id', 'products.name', 'products.code')
        ->orderByDesc('sold_qty')
        ->limit(5)
        ->get();

    $lowStockProducts = $db->table('products')
        ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
        ->leftJoin('ware_houses', 'products.warehouse_id', '=', 'ware_houses.id')
        ->select('products.name', 'products.code', 'products.product_qty', 'products.stock_alert', 'product_categories.category_name', 'ware_houses.name as warehouse_name')
        ->whereColumn('products.product_qty', '<=', 'products.stock_alert')
        ->orderBy('products.product_qty')
        ->limit(5)
        ->get();

    $latestSales = $db->table('sales')
        ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
        ->leftJoin('ware_houses', 'sales.warehouse_id', '=', 'ware_houses.id')
        ->select('sales.date', 'sales.grand_total', 'sales.due_amount', 'customers.name as customer_name', 'ware_houses.name as warehouse_name')
        ->orderByDesc('sales.id')
        ->limit(5)
        ->get();
@endphp

<style>
    .inventory-hero {
        background: linear-gradient(135deg, #172033 0%, #204354 52%, #1f6f68 100%);
        border-radius: 8px;
        color: #fff;
        padding: 24px;
        overflow: hidden;
        position: relative;
    }

    .inventory-hero:after {
        content: "";
        position: absolute;
        inset: auto -80px -110px auto;
        width: 260px;
        height: 260px;
        border: 42px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
    }

    .metric-card {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(31, 45, 61, .08);
        min-height: 142px;
    }

    .metric-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .mini-stat {
        border: 1px solid #edf1f5;
        border-radius: 8px;
        background: #fff;
        padding: 16px;
        height: 100%;
    }

    .dashboard-table th {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
</style>

<div class="content">
    <div class="container-xxl">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Inventory Dashboard</h4>
                <p class="text-muted mb-0 mt-1">Sales, purchases, returns, dues and stock overview.</p>
            </div>
        </div>

        <div class="inventory-hero mb-3">
            <div class="row align-items-center g-3 position-relative">
                <div class="col-lg-7">
                    <span class="badge bg-light text-dark mb-3">Live inventory summary</span>
                    <h3 class="text-white fw-semibold mb-2">Business overview at a glance</h3>
                    <p class="mb-0 text-white-50">Total sales {{ $money($salesTotal) }}, stock units {{ $num($stockUnits) }}, and pending sale dues {{ $money($saleDueTotal) }}.</p>
                </div>
                <div class="col-lg-5">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3 text-center">
                                <div class="fs-22 fw-semibold">{{ $num($productsCount) }}</div>
                                <small class="text-white-50">Products</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3 text-center">
                                <div class="fs-22 fw-semibold">{{ $num($suppliersCount) }}</div>
                                <small class="text-white-50">Suppliers</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3 text-center">
                                <div class="fs-22 fw-semibold">{{ $num($customersCount) }}</div>
                                <small class="text-white-50">Customers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Sales</p>
                                <h4 class="fw-semibold mb-1">{{ $money($salesTotal) }}</h4>
                                <span class="badge bg-primary-subtle text-primary">{{ $num($salesCount) }} invoices</span>
                            </div>
                            <span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid fa-cart-shopping"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Sale Returns</p>
                                <h4 class="fw-semibold mb-1">{{ $money($saleReturnsTotal) }}</h4>
                                <span class="badge bg-danger-subtle text-danger">{{ $num($saleReturnsCount) }} returns</span>
                            </div>
                            <span class="metric-icon bg-danger-subtle text-danger"><i class="fa-solid fa-rotate-left"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Purchases</p>
                                <h4 class="fw-semibold mb-1">{{ $money($purchasesTotal) }}</h4>
                                <span class="badge bg-success-subtle text-success">{{ $num($purchasesCount) }} records</span>
                            </div>
                            <span class="metric-icon bg-success-subtle text-success"><i class="fa-solid fa-bag-shopping"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Sale Dues</p>
                                <h4 class="fw-semibold mb-1">{{ $money($saleDueTotal) }}</h4>
                                <span class="badge bg-warning-subtle text-warning">{{ $num($saleDueCount) }} pending</span>
                            </div>
                            <span class="metric-icon bg-warning-subtle text-warning"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Products</p><h4 class="mb-0">{{ $num($productsCount) }}</h4><small class="text-muted">{{ $num($stockUnits) }} units</small></div></div>
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Low Stock</p><h4 class="mb-0">{{ $num($lowStockCount) }}</h4><small class="text-muted">Need reorder</small></div></div>
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Suppliers</p><h4 class="mb-0">{{ $num($suppliersCount) }}</h4><small class="text-muted">Total vendors</small></div></div>
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Warehouses</p><h4 class="mb-0">{{ $num($warehousesCount) }}</h4><small class="text-muted">Locations</small></div></div>
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Purchase Return</p><h4 class="mb-0">{{ $money($purchaseReturnsTotal) }}</h4><small class="text-muted">{{ $num($purchaseReturnsCount) }} records</small></div></div>
            <div class="col-md-6 col-xl-2"><div class="mini-stat"><p class="text-muted mb-1">Return Dues</p><h4 class="mb-0">{{ $money($saleReturnDueTotal) }}</h4><small class="text-muted">Sale return due</small></div></div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Monthly Sales Graph</h5>
                        <span class="badge bg-light text-dark">Last 12 months</span>
                    </div>
                    <div class="card-body">
                        <div id="inventory-sales-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Top Selling Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover dashboard-table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $item->name ?? 'Deleted Product' }}</div>
                                                <small class="text-muted">{{ $item->code ?? '-' }}</small>
                                            </td>
                                            <td class="text-end">{{ $num($item->sold_qty) }}</td>
                                            <td class="text-end">{{ $money($item->sold_amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">No sales data found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Low Stock Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover dashboard-table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Warehouse</th>
                                        <th class="text-end">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStockProducts as $product)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $product->name }}</div>
                                                <small class="text-muted">{{ $product->code }}</small>
                                            </td>
                                            <td>{{ $product->category_name ?? '-' }}</td>
                                            <td>{{ $product->warehouse_name ?? '-' }}</td>
                                            <td class="text-end"><span class="badge bg-danger-subtle text-danger">{{ $num($product->product_qty) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No low stock products</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Latest Sales</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover dashboard-table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Warehouse</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestSales as $sale)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                                            <td>{{ $sale->customer_name ?? '-' }}</td>
                                            <td>{{ $sale->warehouse_name ?? '-' }}</td>
                                            <td class="text-end">{{ $money($sale->grand_total) }}</td>
                                            <td class="text-end">{{ $money($sale->due_amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No sales found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-none">
            <div id="website-visitors"></div><div id="conversion-visitors"></div><div id="session-visitors"></div><div id="active-users"></div><div id="monthly-sales"></div><div id="audiences-daily"></div>
            <div id="sparkline-bounce-1"></div><div id="sparkline-bounce-2"></div><div id="sparkline-bounce-3"></div><div id="sparkline-bounce-4"></div><div id="sparkline-bounce-5"></div><div id="sparkline-bounce-6"></div><div id="sparkline-bounce-7"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var chartElement = document.querySelector('#inventory-sales-chart');

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        new ApexCharts(chartElement, {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            series: [
                { name: 'Sales', data: @json($chartSales) },
                { name: 'Sale Returns', data: @json($chartReturns) }
            ],
            colors: ['#2563eb', '#e11d48'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.32,
                    opacityTo: 0.06
                }
            },
            xaxis: {
                categories: @json($chartMonths),
                labels: { rotate: -35 }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return 'Rs ' + Number(value).toLocaleString();
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return 'Rs ' + Number(value).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            },
            grid: { strokeDashArray: 4 },
            legend: { position: 'top' }
        }).render();
    });
</script>
@endpush

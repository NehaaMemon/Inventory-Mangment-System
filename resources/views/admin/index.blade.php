@extends('admin.admin_master')

@section('admin')
    @php
        $money = fn ($amount) => number_format((float) $amount, 2);
        $number = fn ($value) => number_format((float) $value);
    @endphp

    <div class="content">
        <div class="container-xxl">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Inventory Dashboard</h4>
                    <p class="text-muted mb-0 mt-1">Sales, stock, purchases, dues and latest activity overview.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Sales</p>
                                    <h4 class="mb-1 fw-semibold">Rs {{ $money($dashboard['sales_total']) }}</h4>
                                    <span class="badge bg-primary-subtle text-primary">{{ $number($dashboard['sales_count']) }} invoices</span>
                                </div>
                                <div class="avatar-sm rounded bg-primary-subtle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-cart-shopping text-primary fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Sale Returns</p>
                                    <h4 class="mb-1 fw-semibold">Rs {{ $money($dashboard['sale_returns_total']) }}</h4>
                                    <span class="badge bg-danger-subtle text-danger">{{ $number($dashboard['sale_returns_count']) }} returns</span>
                                </div>
                                <div class="avatar-sm rounded bg-danger-subtle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-rotate-left text-danger fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Purchases</p>
                                    <h4 class="mb-1 fw-semibold">Rs {{ $money($dashboard['purchases_total']) }}</h4>
                                    <span class="badge bg-success-subtle text-success">{{ $number($dashboard['purchases_count']) }} purchases</span>
                                </div>
                                <div class="avatar-sm rounded bg-success-subtle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-bag-shopping text-success fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Sale Dues</p>
                                    <h4 class="mb-1 fw-semibold">Rs {{ $money($dashboard['sale_due_total']) }}</h4>
                                    <span class="badge bg-warning-subtle text-warning">{{ $number($dashboard['sale_due_count']) }} pending bills</span>
                                </div>
                                <div class="avatar-sm rounded bg-warning-subtle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-file-invoice-dollar text-warning fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Products</p>
                            <h4 class="mb-0 fw-semibold">{{ $number($dashboard['products_count']) }}</h4>
                            <small class="text-muted">{{ $number($dashboard['stock_units']) }} stock units</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Suppliers</p>
                            <h4 class="mb-0 fw-semibold">{{ $number($dashboard['suppliers_count']) }}</h4>
                            <small class="text-muted">Active records</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Customers</p>
                            <h4 class="mb-0 fw-semibold">{{ $number($dashboard['customers_count']) }}</h4>
                            <small class="text-muted">Total customers</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Warehouses</p>
                            <h4 class="mb-0 fw-semibold">{{ $number($dashboard['warehouses_count']) }}</h4>
                            <small class="text-muted">Stock locations</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Low Stock</p>
                            <h4 class="mb-0 fw-semibold">{{ $number($dashboard['low_stock_count']) }}</h4>
                            <small class="text-muted">Need attention</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Purchase Returns</p>
                            <h4 class="mb-0 fw-semibold">Rs {{ $money($dashboard['purchase_returns_total']) }}</h4>
                            <small class="text-muted">{{ $number($dashboard['purchase_returns_count']) }} returns</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex align-items-center">
                                <div class="border rounded-2 me-2 widget-icons-sections">
                                    <i data-feather="bar-chart" class="widgets-icons"></i>
                                </div>
                                <h5 class="card-title mb-0">Monthly Sales Graph</h5>
                            </div>
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
                                <table class="table table-hover mb-0 align-middle">
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
                                                    <div class="fw-semibold">{{ $item->product->name ?? 'Deleted Product' }}</div>
                                                    <small class="text-muted">{{ $item->product->code ?? '-' }}</small>
                                                </td>
                                                <td class="text-end">{{ $number($item->sold_qty) }}</td>
                                                <td class="text-end">Rs {{ $money($item->sold_amount) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No sales data found</td>
                                            </tr>
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
                                <table class="table table-hover mb-0 align-middle">
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
                                                <td>{{ $product->category->category_name ?? '-' }}</td>
                                                <td>{{ $product->warehouse->name ?? '-' }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-danger-subtle text-danger">{{ $number($product->product_qty) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No low stock products</td>
                                            </tr>
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
                                <table class="table table-hover mb-0 align-middle">
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
                                                <td>{{ $sale->customer->name ?? '-' }}</td>
                                                <td>{{ $sale->warehouse->name ?? '-' }}</td>
                                                <td class="text-end">Rs {{ $money($sale->grand_total) }}</td>
                                                <td class="text-end">Rs {{ $money($sale->due_amount) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No sales found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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

            var options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false }
                },
                series: [
                    {
                        name: 'Sales',
                        data: @json($chart['sales'])
                    },
                    {
                        name: 'Sale Returns',
                        data: @json($chart['sale_returns'])
                    }
                ],
                colors: ['#537AEF', '#ec8290'],
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.28,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: @json($chart['months']),
                    labels: {
                        rotate: -35
                    }
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
                grid: {
                    strokeDashArray: 4
                },
                legend: {
                    position: 'top'
                }
            };

            new ApexCharts(chartElement, options).render();
        });
    </script>
@endpush

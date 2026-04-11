<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;


class ReportController extends Controller
{
    public function index() : View {
         $salesCount = Sale::count();
        $salesReturn = SaleReturn::count();
        $products = Product::count();
        $purchase = Purchase::count();
        $purchaseReturn = PurchaseReturn::count();
        $purchases = Purchase::with(['supplier', 'purchaseItems.product','warehouse'])
            ->get();
        return view('admin.backend.report.index',
        compact('purchases','salesCount','salesReturn','products','purchase','purchaseReturn'));

    }

    public function filterPurchases(Request $request): JsonResponse {
       $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $query = Purchase::with(['supplier', 'purchaseItems.product','warehouse']);


        if($startDate && $endDate) {
            try {
                $parsedStartDate = Carbon::parse($startDate)->format('Y-m-d');
                $parsedEndDate = Carbon::parse($endDate)->format('Y-m-d');
                $query->whereBetween('date', [$parsedStartDate, $parsedEndDate]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid date format.',
                    'purchases' => [],
                ], 422);
            }
        }
        $purchases = $query->get();
        return response()->json(['purchases' => $purchases]);


    }

        public function purchaseReturnReport() : View {
         $salesCount = Sale::count();
        $salesReturn = SaleReturn::count();
        $products = Product::count();
        $purchase = Purchase::count();
        $purchaseReturn = PurchaseReturn::count();
        $purchaseReturns = PurchaseReturn::with(['supplier', 'purchaseItems.product','warehouse'])
            ->get();
        return view('admin.backend.report.purchase-return',
        compact('purchaseReturns','salesCount','salesReturn','products','purchase','purchaseReturn'));

    }

        public function saleReport() : View {
            $salesCount = Sale::count();
            $salesReturn = SaleReturn::count();
            $products = Product::count();
            $purchase = Purchase::count();
            $purchaseReturn = PurchaseReturn::count();
            $sales= Sale::with(['customer', 'saleItems.product','warehouse'])
                ->get();
            return view('admin.backend.report.sale',
            compact('sales','salesCount','salesReturn','products','purchase','purchaseReturn'));

        }

        public function filterSales(Request $request): JsonResponse {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $query = Sale::with(['customer', 'saleItems.product','warehouse']);


        if($startDate && $endDate) {
            try {
                $parsedStartDate = Carbon::parse($startDate)->format('Y-m-d');
                $parsedEndDate = Carbon::parse($endDate)->format('Y-m-d');
                $query->whereBetween('date', [$parsedStartDate, $parsedEndDate]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid date format.',
                    'purchases' => [],
                ], 422);
            }
        }
        $sales = $query->get();
        return response()->json(['sales' => $sales]);
        }


            public function saleReturnReport() : View {
                $salesCount = Sale::count();
                $salesReturn = SaleReturn::count();
                $products = Product::count();
                $purchase = Purchase::count();
                $purchaseReturn = PurchaseReturn::count();
                $saleReturn= SaleReturn::with(['customer', 'saleReturnItems.product','warehouse'])
                    ->get();
                return view('admin.backend.report.sale-return',
                compact('saleReturn','salesCount','salesReturn','products','purchase','purchaseReturn'));

            }
                public function stockReport() : View {
                    $salesCount = Sale::count();
                    $salesReturn = SaleReturn::count();
                    $products = Product::count();
                    $purchase = Purchase::count();
                    $purchaseReturn = PurchaseReturn::count();
                    $stock = Product::with(['category', 'warehouse'])
                        ->get();
                    return view('admin.backend.report.stock',
                    compact('stock','salesCount','salesReturn','products','purchase','purchaseReturn'));

                }
}

@extends('admin.admin_master')
@section('admin')
 <div class="content">

<!-- Start Content-->
<div class="container-xxl">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">All Sale</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <a href="{{ route('sale.create') }}" class="btn btn-secondary">
                    Add Sale
                </a>

            </ol>
        </div>
    </div>

    <!-- Datatables  -->
    <div class="row">
        <div class="col-12">
            <div class="card">

    <div class="card-header">
        {{-- <h5 class="card-title mb-0">All Brand</h5> --}}
    </div><!-- end card header -->

    <div class="card-body">
        <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
            <thead>
            <tr>
                <th>Id</th>
                <th>WareHouse</th>
                <th>Status</th>
                <th>Grand Total</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Created</th>
                <th>Action </th>
            </tr>
            </thead>
            <tbody>
                @foreach ($sales as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->warehouse->name }}</td>
                    <td>
                    @if($item->status === 'Sale')
                        <span class="badge bg-success main-btn">Sale</span>
                    @elseif($item->status === 'Ordered')
                        <span class="badge bg-warning main-btn">Ordered</span>
                    @else
                        <span class="badge bg-danger main-btn">Pending</span>
                    @endif
                </td>

                    <td>$ {{ $item->grand_total }}</td>
                     <td><h4><span class="badge bg-success ">{{ $item->paid_amount }}</span></h4></td>
                    <td><h4><span class="badge bg-warning ">{{ $item->due_amount }}</span></h4></td>

                    <td>{{ $item->created_at->format('d-m-Y ') }}

                        </td>
                        {{-- <td>
                        @if($item->status == 1)
                        <span class="badge bg-success">Deliver</span>
                        @else
                        <span class="badge bg-danger">Decline</span>
                        @endif
                    </td> --}}


                        <th>
                        <a title="Edit" href="{{ route('sale.edit',$item->id) }}"
                            class="btn btn-warning"><i class="fa-solid fa-pen-to-square">
                                </i></a>

                        <a title="Detail" href="{{ route('sale.details',$item->id) }}"
                            class="btn btn-info"><i class="fa-regular fa-eye"></i></a>

                        <a title="PDF Invoice" href="{{ route('sale.invoice',$item->id) }}"
                             class="btn btn-primary"><i class="fa-solid fa-download"></i></a>

                <form action="{{ route('sale.delete', $item->id) }}" method="POST" class="delete-form" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button title="Delete" type="button" class="btn btn-danger delete-btn"><i class="fa-regular fa-trash-can"></i></button>
                    </form>

                    </th>
                </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

@endsection




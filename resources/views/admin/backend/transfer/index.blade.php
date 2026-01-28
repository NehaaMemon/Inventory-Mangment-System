@extends('admin.admin_master')
@section('admin')
    <div class="content">

        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">All Transfer</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('transfer.create') }}" class="btn btn-secondary">
                            Add Transfer
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
                                        <th>Date</th>
                                        <th>From WareHouse</th>
                                        <th>To WareHouse</th>
                                        <th>Product</th>
                                        <th>Stock Transfer</th>
                                        <th>Action </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfer as $key => $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->created_at->format('d-m-Y ') }} </td>
                                            <td>{{ $item->from_warehouse->name }}</td>
                                            <td> {{ $item->to_warehouse->name }} </td>
                                            <td>
                                                @foreach ($item->transferItems as $transferItem)
                                                    {{ $transferItems->product }}
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($item->transferItems as $transferItem)
                                                    {{ $transferItem->quantity }}
                                                @endforeach
                                            </td>



                                            <th>
                                                <a title="Edit" href="{{ route('sale.edit', $item->id) }}"
                                                    class="btn btn-warning"><i class="fa-solid fa-pen-to-square">
                                                    </i></a>

                                                <a title="Detail" href="{{ route('sale.details', $item->id) }}"
                                                    class="btn btn-info"><i class="fa-regular fa-eye"></i></a>



                                                <form action="{{ route('sale.delete', $item->id) }}" method="POST"
                                                    class="delete-form" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button title="Delete" type="button"
                                                        class="btn btn-danger delete-btn"><i
                                                            class="fa-regular fa-trash-can"></i></button>
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

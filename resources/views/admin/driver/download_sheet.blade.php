@extends('admin.layout.yajra')
@php

$util = new \App\Utils\dateUtil();

@endphp
@section('content')
        <div class="page-title">
            <div class="row">
                <div class="col-6">

                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>

                    </ol>
                </div>
            </div>
        </div>
        </div>
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="row">

            </div>
        </div>

       <div class="card">
    <div class="card-header">
        <h4>Please Select  Date Range And Download Report</h4>
    </div>
    <div class="card-body">
        <form id="filter-form" class="form-inline mb-3">
            <input type="date" name="from" class="form-control mx-2 mb-3" placeholder="From Date" required>
            <input type="date" name="to" class="form-control mx-2 mb-3" placeholder="To Date" required>
            <button type="submit" class="btn btn-primary mx-2 mb-3">Filter</button>
            {{--  <a href="#" id="export-btn" class="btn btn-success mb-3">Download Excel</a>--}}
        </form>

        <table class="table table-bordered" id="driver-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Balance</th>
                    <th>Deductions</th>

                </tr>
            </thead>
        </table>
    </div>
</div>


@endsection

@section('js')
<script>
    let table;

    function loadTable(from, to) {
        table = $('#driver-table').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            ajax: {
                url: '{{ url('/admin/download/drivers_sheet') }}',
                data: { from: from, to: to }
            },
            dom: 'Bfrtip', // 👈 enables buttons
        buttons: [
            {
                extend: 'excelHtml5',
                title: `Driver_Balances_${from}_to_${to}`, // 👈 filename includes date range
                filename: `Driver_Balances_${from}_to_${to}`,
                exportOptions: {
                    columns: [0, 1, 2,3]
                }
            },
            {
                extend: 'csvHtml5',
                title: `Driver_Balances_${from}_to_${to}`,
                filename: `Driver_Balances_${from}_to_${to}`,
                exportOptions: {
                    columns: [0, 1, 2,3]
                }
            },
            {
                extend: 'print',
                title: `Driver Balances from ${from} to ${to}`, // 👈 shown in print header
                exportOptions: {
                    columns: [0, 1, 2,3]
                }
            }
        ],
            columns: [
                { data: 'driver_id', name: 'id' },
                { data: 'username', name: 'name' },
                { data: 'balance', name: 'balance' },
                { data: 'credit_history_total', name: 'credit_history_total' },

            ]
        });
    }
     $(document).ready(function () {
        let defaultFrom = new Date().toISOString().split('T')[0];
        let defaultTo = defaultFrom;

        loadTable(defaultFrom, defaultTo);

        $('#filter-form').on('submit', function (e) {
            e.preventDefault();
            let from = $('input[name="from"]').val();
            let to = $('input[name="to"]').val();
            loadTable(from, to);

            // Update export link
           // $('#export-btn').attr('href', `{{ url('driver.balances.export') }}?from=${from}&to=${to}`);
        });
    });
</script>

@endsection

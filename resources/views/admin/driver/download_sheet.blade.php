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
<div class="table-responsive">
    <table class="table table-bordered" id="driver-table">
            <thead>
                <tr>

                    <th>Internal user id</th>
                    <th>Address1</th>
                    <th>Address2</th>
                    <th>City</th>
                    <th>State/Province</th>
                    <th>Postal Code</th>
                    <th>Country Code</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Bussiness Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Language</th>
                    <th>Amount</th>
                    <th>source currency</th>
                    <th>Notes</th>
                    <th>Load id/Payout id</th>
                    <th>Send Notification</th>

                </tr>
            </thead>
        </table>

</div>

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
                data: { from: from, to: to },
                 dataSrc: function (json) {
        console.log('Backend Response:', json); // 👈 FULL response
        console.log('Rows:', json.data);        // 👈 actual table rows
        return json.data;
    }
            },

            dom: 'Bfrtip', // 👈 enables buttons
        buttons: [
            {
                extend: 'excelHtml5',
                title: `Driver_Balances_${from}_to_${to}`, // 👈 filename includes date range
                filename: `Driver_Balances_${from}_to_${to}`,
               
            },
            {
                extend: 'csvHtml5',
                title: `Driver_Balances_${from}_to_${to}`,
                filename: `Driver_Balances_${from}_to_${to}`,

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

                { data: 'username', name: 'name' },
                { data: 'address1', name: 'address1' },
                { data: 'address2', name: 'address2' },
                { data: 'city', name: 'city' },
                { data: 'state', name: 'state' },
                { data: 'postal_code', name: 'postal_code' },
                { data: 'country_code', name: 'country_code' },
                { data: 'first_name', name: 'first_name' },
                { data: 'last_name', name: 'last_name' },
                { data: 'business_name', name: 'business_name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'language', name: 'language' },
                { data: 'balance', name: 'balance' },
                { data: 'currency', name: 'currency' },
                { data: 'notes', name: 'notes' },
                { data: 'load_payout_id', name: 'load_payout_id' },
                { data: 'send_notification', name: 'send_notification' },

                // { data: 'credit_history_total', name: 'credit_history_total' },

            ],

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

@extends('admin.admin-layout')
@section('css')



    <style>
        .cost-update-btn {
            display: none;
        }

        .extra-update-btn {
            display: none;
        }

        .extra-td {
            cursor: pointer;
        }

        .cost-td {
            cursor: pointer;
        }

        .cost-td:hover .cost-update-btn {
            display: block;

        }

        .extra-td:hover .extra-update-btn {
            display: block;

        }
    </style>



@endsection

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                @php
                    $util = new \App\Utils\dateUtil();
                @endphp
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
        <form action="">
            <input hidden name="tab" value="{{request()->tab}}">

            <div class="row">

                <div class="col-md-3">
                    <label>Driver</label>
                    <select class="form-control" name="driver" id="driver_id">

                        <option value="">All</option>
                        @foreach($drivers as $driver)
                            <option
                                value="{{$driver->driver_id}}">{{$driver->username}}</option>

                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Payment Method</label>
                    <select class="form-control" name="type" id="type">
                        <option value="">All</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="account">Account</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Account</label>
                    <select class="form-control" name="account" id="account">
                        <option value="">All</option>
                        @foreach($accounts as $account)
                        <option value="{{$account->account_id}}">{{$account->account_id}}</option>
                        @endforeach

                    </select>
                </div>
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" value="{{\Carbon\Carbon::now()->startOfWeek()->toDateString()}}"
                           name="from_date" id="from_date"
                           class="form-control">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" id="to_date"
                           value="{{\Carbon\Carbon::now()->endOfWeek()->toDateString()}}"
                           class="form-control">
                </div>

            </div>
        </form>
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">


                    <div class="card-body">

                        <div class="tabbed-card">

                            <div class="table-responsive">
                                <table class="table-sm table-striped" id="alltrips">
                                    <thead>
                                    <tr>

                                        <th>Trip Id</th>
                                        <th>Pin Status</th>
                                        <th>Driver Id</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Cost</th>
                                        <th>Extra</th>
                                        <th>Total Cost</th>
                                        <th>Paid</th>
                                        <th>Payment Method</th>
                                        <th>Passenger Phone</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Update Reason</th>
                                    </tr>

                                    </thead>
                                    <tbody>

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

@section('js')


    <script type="text/javascript">


        $(document).ready(function () {

            var alltrips = $('#alltrips').DataTable({
                processing: true,
                serverSide: true,
                dom: "Blfrtip",
                // scrollX: '100%',
                // autoWidth: true,
                // responsive: true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

                ajax: {
                    url: "{{ url('admin/trips2') }}",
                    method:"POST",
                    data: function (data) {
                        data._token = "{{csrf_token()}}"
                        data.driver = $('#driver_id').val();
                        data.type = $('#type').val();
                        data.from_date = $('#from_date').val();
                        data.to_date = $('#to_date').val();
                        data.account = $('#account').val();
                        data.tab = 'paid';
                    }
                },
                columns: [
                    {data: 'trip_id', name: 'trip_id'},
                    {data: 'cube_pin_status', name: 'cube_pin_status'},
                    {data: 'driver_id', name: 'driver_id',},
                    {data: 'location_from', name: 'location_from'},
                    {data: 'location_to', name: 'location_to'},
                    {data: 'trip_cost', name: 'trip_cost'},
                    {data: 'extra_charges', name: 'extra_charges'},
                    {data: 'total_cost', name: 'total_cost'},
                    {data: 'paid', name: 'paid'},
                    {data: 'payment_method', name: 'payment_method'},
                    {data: 'passenger_phone', name: 'passenger_phone'},
                    {data: 'date', name: 'date'},
                    {data: 'time', name: 'time'},
                    {data: 'reason', name: 'reason'},


                ],
                buttons: [
                    'excel','colvis'
                ],
                language: {

                },
                drawCallback: function() {
                    var table = this.api();

                    table.rows().every(function () {
                        var data = this.data();
                        var paid = parseFloat(data.paid.replace(/,/g, '')) || 0;  // Remove commas and parse as float
                        var tripCost = parseFloat(data.trip_cost.replace(/,/g, '')) || 0; // Same for trip_cost

                        // Debugging the values
                        console.log("Paid:", paid, "Trip Cost:", tripCost);

                        // If paid < trip_cost, hide the row (adjust logic if needed)
                        if (paid < tripCost) {
                            $(this.node()).hide();
                        }
                    });
                }

            });
            $('#driver_id,#account').on('change', function () {
                alltrips.draw();
            });
            $('#type').on('change', function () {
                alltrips.draw();
            });
            $('#from_date,#to_date').on('change', function () {
                alltrips.draw();
            });


        });


    </script>

@endsection

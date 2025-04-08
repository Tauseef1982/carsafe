@extends('admin.layout.yajra')
@section('css')


    <!-- Buttons extension CSS -->

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

                <div class="col-md-2">
                    <label>Driver</label>
                    <select class="form-control" name="driver" id="driver_id">

                        <option value="">All</option>
                        @foreach($drivers as $driver)
                            <option
                                value="{{$driver->driver_id}}">{{$driver->username}}</option>

                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Payment Method</label>
                    <select class="form-control" name="type" id="type">
                        <option value="">All</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="account">Account</option>
                    </select>
                </div>
                <div class="col-md-2">
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
                                        <th>Extra Description</th>
                                        <th>Total Cost</th>
                                        <th>Paid</th>
                                        <th>Payment Method</th>
                                        <th>Passenger Phone</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Complaint</th>
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
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100,1000]],

                ajax: {
                    url: "{{ url('admin/tripswithoutestimatedcost') }}",
                    method:"POST",
                    data: function (data) {
                        data._token = "{{csrf_token()}}"
                        data.driver = $('#driver_id').val();
                        data.type = $('#type').val();
                        data.from_date = $('#from_date').val();
                        data.to_date = $('#to_date').val();
                        data.account = $('#account').val();
                        data.tab = 'all';
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
                    {data: 'extra_description', name: 'extra_description'},
                    {data: 'total_cost', name: 'total_cost'},
                    {data: 'paid', name: 'paid'},
                    {data: 'payment_method', name: 'payment_method'},
                    {data: 'passenger_phone', name: 'passenger_phone'},
                    {data: 'date', name: 'date'},
                    {data: 'time', name: 'time'},
                    {data: 'complaint', name: 'complaint'},
                    {data: 'reason', name: 'reason'},


                ],
                buttons: [
                    'excel','colvis','pdf','print'
                ],
                language: {
                    processing: '<div style="position: fixed; top: 280px; left: 55%; transform: translateX(-50%);">Processing...</div>'
                },

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

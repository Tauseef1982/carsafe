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
                                        <th>Status</th>
                                        <th>Accepted By</th>
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
    <div class="modal fade" id="extraModaledit" tabindex="-1" role="dialog"
         aria-labelledby="extraModalLabeledit"
         aria-hidden="true">
        <div class="modal-dialog" role="document" id="extraModaleditbody">

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
                    url: "{{ url('admin/trips2') }}",
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
                    {data: 'cost', name: 'cost'},
                    {data: 'extra_charges', name: 'extra_charges'},
                    {data: 'extra_description', name: 'extra_description'},
                    {data: 'total_cost', name: 'total_cost'},
                    {data: 'paid', name: 'paid'},
                    {data: 'payment_method', name: 'payment_method',className: 'account-td'},
                    {data: 'passenger_phone', name: 'passenger_phone'},
                    {data: 'date', name: 'date'},
                    {data: 'time', name: 'time'},
                    {data: 'complaint', name: 'complaint'},
                    {data: 'reason', name: 'reason'},
                    {data: 'status', name: 'status'},
                    {data: 'accepted_by', name: 'accepted_by'}


                ],
                buttons: [
                    'excel','colvis','pdf','print'
                ],
                language: {
                      processing: '<div style="position: fixed; top: 280px; left: 55%; transform: translateX(-50%);">Processing...</div>'
                },
                createdRow: function (row, data, dataIndex) {

            $(row).attr('data-trip-id', data.trip_id);
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


            $(document).on('click', '.cost_form_submit_btn', function () {
                const tripId = $(this).data('trip-id');
                const form = $(`form[data-trip-id="${tripId}"]`);
                console.log(`Submitting form for trip ID: ${tripId}`);
                $.ajax({
                    url: '{{url('admin/update-cost')}}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if(response.success == true){
                            alltrips.draw();
                        }else if(response.success == false){
                            alert(response.message);
                        }
                        $('#extraModaledit').modal('hide');
                    },
                    error: function (error) {
                        console.error('Error submitting form for trip ID:', tripId, error);
                    }
                });
            });


            $(document).off('click', '.extra_form_submit_btn').on('click', '.extra_form_submit_btn', function() {

                const tripId = $(this).data('trip-id');

                const form = $(`form[data-trip-id="${tripId}"]`);

                console.log(`Submitting form for trip ID: ${tripId}`);


                $.ajax({
                    url: '{{url('admin/update-charges')}}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {

                        if(response.success == true){

                            alltrips.draw();
                            $('#extraModaledit').modal('hide');

                        }else if(response.success == false){

                            alert(response.message);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form for trip ID:', tripId, error);
                    }
                });
            });


});



        function show_extra_model(element) {
                var modelContent = $(element).attr('data-modelcontent');
                $('#extraModaleditbody').html(modelContent);

            }
           // account and payment method ajax
        $(document).off('click', '.account_form_submit_btn').on('click', '.account_form_submit_btn', function(e) {
            e.preventDefault();

            const button = $(this);
            const tripId = button.data('trip-id');
            button.prop('disabled', true);
            const form = $(`form[data-trip-id="${tripId}"]`);

            console.log(`Submitting form for trip ID: ${tripId}`);


            $.ajax({
                url: '{{url('admin/update-account')}}',
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    console.log('Form submitted successfully for trip ID:', tripId);
                    var tripRow = $('tr[data-trip-id="' + response.trip_id + '"]');
                    tripRow.find('.account-td').text(response.method + " Acc#: " + response.account);



                    $('#extraModaledit').modal('hide');
                },
                error: function(error) {
                    console.error('Error submitting form for trip ID:', tripId, error);
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
        $(document).on('submit', '.account_update_form', function(e) {
            e.preventDefault();
            console.log('Form submission prevented.');
        });



    </script>

@endsection

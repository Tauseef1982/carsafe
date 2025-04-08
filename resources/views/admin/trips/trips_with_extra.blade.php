@extends('admin.layout.yajra')
@section('css')


    <!-- Buttons extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

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
<div id="ajaxLoader" style=" display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="spinner-border text-light" style="margin-left:50%; margin-top:20%" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

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
                                value="{{$driver->driver_id}}">{{$driver->first_name}} {{$driver->last_name}}</option>

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
                                <table class="table table-striped" id="alltrips">
                                    <thead>
                                    <tr>

                                    <th>Action</th>
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

    <div class="modal fade" id="extraModaledit" tabindex="-1" role="dialog"
         aria-labelledby="extraModalLabeledit"
         aria-hidden="true">
        <div class="modal-dialog" role="document" id="extraModaleditbody">

        </div>
    </div>

    <div class="modal fade" id="TripPaymentsModal" tabindex="-1" role="dialog"
         aria-labelledby="TripPaymentsLabeledit"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" id="TripPaymentsModalBody">

        </div>
    </div>
    <div class="modal fade" id="EditTripPaymentsModal" tabindex="-1" role="dialog"
         aria-labelledby="EditTripPaymentsModalLabeledit"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" id="EditTripPaymentsModalBody">

        </div>
    </div>
@endsection

@section('js')



    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.4/js/buttons.colVis.min.js"></script>

    <script type="text/javascript">

        {{--function edit_trip_prices(element) {--}}
        {{--    $('#extraModaleditbody').html('');--}}

        {{--    var trip_id = $(element).attr('data-trip_id');--}}
        {{--    var type = $(element).attr('data-type');--}}
        {{--    $.ajax({--}}
        {{--        url: '{{url('admin/get-update-prices-modal')}}',--}}
        {{--        type: 'GET',--}}
        {{--        data:{trip_id:trip_id,type:type},--}}
        {{--        success: function(response) {--}}

        {{--            $('#extraModaleditbody').html(response);--}}

        {{--        },--}}
        {{--        error: function(error) {--}}

        {{--        }--}}
        {{--    });--}}

        {{--}--}}


        function delete_single_payment(type,id) {

            $.ajax({
                url: '{{url('admin/delete-single-payment')}}',
                type: 'Delete',
                data:{id:id,type:type,_token:"{{csrf_token()}}"},
                success: function(response) {

                    $('#TripPaymentsModal').modal('hide');


                },
                error: function(error) {

                }
            });

        }

        function edit_single_payment(type,id) {
            $('#EditTripPaymentsModalBody').html('');


            $.ajax({
                url: '{{url('admin/get-single-payment-modal')}}',
                type: 'GET',
                data:{id:id,type:type},
                success: function(response) {

                    $('#TripPaymentsModal').modal('hide');
                    $('#EditTripPaymentsModal').modal('show');
                    $('#EditTripPaymentsModalBody').html(response);

                },
                error: function(error) {

                }
            });

        }


        function edit_payments(element) {
            $('#TripPaymentsModalBody').html('');

            var trip_id = $(element).attr('data-trip_id');
            var type = $(element).attr('data-type');
            $.ajax({
                url: '{{url('admin/get-trip-payments')}}',
                type: 'GET',
                data:{trip_id:trip_id,type:type},
                success: function(response) {

                    $('#TripPaymentsModalBody').html(response);

                },
                error: function(error) {

                }
            });

        }



        $(document).ready(function () {

                var alltrips = $('#alltrips').DataTable({
                processing: true,
                serverSide: true,
                dom: "Blfrtip",
                // scrollX: '100%',
                autoWidth: true,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

                ajax: {
                    url: "{{ url('admin/trips_extra') }}",
                    method:"POST",
                    data: function (data) {
                        data._token = "{{csrf_token()}}"
                        data.driver = $('#driver_id').val();
                        data.type = $('#type').val();
                        data.from_date = $('#from_date').val();
                        data.to_date = $('#to_date').val();
                        data.tab = 'all';
                    }
                },
                columns: [
                     {data: 'actions', name: 'actions'},
                    {data: 'trip_id', name: 'trip_id'},
                    {data: 'cube_pin_status', name: 'cube_pin_status'},
                    {data: 'driver_id', name: 'driver_id',},
                    {data: 'location_from', name: 'location_from'},
                    {data: 'location_to', name: 'location_to'},
                    {data: 'cost', name: 'cost'},
                    {data: 'extra_charges', name: 'extra_charges'},
                    {data: 'ExtraDescription', name: 'ExtraDescription',searchable:false},
                    {data: 'total_cost', name: 'total_cost'},
                    // {data: 'trip_cost', name: 'trip_cost'},
                    {data: 'paid', name: 'paid'},
                    {data: 'payment_method', name: 'payment_method'},
                    {data: 'passenger_phone', name: 'passenger_phone'},
                    {data: 'date', name: 'date'},
                    {data: 'time', name: 'time'},
                    {data: 'complaint', name: 'complaint'},
                    {data: 'reason', name: 'reason'},
                    ],
                buttons: [
                    'excel','colvis'
                ],
                language: {
                processing: '<div style="position: fixed; top: 280px; left: 55%; transform: translateX(-50%);">Processing...</div>'
                },

            });
            $('#driver_id').on('change', function () {
                alltrips.draw();
            });
            $('#type').on('change', function () {
                alltrips.draw();
            });
            $('#from_date,#to_date').on('change', function () {
                alltrips.draw();
            });



            $(document).on('click', '.cost_form_submit_btn', function() {

                alert('33');
    const tripId = $(this).data('trip-id');
    const form = $(`form[data-trip-id="${tripId}"]`);
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const button = $(this);

    // Disable the button and show the loader
    button.prop('disabled', true);
    $('#ajaxLoader').fadeIn();

    $.ajax({
        url: '{{url('admin/update-cost')}}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: form.serialize(),
        success: function(response) {
            // Reload the DataTable and hide the modal
            if(response.success == false){

                alltrips.ajax.reload();
            }else if(response.success == false){

                    alert(response.message);
                }

            $('#extraModaledit').modal('hide');
        },
        error: function(xhr, status, error) {
            const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
            alert(errorMessage);
            console.error(`Error ${status}: ${error}`);
        },
        complete: function() {
            // Enable the button and hide the loader
            button.prop('disabled', false);
            $('#ajaxLoader').fadeOut();
        }
    });
});



            //extra charges ajax

            $(document).on('click', '.extra_form_submit_btn', function() {

                const tripId = $(this).data('trip-id');
                const form = $(`form[data-trip-id="${tripId}"]`);
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const button = $(this);

                // Disable button and show loader
                button.prop('disabled', true);
                $('#ajaxLoader').fadeIn();

                $.ajax({
                    url: '{{url('admin/update-charges')}}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: form.serialize(),
                    success: function(response) {

                        if(response.success == true){
                        alltrips.ajax.reload();
                        $('#extraModaledit').modal('hide');
                    }else if(response.success == false){

                    alert(response.message);
                }
                    },
                    error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                        alert(errorMessage);
                        console.error(`Error ${status}: ${error}`);
                    },
                    complete: function() {
                        // Enable button and hide loader
                        button.prop('disabled', false);
                        $('#ajaxLoader').fadeOut();
                    }
                });

            });


        });


    </script>


@endsection

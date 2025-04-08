@php

    $util = new App\Utils\dateUtil();
  $caost = 0;
    $paid_t = 0;
    $cash = 0;
@endphp

<div class="row" id="datefilters">


    <div class="col-md-4">
        <label>From Date</label>
        <input type="date" id="form_date_tab" value="{{$request->from_date}}"

               name="from_date"
               class="form-control">
    </div>
    <div class="col-md-4">
        <label>To Date</label>
        <input type="date" name="to_date" id="to_date_tab" value="{{$request->to_date}}"
               onchange="reload_ajax_trips('{{$request->show_id}}','{{$request->type}}')"
               class="form-control">
    </div>

</div>
<div class="table-responsive">
    <table class="display table table-sm" id="trips_ajax">
        <thead>
        <tr>

            <th>Trip Id</th>
            <th>Pin Status</th>
            <th>From</th>
            <th>Date</th>
            <th>Time</th>
            <th>Account</th>
            <th>Payment Method</th>
            <th>Cost</th>
            <th>Extra</th>
            <th>Extra Description</th>
            <th>Total Cost</th>
            <th>Paid</th>
            <th>Status</th>
            <th>Complaint</th>
            <th>Update Reason</th>
            <th>Accepted By</th>
        </tr>
        </thead>
        <tbody>

        @foreach($trips as $trip)

            <tr data-trip-id="{{ $trip->trip_id }}">

                <td>{{$trip->trip_id}}</td>
                <td>{{$trip->cube_pin}} {{$trip->cube_pin_status}}</td>
                <td>{{$trip->location_from}}<br> To <br> {{$trip->location_to}}</td>
                <td>{{$util->format_date($trip->date)}}</td>
                <td>{{$util->time_format($trip->time)}}</td>
                <td class="account-td">
                    @if($trip->payment_method == 'cash')
                        <a target="_blank"
                           href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                           class="btn-sm btn-primary w-100">Accept Customer Payment</a>
                    @else
                        {{$trip->account_number}}
                        @if (Auth::guard('admin')->user()->role == 'admin')
                        @if (strpos($trip->status, 'Cancelled') !== false || strpos($trip->status, 'canceled') !== false
                        || $trip->payment_method == 'cash')

                        @else
                        <button class="btn account-update-btn" data-bs-toggle="modal"
                                data-original-title="test"
                                data-bs-target="#extraModaledit" onclick="show_extra_model(this)" data-modelcontent='<div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="exampleModalLabel">Update
                        Account and Payment Method</h5>
                    <button class="btn-close" type="button"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <form method="post" class="account_update_form" data-trip-id="{{$trip->trip_id}}">

                    <div class="modal-body">
                        @csrf

                            <input hidden class="form-control mb-3"
                                   value="{{$trip->trip_id}}" name="trip_id"/>

                        <label for="">Please Enter Account Number</label>
                        <input type="number" name="account" required
                                value ="{{$trip->account_number}}"
                               class="form-control mb-3"
                               placeholder="Please Add Account">
                               <label for="">Please Select Payment Method</label>
                                <select name="payment_method" class="form-select">
                                <option value="{{$trip->payment_method}}" selected>{{$trip->payment_method}}</option>
                                 <option value="account">Account</option>
                                 <option value="cash">Cash</option>
                                 <option value="card">Card</option>
                                </select>
                        <label for="">Please Enter Reason</label>
                        <textarea name="reason" id=""
                                  class="form-control" required
                                  placeholder="Enter Here">{{$trip->reason}}</textarea>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button"
                                data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary account_form_submit_btn"
                                type="button" data-trip-id="{{$trip->trip_id}}">Save
                        </button>
                    </div>
                </form>

            </div>'><i class="fa fa-pencil"></i></button>
                    @endif
                    @endif
                    @endif
                </td>
                <td class="method-td">{{$trip->payment_method}}</td>

                <td class="cost-td">
                    {{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}
                    @if (Auth::guard('admin')->user()->role == 'admin')
                        @if (strpos($trip->status, 'Cancelled') !== false || strpos($trip->status, 'canceled') !== false
                        || $trip->payment_method == 'cash')

                        @else
                        <button class="btn cost-update-btn" data-bs-toggle="modal"
                                data-original-title="test"
                                data-bs-target="#extraModaledit" onclick="edit_trip_prices(this)" data-type="cost" data-trip_id="{{$trip->trip_id}}" ><i class="fa fa-pencil"></i></button>
                    @endif
                    @endif

                </td>
                <td class="extra-td">
                    {{number_format($trip->extra_charges, 2, '.', ',')}}
                    @if (Auth::guard('admin')->user()->role == 'admin')
                        @if (strpos($trip->status, 'canceled') !== false || $trip->payment_method == 'cash')

                            @else
                        <button class="btn extra-update-btn" onclick="edit_trip_prices(this)" data-type="extra" data-trip_id="{{$trip->trip_id}}" data-bs-toggle="modal" data-original-title="test" data-bs-target="#extraModaledit"><i class="fa fa-pencil"></i></button>
                    @endif
                    @endif
                </td>
                <td class="description">
                    {!! $trip->ExtraDescription !!}
                </td>
                <td class="cost">{{$trip->trip_cost}}</td>
                <td>@if($trip->is_auto_paid_as_adjustment == 1)
                        <p class="text-success">PAID AUTO <a href="{{url('admin/adjustments')}}"
                                                             target="_blank">view</a></p>
                    @endif{{number_format($trip->total_paid + $trip->total_paid_adjust, 2, '.', ',')}}
                    @if(($trip->total_paid + $trip->total_paid_adjust) > $trip->trip_cost) <p class="text-danger">Over Paid</p>@endif
                </td>
                <td>
                    {{$trip->status}}
                </td>
                <td>{{$trip->complaint}}</td>

                <td>{{$trip->reason}}</td>
                <td>{{$trip->accepted_by}}</td>
            </tr>
        @php


            $caost = $caost + ($trip->trip_cost);
            $paid_t = $paid_t + ($trip->total_paid + $trip->total_paid_adjust);
            if($trip->payment_method == 'cash'){
            $cash = $cash + ($trip->trip_cost);
            }

        @endphp

        @endforeach
        <tbody>
        <tr>

            <th>Trip Id</th>
            <th>Pin Status</th>
            <th>From</th>
            <th>Date</th>
            <th>Time</th>
            <th>Account</th>
            <th>Payment Method</th>
            <th>Cost</th>
            <th>Extra</th>
            <th>Extra Description</th>
            <th>Total Cost = {{$caost}} , cash = {{$cash}}</th>
            <th>Paid = {{$paid_t}}</th>
            <th>Status</th>
            <th>Complaint</th>
            <th>Update Reason</th>
            <th>Accepted By</th>
        </tr>
        </tbody>
    </table>


    <div class="modal fade" id="extraModaledit" tabindex="-1" role="dialog"
         aria-labelledby="extraModalLabeledit"
         aria-hidden="true">
        <div class="modal-dialog" role="document" id="extraModaleditbody">

        </div>
    </div>




</div>

<script>


    function reload_ajax_trips(id, type) {


        var from = $('#form_date_tab').val();
        var to = $('#to_date_tab').val();
        get_trips(type, from, to, "{{$request->driver}}", id);

    }

    function show_extra_model(element) {


        var modelContent = $(element).attr('data-modelcontent');
        $('#extraModaleditbody').html(modelContent);

    }
    {{--function edit_trip_prices(element) {--}}


    {{--    alert('dfsf');--}}
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
</script>
<script>
   $(document).ready(function() {

    $(document).off('click', '.cost_form_submit_btn').on('click', '.cost_form_submit_btn', function(e) {
    e.preventDefault();

    const button = $(this);
    const tripId = button.data('trip-id');
    button.prop('disabled', true);
    const form = $(`form[data-trip-id="${tripId}"]`);


    $.ajax({
        url: '{{url('admin/update-cost')}}',
        type: 'POST',
        data: form.serialize(),
        success: function(response) {

            if(response.success == true) {
                var tripRow = $('tr[data-trip-id="' + response.trip_id + '"]');
                tripRow.find('.cost-td').text(response.updated_cost);
                tripRow.find('.cost').text(response.cost);


            }else if(response.success == false){

                alert(response.message);
            }
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
$(document).on('submit', '.cost_update_form', function(e) {
    e.preventDefault();
    console.log('Form submission prevented.');
});

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
            tripRow.find('.account-td').text(response.account);
            tripRow.find('.method-td').text(response.method);

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



//extra charges ajax

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

                   var tripRow = $('tr[data-trip-id="' + response.trip_id + '"]');
                   tripRow.find('.cost-td').text(response.updated_cost);
                   tripRow.find('.extra-td').text(response.extra);
                  tripRow.find('.cost').text(response.cost);
                  tripRow.find('.description').text(response.description);

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

   $(document).on('submit', '.cost_update_form', function(e) {
       e.preventDefault();
       console.log('Form is being prevented from default submission.');
   });
});


</script>

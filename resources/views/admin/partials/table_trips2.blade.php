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
{{--               onchange="reload_ajax_trips('{{$request->show_id}}','{{$request->type}}')"--}}
               name="from_date"
               class="form-control">
    </div>
    <div class="col-md-4">
        <label>To Date</label>
        <input type="date" name="to_date" id="to_date_tab" value="{{$request->to_date}}"
{{--               onchange="reload_ajax_trips('{{$request->show_id}}','{{$request->type}}')"--}}
               class="form-control">
    </div>
    <div class="col-md-4">

        <button class="btn-sm btn-primary" onclick="reload_ajax_trips('{{$request->account_id}}','{{$request->type}}')">Filter</button>
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
            <th>Discount</th>
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
                <td>
                    @if($trip->payment_method == 'cash')
                        <a target="_blank"
                           href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                           class="btn-sm btn-primary w-100">Accept Customer Payment</a>
                    @else
                        {{$trip->account_number}}
                    @endif
                </td>
                <td>{{$trip->payment_method}}</td>

                <td class="cost-td">
                    {{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}
                    @if (Auth::guard('admin')->user()->role == 'admin')
                        @if (strpos($trip->status, 'Cancelled') !== false && $trip->payment_method === 'account')

                        @else

                        @endif
                    @endif

                </td>
                <td class="extra-td">
                    {{number_format($trip->extra_charges, 2, '.', ',')}}
                    @if (Auth::guard('admin')->user()->role == 'admin')
                        @if (strpos($trip->status, 'Cancelled') !== false && $trip->payment_method === 'account')

                        @else

                        @endif
                    @endif
                </td>
                <td class="description">
                    {!! $trip->ExtraDescription !!}
                </td>
                <td class="cost">{{$trip->discount_amount}}</td>
                <td class="cost">{{$trip->trip_cost - $trip->discount_amount}}</td>
                <td>@if($trip->is_auto_paid_as_adjustment == 1)
                        {{-- <p class="text-success">PAID AUTO <a href="{{url('admin/adjustments')}}"
                                                             target="_blank">view</a></p> --}}
                    @endif{{number_format($trip->total_paid, 2, '.', ',')}}
                    {{-- @if(($trip->total_paid + $trip->total_paid_adjust) > $trip->trip_cost) <p class="text-danger">Over Paid</p>@endif --}}
                </td>
                <td>
                    {{$trip->status}}
                </td>
                <td>{{$trip->complaint}}</td>

                <td>{{$trip->reason}}</td>
                <td>{{$trip->accepted_by}}</td>
            </tr>
        @php


            $caost = $caost + ($trip->trip_cost - $trip->discount_amount);
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

            <th></th>
            <th></th>
            <th>Total Cost = {{$caost}} , cash = {{$cash}}</th>
            <th>Paid = {{$paid_t}}</th>
            <th>Status</th>
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
</script>
<script>
    $(document).ready(function () {

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
                    console.log('Form submitted successfully for trip ID:', tripId);
                    var tripRow = $('tr[data-trip-id="' + response.trip_id + '"]');
                    tripRow.find('.cost-td').text(response.updated_cost);
                    tripRow.find('.cost').text(response.cost);

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

        $(document).on('submit', '.cost_update_form', function (e) {
            e.preventDefault();
            console.log('Form is being prevented from default submission.');
        });

        //extra charges ajax

        $(document).on('click', '.extra_form_submit_btn', function () {

            const tripId = $(this).data('trip-id');


            const form = $(`form[data-trip-id="${tripId}"]`);

            console.log(`Submitting form for trip ID: ${tripId}`);


            $.ajax({
                url: '{{url('admin/update-charges')}}',
                type: 'POST',
                data: form.serialize(),
                success: function (response) {

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
                error: function (error) {
                    console.error('Error submitting form for trip ID:', tripId, error);
                }
            });
        });

        $(document).on('submit', '.cost_update_form', function (e) {
            e.preventDefault();
            console.log('Form is being prevented from default submission.');
        });
    });


</script>

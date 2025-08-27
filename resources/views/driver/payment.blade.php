@php
use Illuminate\Support\Carbon;
use App\Models\Driver;
$util = new \App\Utils\dateUtil();


@endphp
@extends('layout')

@section('css')
<style>
.extracharges-field-div{
 display: none;
  }
  .toggle-extra{
    cursor: pointer;
  }
</style>


@endsection

@section('content')

        <div class="page-title">
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{url('dashboard')}}"><i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="">Accept Payment</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid">
        @if ($errors->has('pin'))
        <div class="alert alert-danger mt-3" role="alert">
            {{ $errors->first('pin') }}
        </div>
    @endif
            <div class="row size-column">
                <div class="col-xl-3 risk-col xl-100 box-col-12">
                    <div class="card total-users">
                        <div class="card-header card-no-border">
                            <h5>Payment</h5>
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            <a href="{{ url('update_price') }}" class="btn btn-primary float-end">Get updated Price</a>
                        </div>
                        <form action="{{url('add-payment')}}" method="post" id="payment_form" onsubmit="disableButton()">
                            @csrf
                            <input hidden name="is_driver" value="1" />
                            <div class="card-body pt-0" id="trip-div">
                                <h5>Select Trip</h5>
                                <div id="trips-container">


                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100" id="show-method-div" hidden type="button">
                                        Skip
                                    </button>
                                </div>

                                {{-- @foreach ($trips as $trip)
                                <div class="card">
                                    <div class="media p-20">
                                        <div class="form-check radio radio-primary me-3">
                                            <input class="form-check-input trip-radio" id="radio{{$trip->id}}" type="radio"
                                                name="trip" value="{{$trip->id}}" data-trip-cost="{{$trip->trip_cost}}"
                                                title="" />
                                            <label class="form-check-label" for="radio{{$trip->id}}">
                                                <div class="media-body">
                                                    <h6 class="mt-0 mega-title-badge">
                                                        {{$trip->location_from}} to {{$trip->location_to}}
                                                        <span
                                                            class="badge badge-primary pull-right digits">${{$trip->trip_cost}}</span>
                                                    </h6>
                                                    <p class="notranslate">
                                                        @php
                                                        $formattedDate = $util->format_date($trip->date);
                                                        $formattedTime = $util->time_format($trip->time);
                                                        @endphp
                                                        Date:{{$formattedDate}} <span
                                                            class="notranslate">{{$formattedTime}}</span>
                                                    </p>


                                                </div>
                                            </label>
                                        </div>

                                    </div>
                                </div>
                                @endforeach --}}



                            </div>
                            <div class="card-body pt-0 hide" id="method-div">
                                <h5>
                                    Payment Method
                                    <span class="badge badge-primary pull-right digits btn" id="show-trip-div">Go Back</span>
                                </h5>

                              @if ($driver->accept_payment_setting == "account")
                               <div class="card" id="account-check">
                                    <div class="media p-20">
                                        <div class="form-check radio radio-primary me-3">
                                            <input class="form-check-input" id="radio19" type="radio" name="payment_method"
                                                   value="account" data-bs-original-title="" title="" />
                                            <label class="form-check-label" for="radio19">
                                                Account
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @elseif($driver->accept_payment_setting == "card")
                                 <div class="card" id="card-check">
                                    <div class="media p-20">
                                        <div class="form-check radio radio-primary me-3">
                                            <input class="form-check-input" id="radio20" type="radio" name="payment_method"
                                                   value="card" data-bs-original-title="" title="" />
                                            <label class="form-check-label" for="radio20">Card</label>
                                        </div>
                                    </div>
                                </div>
                                @elseif($driver->accept_payment_setting == "both")
                                 <div class="card" id="account-check">
                                    <div class="media p-20">
                                        <div class="form-check radio radio-primary me-3">
                                            <input class="form-check-input" id="radio19" type="radio" name="payment_method"
                                                   value="account" data-bs-original-title="" title="" />
                                            <label class="form-check-label" for="radio19">
                                                Account
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                 <div class="card" id="card-check">
                                    <div class="media p-20">
                                        <div class="form-check radio radio-primary me-3">
                                            <input class="form-check-input" id="radio20" type="radio" name="payment_method"
                                                   value="card" data-bs-original-title="" title="" />
                                            <label class="form-check-label" for="radio20">Card</label>
                                        </div>
                                    </div>
                                </div>

                              @endif



                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100" type="button" id="show-amount-div">
                                        Next
                                    </button>
                                </div>
                            </div>


                            <div class="card-body pt-0 hide" id="amount-div">
                                <h5>
                                    Total Amount
                                    <span class="badge badge-primary pull-right digits btn " id="show-method-div-btn-back">Go
                                    Back</span>
                                </h5>
                                <div class="card">
                                    <div class="media p-20">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input class="form-control" type="tel" name="amount" autofocus value=""
                                                   id="amount-field" readonly placeholder="00.00" />
                                        </div>

                                    </div>
                                    @if ($driver->extras_setting == 1)
                                     <span class="toggle-extra" data-target="2">Add Extras </span>
                                     @else
                                     <span class="danger" >Admin has blocked you from adding extra </span>
                                    @endif

                                    <div class="  p-3 extracharges-field-div" data-id="2">
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-stop-4 ">−</button>
                                                <span class="mx-2 fw-bold">$4 Stop</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-stop-4 ">+</button>
                                            </div>

                                            <div class="d-flex align-items-center mb-2">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-stop-5 ">−</button>
                                                <span class="mx-2 fw-bold">$5 Stop</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-stop-5 ">+</button>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-wait">−</button>
                                                <span class="mx-2 fw-bold">Wait Charges</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-wait">+</button>
                                            </div>
                                        </div>



                                        <label for="stop_amount" class="me-2">Stop:</label>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">$</span>
                                            <input class="form-control me-2 stop_amount" type="tel" name="stop_amount[]" readonly id=""
                                                   placeholder="00.00" />

                                        </div>
                                        <!-- <label for="stop_amount" class="me-2">Stop Location:  </label>
                                        <input type="text" class="form-control stop_location"  name="stop_location[]"
                                               placeholder="Please Enter Stop Location Here"> -->
                                        <label for="wait_amount" class="me-2 ">Wait:</label>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">$</span>
                                            <input class="form-control me-2 wait_amount" type="tel" name="wait_amount[]" readonly id=""
                                                   placeholder="00.00" />
                                        </div>


                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100 amount-btn  next-step-btn" type="button" id="next-step-btn">
                                        Next
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-0 hide" id="extra-div">
                                <h5>
                                    Extra Charges
                                    <span class="badge badge-primary pull-right digits btn show-method-div">Go Back</span>
                                </h5>
                                <div class="card">
                                    <div class="medi p-20 mb-3">
                                        <label for="">Trip Price</label>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">$</span>
                                            <input class="form-control price-span" type="tel" name="trip_price" value=""
                                                   id="trip_price" disabled />

                                        </div>


                                        <br>
                                        {{-- <small>Stop, wait & any extra</small> --}}

                                        <span class="span-exto">Total Payable: <span class="price-span"></span>+<span
                                                id="extra-span"></span> =
                                        $<span id="total-span"></span> </span>
                                        <!-- <div class="form-group mt-3 " style="display: none;" id="complaint-field">

                                                          <select class="form-control" id="complaint-select" name="complaint">
                                                              <option value=""> Select Complaint </option>
                                                              <option value="wrong_address">Wrong Address</option>
                                                              <option value="incorrect_fare">Incorrect Fare</option>
                                                          </select>
                                                      </div> -->
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100 next-step-btn set-this-amount-to-amount"
                                            type="button">
                                        Next
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-0 hide" id="gocab-account-div">
                                <h5>
                                    Account
                                    <span class="badge badge-primary pull-right digits btn  show-method-div">Go Back</span>
                                </h5>
                                <div class="card">
                                    <div class="media p-20">
                                        <div class="">
                                            <input class="form-control mb-3" type="tel" name="account" value="" id="acc-field"
                                                   placeholder="Enter account number" />
                                                   <input type="text" class="form-control" id="account_pin_masked" autocomplete="new-password" inputmode="numeric" placeholder="Pin">

                                                <input type="hidden" id="account_pin" name="account_pin">
                                            <!-- <label for="" style="cursor: pointer; text-decoration:underline "
                                                   id="show-extra-field">Add Extra Charges</label><br> -->

                                        </div>

                                    </div>
                                     @if ($driver->extras_setting == 1)
                                     <span class="toggle-extra" data-target="1">Add Extras </span>
                                     @else
                                     <span class="danger" >Admin has blocked you from adding extra </span>
                                    @endif

                                    <div class="  p-3 extracharges-field-div" data-id="1">
                                       <div class="mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-stop-4 stops">−</button>
                                                <span class="mx-2 fw-bold">$4 Stop</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-stop-4 stops">+</button>
                                            </div>

                                            <div class="d-flex align-items-center mb-2">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-stop-5 stops">−</button>
                                                <span class="mx-2 fw-bold">$5 Stop</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-stop-5 stops">+</button>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-primary btn-sm me-2 remove-wait">−</button>
                                                <span class="mx-2 fw-bold">Wait Charges</span>
                                                <button type="button" class="btn btn-secondary btn-sm ms-2 add-wait">+</button>
                                            </div>
                                        </div>
                                        <span class="input-group-text hide">$</span>
                                        <input class="form-control hide" type="tel" name="extra_charges"  autofocus value=""
                                               id="extra_charges" placeholder="00.00 " />

                                        <label for="stop_amount" class="me-2">Stop:</label>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">$</span>
                                            <input class="form-control me-2 stop_amount" type="tel" readonly name="stop_amount[]"
                                                   placeholder="00.00" />

                                        </div>
                                        <!-- <label for="stop_amount" class="me-2">Stop Location:  </label>
                                        <input type="text" class="form-control stop_location"  name="stop_location[]"
                                               placeholder="Please Enter Stop Location Here"> -->
                                        <label for="wait_amount" class="me-2 ">Wait:</label>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">$</span>
                                            <input class="form-control me-2 wait_amount" type="tel" name="wait_amount[]" readonly id="wait_amount"
                                                   placeholder="00.00" />
                                        </div>


                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100" type="submit" id="sb-btn-acc">
                                        Submit
                                    </button>
                                </div>
                            </div>

                            <div class="card-body pt-0 hide" id="card-div">
                                <div id="card-errors" class="text-danger" role="alert"></div>
                                <h5>
                                    Card Details
                                    <span class="badge badge-primary pull-right digits btn show-amount-div">Go Back</span>
                                </h5>
                                <div class="card">

                                    {{-- <div id="card-element" class="p-5">--}}
                                    {{-- <!-- A Stripe Element will be inserted here. -->--}}
                                    {{-- </div>--}}
                                    <div id="card-element-cardnox" style="margin-top: 50px">
                                        <div class="card-js" id="cardnox_inputs" data-capture-name="true"
                                             data-icon-colour="#158CBA"></div>
                                    </div>

                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary btn-block w-100" type="button" id="card-submit-btn">
                                        Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="px-3">
                            <button type="button" class="btn btn-outline-primary  btn-block w-100" id="start-over">
                                Start Over
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->

@endsection
@section('js')
    <script>
        $(document).ready(function () {
            // Function to load the latest trips
            function loadLatestTrips() {
                $.ajax({
                    url: "{{ url('/payment') }}",
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        $('#trips-container').html('<p>Loading trips...</p>');
                    },
                    success: function (response) {
                        console.log(response);
                        let tripsHtml = '';

                        if (response.trips && response.trips.length > 0) {
                            response.trips.forEach(function (trip) {
                                const formattedDate = trip.date ? new Date(trip.date).toLocaleDateString() : 'N/A';
                                const formattedTime = trip.time;
                      tripsHtml += `
                <div class="card">
                    <div class="media p-20">
                        <div class="form-check radio radio-primary me-3">
                            <input class="form-check-input trip-radio" id="radio${trip.trip_id}"
                                   type="radio" name="trip"
                                   value="${trip.trip_id}" data-order_id="${trip.order_id}" data-trip-cost="${parseFloat(trip.trip_cost) + parseFloat(trip.extra_charges ?? 0)}" />
                            <label class="form-check-label" for="radio${trip.trip_id}">
                                <div class="media-body">
                                    <h6 class="mt-0 mega-title-badge">
                                        ${trip.location_from} to ${trip.location_to}
                                        <span class="badge badge-primary pull-right digits">
                                         $${trip.trip_cost}
                                        </span>
                                    </h6>
                                    <p class="notranslate">
                                        Date: ${formattedDate} <span>${formattedTime}</span>
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
                            });
                        } else {
                            tripsHtml = '<p>No trips available.</p>';
                        }

                        $('#trips-container').html(tripsHtml);
                    },

                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        $('#trips-container').html('<p>An error occurred while fetching trips.</p>');
                    }
                });
            }


            loadLatestTrips();
        });
    </script>
    <script>
        $(document).ready(function () {
            $('.toggle-extra').click(function() {
        var targetId = $(this).data('target');
        $('.extracharges-field-div[data-id="' + targetId + '"]').toggle();
    });
   $(document).on('click', '.stops', function (e) {
    e.preventDefault();

    let $btn = $(this);
    let accountId = $('#acc-field').val().trim();

    if (!accountId) {
        alert('Please enter an account number first.');
        return;
    }


    $.ajax({
        url: '/check-account-stops',
        type: 'POST',
        data: {
            account: accountId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.disable_stops) {
                alert('This account  stops are disabled by admin.');
                return;
            }

            // If stops are allowed, run the original logic
            let $container = $btn.closest('.extracharges-field-div');
            let $input = $container.find('.stop_amount').first();
            let current = parseFloat($input.val()) || 0;

            if ($btn.hasClass('add-stop-4')) {
                $input.val((current + 4).toFixed(2));
            }
            else if ($btn.hasClass('remove-stop-4')) {
                if (current >= 4) $input.val((current - 4).toFixed(2));
            }
            else if ($btn.hasClass('add-stop-5')) {
                $input.val((current + 5).toFixed(2));
            }
            else if ($btn.hasClass('remove-stop-5')) {
                if (current >= 5) $input.val((current - 5).toFixed(2));
            }

            updateExtraCharges($container);
        }
    });
});

$(document).on('click', '.add-stop-4, .remove-stop-4, .add-stop-5, .remove-stop-5', function (e) {
    e.preventDefault();

    // Check if payment method is card
    if ($('input[name="payment_method"]:checked').val() !== 'card') {
        alert('Stops can only be added for Card payments.');
        return;
    }

    var $container = $(this).closest('.extracharges-field-div');
    var $input = $container.find('.stop_amount').first();
    var current = parseFloat($input.val()) || 0;

    if ($(this).hasClass('add-stop-4')) {
        $input.val((current + 4).toFixed(2));
    }
    else if ($(this).hasClass('remove-stop-4') && current >= 4) {
        $input.val((current - 4).toFixed(2));
    }
    else if ($(this).hasClass('add-stop-5')) {
        $input.val((current + 5).toFixed(2));
    }
    else if ($(this).hasClass('remove-stop-5') && current >= 5) {
        $input.val((current - 5).toFixed(2));
    }

    updateExtraCharges($container);
});




     let driverWaitCharges = {{ $driver->wait_charges ?? 0.5 }};
    $('.add-wait').click(function (e) {
        e.preventDefault();
        var $container = $(this).closest('.extracharges-field-div');
        var $input = $container.find('.wait_amount').first();
        var current = parseFloat($input.val()) || 0;
        $input.val((current + driverWaitCharges).toFixed(2)); // adjust per unit wait charge if needed
        updateExtraCharges($container);
    });

    // − Wait
    $('.remove-wait').click(function (e) {
        e.preventDefault();
        var $container = $(this).closest('.extracharges-field-div');
        var $input = $container.find('.wait_amount').first();
        var current = parseFloat($input.val()) || 0;
        if (current >= 1) {
            $input.val((current - driverWaitCharges).toFixed(2));
            updateExtraCharges($container);
        }
    });

        function updateExtraCharges($container) {
    var stopAmount = parseFloat($container.find('.stop_amount').first().val()) || 0;
    var waitAmount = parseFloat($container.find('.wait_amount').first().val()) || 0;
    var total = stopAmount + waitAmount;

    $('#extra_charges').val(total.toFixed(2));
}
            function validateStops() {
    let shouldDisable = false;

    $('input[name="stop_amount[]"]').each(function(index) {
        const amountVal = parseFloat($(this).val()) || 0;
        const stopLocation = $('input[name="stop_location[]"]').eq(index).val();

        if (amountVal > 0 && (!stopLocation || stopLocation.trim() === '')) {
            shouldDisable = false;
            return false;
        }
    });


    $('#sb-btn-acc, #next-step-btn').prop('disabled', shouldDisable);
}

// Trigger on input change
$(document).on('input', 'input[name="stop_amount[]"], input[name="stop_location[]"]', validateStops);
            let order_id;
            let selectedTripCost;
            $('#trips-container').on("change", 'input[name="trip"]', function () {

                if ($('input[name="trip"]:checked').length > 0) {

                    $("#show-method-div").click();
                    selectedTripCost = $(this).data('trip-cost');
                     order_id = $(this).data('order_id');
                    //console.log(selectedTripCost);

                    // if(order_id != null) {
                    //     $("#gocab-account-div").show();
                    //     $("#method-div").hide();
                    //     $("#amount-div").hide();
                    //     $("#extra-div").hide();
                    //     $("#acc-field").hide();
                    //     $("#account_pin_masked").hide();
                    //     $("#sb-btn-acc").prop('disabled',false);

                    // }

                }
            });

      $(document).on('change', '#acc-field', function () {
    let accountId = $(this).val().trim();

    // 🚫 Do nothing if empty
    if (!accountId) {
        $('#sb-btn-acc').prop('disabled', false); // optional: reset button state
        return;
    }

    let order_id  = $('input[name="trip"]').data('order_id');

    $.ajax({
        url: '/check-disable-account-payment',
        type: 'POST',
        data: {
            account: accountId,
            order_id: order_id,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.disable_account_payment) {
                $('#sb-btn-acc').prop('disabled', true);
                alert('Account payment method is restricted for this account');
            } else {
                $('#sb-btn-acc').prop('disabled', false);
            }
        }
    });
});






            $('#show-extra-field').on('click', function () {

                $('#extracharges-field-div').toggleClass('d-none');
                $('#extra_charges').focus();
            });



            $("#show-method-div").prop("disabled", false);




            $('input[name="payment_method"]').on("change", function () {
                if ($('input[name="payment_method"]:checked').length > 0) {
                    $("#show-amount-div").click();
                    $("#next-step-btn").prop("disabled", false);
                } else {
                    $("#next-step-btn").prop("disabled", false);
                }
            });

            $("#show-method-div").click(function () {
                if ($('input[name="trip"]:checked').length > 0) {
                    $("#trip-div").hide();
                    $("#method-div").show();
                } else {
                    $("#trip-div").hide();
                    $('#radio20').prop('checked', true);
                    $('#amount-div').show();

                }


            });
            $("#show-trip-div").click(function () {
                $("#trip-div").show();
                $("#method-div").hide();
            });
            $("#show-trip-div").click(function () {
                $("#trip-div").show();
                $("#method-div").hide();
            });

            $(".next-step-btn").click(function () {
                let selectedValue = $('input[name="payment_method"]:checked').val();
                if (selectedValue == 'card') {
                    let totalAmount = $('input[name="amount"]').val();
                    let extraChargesTota = 0;
                     extraChargesTota = $('#extra_charges').val();
                    extraChargesTota = parseFloat(extraChargesTota) || 0;
                    totalAmount = parseFloat(totalAmount) + extraChargesTota;
                    let fee = totalAmount * 0.03;
                    let total = totalAmount + fee + .3;
                    total = parseFloat(total.toFixed(2));

                    $('#card-submit-btn').html('Submit -$' + total)
                    $("#card-div").show();
                    $("#method-div").hide();
                    $("#amount-div").hide();
                    $("#extra-div").hide();

                } else if (selectedValue == 'account') {
                    $("#gocab-account-div").show();
                    $("#method-div").hide();
                    $("#amount-div").hide();
                    $("#extra-div").hide();

                }

            });

            $('.show-method-div').click(function () {
                $("#method-div").show();
                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").hide();
                $("#extra-div").hide();

            })

            $('#show-complaint-div').click(function () {
                $("#complaint-div").show();
                $("#method-div").hide();
                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").hide();
                $("#extra-div").hide();

            })

            $('#complaint-select').change(function () {
                if ($('#complaint-select').val().length > 0) {
                    $("#show-amount-div").click();
                }
            });

            function calculateExtraCharges() {
                let stopAmount = 0;
$('.stop_amount').each(function () {
    let val = parseFloat($(this).val());
    if (!isNaN(val)) {
        stopAmount = val; // or use += val if you want to sum all filled values
        return false;     // stops at the first filled input
    }
});

let waitAmount = 0;
$('.wait_amount').each(function () {
    let val = parseFloat($(this).val());
    if (!isNaN(val)) {
        waitAmount = val;
        return false;
    }
});

let roundtripAmount = 0;
$('.round_trip').each(function () {
    let val = parseFloat($(this).val());
    if (!isNaN(val)) {
        roundtripAmount = val;
        return false;
    }
});

                //round_trip
                console.log('stop = ' + stopAmount + ' wait = ' + waitAmount);

                let total = stopAmount + waitAmount + roundtripAmount;

                $('#extra_charges').val(total.toFixed(2)).trigger('change');
                let extraChargesTotal;

                extraChargesTotal = $('#extra_charges').val();
                extraChargesTotal = parseFloat(extraChargesTotal);
                selectedTripCost = parseFloat(selectedTripCost);
                let totalCharges = selectedTripCost + extraChargesTotal;
               console.log(totalCharges);
                $('.extra-span').html(extraChargesTotal);
                $('.total-span').html(totalCharges);
                $('.span-exto').show();

                $('#sb-btn-acc').html('Submit');
            }

            $('.stop_amount, .wait_amount, .round_trip').on('input', function () {
                calculateExtraCharges();

            });
            $("#show-amount-div").click(function () {
                let selectedValue = $('input[name="payment_method"]:checked').val();
                if (selectedValue == 'card') {
                    $("#method-div").hide();
                    $("#complaint-div").hide();
                    $("#gocab-account-div").hide();
                    $("#card-div").hide();
                    $("#amount-div").show();
                    $('#amount-field').val(selectedTripCost);
                } else if (selectedValue == 'account') {
                    console.log(selectedValue + " its account" + selectedTripCost);
                    $("#method-div").hide();
                    $("#complaint-div").hide();
                    $("#gocab-account-div").show();
                    $("#card-div").hide();
                    if (selectedTripCost == 0 || selectedTripCost == null) {
                        $("#amount-div").show();
                        $('#amount-field').focus();
                        $("#gocab-account-div").hide();
                    } else {
                        $("#extra-div").hide();
                        $('#extra_charges').focus();
                        $('.price-span').html(selectedTripCost);
                        $('#trip_price').val(selectedTripCost);
                        let extraCharges;
                        $('#extra_charges').keyup(function () {
                            extraCharges = $(this).val();
                            extraCharges = parseFloat(extraCharges);
                            selectedTripCost = parseFloat(selectedTripCost);
                            let totalCharges = selectedTripCost + extraCharges;
                            console.log(selectedTripCost );
                            $('#extra-span').html(extraCharges);
                            $('#total-span').html(totalCharges);
                            $('.span-exto').show();

                            $('#sb-btn-acc').html('Submit-$' + totalCharges);

                        });

                    }
                }
            });

            $('.show-amount-div').click(function () {
                $("#method-div").hide();
                $("#complaint-div").hide();

                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").show();
                $('#amount-field').focus();
            })


            $('#show-method-back').click(function () {
                $("#method-div").show();
                $("#complaint-div").hide();
            });

             $('#account-check').click(function () {
            $('#radio19').prop('checked', true);
            $("#show-amount-div").click();
        });
        $('#card-check').click(function () {
          $('#radio20').prop('checked', true);
            $("#show-amount-div").click();


        });


        });


        function toggleButton() {

            var inputVal = $('#acc-field').val();
            if (inputVal === "" || inputVal == 0) {
                $('#sb-btn-acc').prop('disabled', true);
            } else {
                $('#sb-btn-acc').prop('disabled', false);
            }
        }

        $('#acc-field').on('input', function () {
            toggleButton();
        });
        toggleButton();

        function toggleButton2() {

            var inputVal2 = $('#amount-field').val();
            if (inputVal2 === "" || inputVal2 == 0) {
                $('.amount-btn').prop('disabled', false);

            } else {
                $('.amount-btn').prop('disabled', false);
            }
        }

        $('#amount-field').on('input', function () {
            toggleButton2();
        });

        toggleButton2();


        $('.set-this-amount-to-amount').on('click', function () {

            var tripPrice = parseFloat($('#trip_price').val()) || 0;

            $('#amount-field').val(tripPrice.toFixed(2));
        });

        if ($('input[name="trip"]:checked').length > 0) {
            $('#show-method-div-btn-back').click(function () {
                $("#complaint-div").hide();

                $("#method-div").hide();
                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").hide();
                $("#extra-div").hide();
            })
        } else {
            $('#show-method-div-btn-back').click(function () {
                $('#trip-div').show();
                $("#method-div").hide();
                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").hide();
                $("#extra-div").hide();
            })
        }

        function priceValidation() {
            let totalCharges = $('#amount-field').val();
            let accountNumber = $('#acc-field').val();
            if (totalCharges == accountNumber) {
                $('#account-price-validate').removeClass('d-none');
            } else {
                $('#account-price-validate').addClass('d-none');
            }
        }

        $('#acc-field').on('input', function () {
            priceValidation();
        });
        $('#amount-field').on('input', function () {
            $('#account-price-validate').addClass('d-none');
        });

        $('#show-complaint-field').click(function () {
            $('#complaint-field').toggle();
        });


    </script>

    {{--
    <script src="https://js.stripe.com/v3/"></script>--}}


    <script>
        $(document).ready(function () {

            $('#card-submit-btn').on('click', function (event) {

                let selectedValue = $('input[name="payment_method"]:checked').val();

                if (selectedValue == 'card') {

                    event.preventDefault();
                    $('#card-submit-btn').prop('disabled', true);
                   var myCard = $('#cardnox_inputs');

                    let cardNumber = myCard.CardJs('cardNumber');
                    let cardType = myCard.CardJs('cardType');
                    let name = myCard.CardJs('name');
                    let expiryMonth = myCard.CardJs('expiryMonth');
                    let expiryYear = myCard.CardJs('expiryYear');
                    let cvc = myCard.CardJs('cvc');

                    $.ajax({
                        url: "{{route('cardknox-genToken')}}",
                        method: 'POST',
                        data: {
                            _token: "{{csrf_token()}}",
                            cardNumber: cardNumber,
                            cardType: cardType,
                            name: name,
                            expiryMonth: expiryMonth,
                            expiryYear: expiryYear,
                            cvc: cvc,
                        },
                        success: function (response) {

                            if (response.success == true) {
                                var form = $('#payment_form');
                                form.append($('<input type="hidden" name="cardknoxToken">').val(response.token));
                                  console.log(form.serialize());
                                $.ajax({
                                    url: form.attr('action'),
                                    method: 'POST',
                                    data: form.serialize(),

                                    success: function (response2) {

                                        console.log(response2.status + " this is response");
                                        if (response2.status == true) {
                                            window.location.href = "{{url('/success')}}";
                                        } else {

                                            $('#card-errors').text(response2.message || 'Payment failed. Please try again.');
                                            $('#card-submit-btn').prop('disabled', false);
                                        }
                                    },
                                    error: function (error2) {
                                        // Handle the error case
                                        const errorMessage = error2.responseJSON?.message || 'Payment processing error. Please try again.';
                                        $('#card-errors').text(errorMessage);
                                        $('#card-submit-btn').prop('disabled', false);
                                    }
                                });

                            } else {
                                $('#card-submit-btn').prop('disabled', false);
                                alert('Wrong Card Details');
                            }

                        },
                        error: function (error) {

                        }
                    });


                } else {
                    $('#payment_form').submit();
                }

            });
            let realValue = '';

$('#account_pin_masked').on('input', function(e) {
    const input = $(this);
    const entered = input.val();

    if (entered.length > realValue.length) {
        const newChar = entered.charAt(entered.length - 1);
        if (/^[0-9]$/.test(newChar)) {
            realValue += newChar;
        }
    } else {
        realValue = realValue.slice(0, entered.length);
    }

    input.val('*'.repeat(realValue.length));

    // **Important:** Update hidden field immediately
    $('#account_pin').val(realValue);
});

// No need to do anything extra on form submit now


        $('#start-over').click(function () {
            window.location.reload();
            $('#payment_form')[0].reset();
            $("#trip-div").show();
            $("#method-div").hide();
            $("#gocab-account-div").hide();
            $("#card-div").hide();
            $("#amount-div").hide();
            $("#extra-div").hide();
        });
    });
    </script>
    <script>
        function disableButton() {

            document.getElementById('sb-btn-acc').disabled = true;

            document.getElementById('sb-btn-acc').innerText = 'Submitting...';
        }
    </script>
    <script>
        const stopAmountInput = document.getElementsByClassName('.stop_amount');
        const stopLocationInput = document.getElementsByClassName('.stop_location');
        const form = document.getElementById('payment_form');

        //stopAmountInput.addEventListener('input', () => {
         //   const stopAmountValue = parseFloat(stopAmountInput.value);

         //   if (stopAmountValue > 0) {
          //      stopLocationInput.required = true;
         //   } else {
          //      stopLocationInput.required = false;
         //   }
       // });
       document.addEventListener('input', (e) => {
  if (!e.target.classList.contains('stop_amount')) return;

  const row = e.target.closest('.stop-row');
  const stopLocationInput = row?.querySelector('.stop_location');
  if (!stopLocationInput) return;

  const val = parseFloat(e.target.value) || 0;
  stopLocationInput.required = val > 0;
});

        form.addEventListener('submit', (event) => {
            if (stopAmountInput.value && parseFloat(stopAmountInput.value) > 0 && !stopLocationInput.value) {
                alert('Please enter a stop location.');
                event.preventDefault();
            }
        });
    </script>




@endsection

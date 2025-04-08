@php
    use Illuminate\Support\Carbon;
    $util = new \App\Utils\dateUtil();
@endphp
@extends('layout')

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
                </div>
                <form action="{{url('add-payment-new')}}" method="post" id="payment_form" onsubmit="disableButton()">
                    @csrf
                    <input hidden name="is_driver" value="1" />
                    <div class="card-body pt-0" id="trip-div">
                        <h5>Select Trip</h5>

                        @foreach ($trips as $trip)
                                                <div class="card">
                                                    <div class="media p-20">
                                                        <div class="form-check radio radio-primary me-3">
                                                            <input class="form-check-input trip-radio" id="radio{{$trip->id}}" type="radio"
                                                                name="trip" value="{{$trip->id}}" data-trip-cost="{{$trip->trip_cost}}"
                                                                data-accountnumber="{{$trip->account_number}}" title="" />
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
                        @endforeach


                        <div class="text-end mt-3">
                            <button class="btn btn-primary btn-block w-100" id="show-method-div" type="button">
                                Skip
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0 hide" id="method-div">
                        <h5>
                            Payment Method
                            <span class="badge badge-primary pull-right digits btn" id="show-trip-div">Go Back</span>
                        </h5>

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
                                        id="amount-field" placeholder="00.00" />
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            {{-- <button class="btn btn-primary btn-block w-100 amount-btn  next-step-btn" type="button"
                                id="next-step-btn">
                                Next
                            </button> --}}
                            <button class="btn btn-primary btn-block w-100 amount-btn" type="button"
                                id="new-submit-btn">
                                Submit
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


                                <span class="span-exto">Total Payable: <span class="price-span"></span>+<span
                                        id="extra-span"></span> =
                                    $<span id="total-span"></span> </span>

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
                        <h5 class="d-none">
                            Account Number
                            <span class="badge badge-primary pull-right digits btn  show-method-div">Go Back</span>
                        </h5>
                        <div class="card">
                            <div class="media p-20">
                                <div class="">
                                    <input class="form-control mb-3 d-none" type="tel" name="account" value=""
                                        id="acc-field" placeholder="Enter account number" readonly />
                                    <label for="" style="cursor: pointer; text-decoration:underline "
                                        id="show-extra-field">Add Extra Charges</label><br>

                                </div>

                            </div>
                            <div class=" d-none p-3" id="extracharges-field-div">

                                <span class="input-group-text hide">$</span>
                                <input class="form-control hide" type="tel" name="extra_charges" autofocus value=""
                                    id="extra_charges" placeholder="00.00 " />

                                <label for="stop_amount" class="me-2">Stop:</label>
                                <div class="input-group mb-3">

                                    <span class="input-group-text">$</span>
                                    <input class="form-control me-2" type="tel" name="stop_amount" id="stop_amount"
                                        placeholder="00.00" />

                                </div>
                                <label for="stop_amount" class="me-2">Stop Location: <small class="text-danger">Reqierd
                                        Field</small><sup class="text-danger">*</sup> </label>
                                <input type="text" class="form-control" id="stop_location" name="stop_location"
                                    placeholder="Please Enter Stop Location Here">
                                <label for="wait_amount" class="me-2 ">Wait:</label>
                                <div class="input-group mb-3">

                                    <span class="input-group-text">$</span>
                                    <input class="form-control me-2" type="tel" name="wait_amount" id="wait_amount"
                                        placeholder="00.00" />
                                </div>
                                <label for="round_trip" class="me-2 ">Round Trip:</label>
                                <div class="input-group mb-3">

                                    <span class="input-group-text">$</span>
                                    <input class="form-control me-2" type="tel" name="round_trip" id="round_trip"
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

                            <div id="card-element" class="p-5">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>

                        </div>
                        <div class="text-end mt-3">
                            <button class="btn btn-primary btn-block w-100" type="button" id="card-submit-btn">
                                Submit ddddd
                            </button>
                        </div>
                    </div>
                </form>
                <div class="px-3">
                    <button type="button" class="btn btn-outline-primary text-dark btn-block w-100"
                        id="start-over">Start Over</button>
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


        $('#show-extra-field').on('click', function () {

            $('#extracharges-field-div').toggleClass('d-none');
            $('#extra_charges').focus();
        });
        let selectedTripCost;
        $('.trip-radio').change(function () {
            selectedTripCost = $(this).data('trip-cost');
        });

        $("#show-method-div").prop("disabled", false);

        $('input[name="trip"]').on("change", function () {

            if ($('input[name="trip"]:checked').length > 0) {
                    // $('#acc-field').val($(this).data('accountnumber'));
                    // toggleButton();
                 showing_inputs();



            } else {
                $("#show-method-div").prop("disabled", false);
            }
        });



        $('input[name="payment_method"]').on("change", function () {
            if ($('input[name="payment_method"]:checked').length > 0) {
                $("#show-amount-div").click();
            } else {
                $("#next-step-btn").prop("disabled", false);
            }
        });


        function showing_inputs(){


                 var selectedTripCostt = $('input[name="trip"]:checked').data('trip-cost');

                // if (selectedValue == 'card') {
                //     $("#method-div").hide();
                //     $("#complaint-div").hide();
                //     $("#gocab-account-div").hide();
                //     $("#card-div").hide();
                //     $("#amount-div").show();
                //     $('#amount-field').focus();
                // } else if (selectedValue == 'account') {

                    $("#method-div").hide();
                    $("#complaint-div").hide();
                    $("#trip-div").hide();

                    $("#gocab-account-div").show();
                    $("#card-div").hide();
                    if (selectedTripCostt == 0 || selectedTripCostt == null) {


                        $("#amount-div").show();
                        $('#amount-field').focus();
                        $("#gocab-account-div").hide();


                    } else {



                        $("#extra-div").hide();
                        $('#extra_charges').focus();
                        $('.price-span').html(selectedTripCost);
                        $('#trip_price').val(selectedTripCostt);


                        let extraCharges;
                        $('#extra_charges').keyup(function () {
                            extraCharges = $(this).val();
                            extraCharges = parseFloat(extraCharges);
                            selectedTripCostt = parseFloat(selectedTripCostt);
                            let totalCharges = selectedTripCostt + extraCharges;

                            $('#extra-span').html(extraCharges);
                            $('#total-span').html(totalCharges);
                            $('.span-exto').show();

                            $('#sb-btn-acc').html('Submit-$' + totalCharges);

                        });

                    }
                // }



        }

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
                totalAmount = parseFloat(totalAmount)
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
            let stopAmount = parseFloat($('#stop_amount').val()) || 0;
            let waitAmount = parseFloat($('#wait_amount').val()) || 0;
            let roundtripAmount = parseFloat($('#round_trip').val()) || 0;
            //round_trip
            let total = stopAmount + waitAmount + roundtripAmount;

            $('#extra_charges').val(total.toFixed(2)).trigger('change');
            let extraChargesTotal;

            extraChargesTotal = $('#extra_charges').val();
            extraChargesTotal = parseFloat(extraChargesTotal);
            selectedTripCost = parseFloat(selectedTripCost);
            let totalCharges = selectedTripCost + extraChargesTotal;

            $('#extra-span').html(extraChargesTotal);
            $('#total-span').html(totalCharges);
            $('.span-exto').show();

            $('#sb-btn-acc').html('Submit');
        }

        $('#stop_amount, #wait_amount, #round_trip').on('input', function () {
            calculateExtraCharges();
        });
        $("#show-amount-div").click(function () {


            let selectedValue = $('input[name="trip"]:checked').val();

            if (selectedValue == 'card') {
                $("#method-div").hide();
                $("#complaint-div").hide();
                $("#gocab-account-div").hide();
                $("#card-div").hide();
                $("#amount-div").show();
                $('#amount-field').focus();
            } else if (selectedValue == 'account') {

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
        })


    });
    $('#account-check').click(function () {
        $('#radio19').prop('checked', true);
        $("#show-amount-div").click();
    });
    $('#card-check').click(function () {
        $('#radio20').prop('checked', true);
        $("#show-amount-div").click();
    });

    function toggleButton() {

        var inputVal = $('#acc-field').val();
        if (inputVal === "" || inputVal == 0) {
            $('#sb-btn-acc').prop('disabled', false);
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
            $('.amount-btn').prop('disabled', true);

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

<script src="https://js.stripe.com/v3/"></script>


<script>
    $(document).ready(function () {
        // Initialize Stripe with your publishable key
        var stripe = Stripe("{{config('app.STRIPE_KEY')}}");
        var elements = stripe.elements();

        // Create an instance of the card Element
        var card = elements.create('card');
        card.mount('#card-element');

        // Handle form submission

        $('#new-submit-btn').on('click', function (event) {
            $('#payment_form').submit();
        });



    });
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

</script>
<script>
    function disableButton() {

        document.getElementById('sb-btn-acc').disabled = true;

        document.getElementById('sb-btn-acc').innerText = 'Submitting...';
    }
</script>
<script>
    const stopAmountInput = document.getElementById('stop_amount');
    const stopLocationInput = document.getElementById('stop_location');
    const form = document.getElementById('payment_form');

    stopAmountInput.addEventListener('input', () => {
        const stopAmountValue = parseFloat(stopAmountInput.value);

        if (stopAmountValue > 0) {
            stopLocationInput.required = true;
        } else {
            stopLocationInput.required = false;
        }
    });

    form.addEventListener('submit', (event) => {
        if (stopAmountInput.value && parseFloat(stopAmountInput.value) > 0 && !stopLocationInput.value) {
            alert('Please enter a stop location.');
            event.preventDefault();
        }
    });
</script>


@endsection

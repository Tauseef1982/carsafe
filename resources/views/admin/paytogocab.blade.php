@extends('admin.admin-layout')

@section('content')
    @php
    $util = new \App\Utils\dateUtil();
    @endphp
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
            <div class="risk-col xl-100 box-col-12">
                <div class="card total-users">
                    <div class="card-header card-no-border">
                        <h5>Payment</h5>
                    </div>
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="payment_form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="px-3">
                            <input hidden id="to_gocab" name="to_gocab" value="{{$to_gocab}}">
                            <button type="button" class="btn btn-outline-primary text-dark btn-block w-100" onclick="payToDriver(event)">Pay $<span id="total_pay">0</span></button>
                        </div>
                        <div class="card-body pt-0" id="trip-div">
                            <h5>Select Trip</h5>

                            <div class="card">
                                <div class="media p-20">
                                    <div class="form-check radio radio-primary me-3">
                                        @foreach($trips as $index => $trip)
                                            <input class="paycheckbox" type="checkbox" data-amount="{{ number_format($trip->trip_cost, 2) }}" name="trip[]" id="radio_{{$index}}" value="{{ number_format($trip->trip_cost, 2) }}"/>

                                            <input type="hidden" name="trips[{{$index}}][amount]" value="{{ number_format($trip->trip_cost, 2) }}" />
                                            <input type="hidden" name="trips[{{$index}}][trip_id]" value="{{ $trip->trip_id }}" />
                                            <input type="hidden" name="trips[{{$index}}][driver_id]" value="{{ $trip->driver_id }}" />
                                            <input type="hidden" name="trips[{{$index}}][stripe_id]" value="{{ $trip->stripe_id }}" />
                                            <input type="hidden" name="trips[{{$index}}][account_id]" value="{{ $trip->account_number }}" />

                                            <label class="form-check-label" for="radio_{{$index}}">
                                                <div class="media-body">
                                                    <h6 class="mt-0 mega-title-badge">
                                                        {{ $trip->location_from ?? 'Unknown Location' }} to {{ $trip->location_to ?? 'Unknown Location' }}
                                                    </h6>
                                                    <p class="notranslate">
                                                        Date: {{ \Carbon\Carbon::parse($trip->date)->format('m-d-Y') }} <span class="notranslate">{{ \Carbon\Carbon::parse($trip->time)->format('h:i A') }}</span>
                                                        <br><span class="digits">Cost: ${{ number_format($trip->trip_cost, 2) }}</span>
                                                        <br><span class="digits">UnPaid: ${{ number_format($trip->trip_cost - $trip->total_paid, 2) }}</span>
                                                        <br><span class="digits">Paying: ${{ number_format($trip->trip_cost, 2) }}</span>
                                                    </p>
                                                </div>
                                            </label>
                                            <br>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div id="unselected_amount_div" class="alert alert-warning" style="display:none;">
                                Remaining Amount Not Greater Then UnSelected Cost Trips Remaining= {{$to_gocab}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->

@endsection

@section('js')

<script>
    $(document).ready(function () {
        let totalTripsAmount = 0;
        let toGoCab = parseFloat("{{ $to_gocab }}");
        let selectedAmount = 0;

        $('.paycheckbox').each(function () {
            totalTripsAmount += parseFloat($(this).data('amount'));
        });

        $('.paycheckbox').each(function () {
            let tripAmount = parseFloat($(this).data('amount'));
            if (selectedAmount + tripAmount <= toGoCab) {
                $(this).prop('checked', true);
                selectedAmount += tripAmount;
            }
        });

        $('#total_pay').html(selectedAmount.toFixed(2));

        let remainingAmount = toGoCab - selectedAmount;
        let unselectedAmount = totalTripsAmount - selectedAmount;

        if (remainingAmount < unselectedAmount) {
            $('#unselected_amount_div').show();
            $('#unselected_amount').html(unselectedAmount.toFixed(2));
        } else {
            $('#unselected_amount_div').hide();
        }

        $('.paycheckbox').click(function () {
            selectedAmount = 0;

            $('.paycheckbox').each(function () {
                if ($(this).is(':checked')) {
                    selectedAmount += parseFloat($(this).data('amount'));
                }
            });

            unselectedAmount = totalTripsAmount - selectedAmount;
            remainingAmount = toGoCab - selectedAmount;

            $('#total_pay').html(selectedAmount.toFixed(2));


            if (remainingAmount > unselectedAmount) {
                $('#unselected_amount_div').show();
                $('#unselected_amount').html(unselectedAmount.toFixed(2));
            } else {
                $('#unselected_amount_div').hide();
            }
        });
    });

    function payToDriver(event) {
        event.preventDefault();

        // Serialize only the checked checkboxes and their associated hidden fields
        let selectedTrips = [];

        // Find each checkbox that is checked
        $('#payment_form').find('input.paycheckbox:checked').each(function() {
            let tripIndex = $(this).attr('id').split('_')[1];  // Get the index from the id of the checkbox

            // Collect all the hidden inputs related to this checked trip
            let tripData = {
                amount: $('input[name="trips[' + tripIndex + '][amount]"]').val(),
                trip_id: $('input[name="trips[' + tripIndex + '][trip_id]"]').val(),
                driver_id: $('input[name="trips[' + tripIndex + '][driver_id]"]').val(),
                stripe_id: $('input[name="trips[' + tripIndex + '][stripe_id]"]').val(),
                account_id: $('input[name="trips[' + tripIndex + '][account_id]"]').val()
            };

            selectedTrips.push(tripData);
        });

        // Convert the selected trips array to a JSON string to send via AJAX
        let postData = {
            trips: selectedTrips,
            to_gocab: $('#to_gocab').val(),  // Add any other data like the total amount to be paid
            _token: '{{ csrf_token() }}'  // Include CSRF token
        };

        $.ajax({
            url: '{{url("/admin/pay-to-driver-gocab")}}',
            method: 'POST',
            data: postData,
            success: function(response) {
                console.log("Payment successful");
                if (response.status === 'success') {
                    alert(response.message);
                } else if (response.status === 'error') {
                    alert(response.message);
                }
            },
            error: function(error) {
                console.error("Payment failed", error);
                alert("Payment failed".error);
            }
        });
    }

</script>

@endsection

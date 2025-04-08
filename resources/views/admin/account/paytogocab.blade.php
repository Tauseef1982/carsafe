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
                            {{-- {{dd($account_id);}} --}}
                            <input hidden id="main_account_id" name="main_account_id" value="{{$account_id}}">

                            <button type="button" class="btn btn-outline-primary text-dark btn-block w-100" onclick="payToDriver(event)">Pay $<span id="total_pay">0</span></button>
                        </div>
                        <div class="card-body pt-0" id="trip-div">
                            <h5>Select Trip</h5>

                            <div class="card">
                                <div class="media p-20">
                                    <div class="form-check radio radio-primary me-3">
                                        @foreach($trips as $index => $trip)
                                            <input class="paycheckbox" type="checkbox" data-amount="{{ number_format($trip->trip_cost - $trip->total_paid, 2) }}" name="trip[]" id="radio_{{$index}}" value="{{ number_format($trip->trip_cost - $trip->total_paid, 2) }}"/>

                                            <input type="hidden" name="trips[{{$index}}][amount]" value="{{ number_format($trip->trip_cost, 2) }}" />
                                            <input type="hidden" name="trips[{{$index}}][unpaid_amount]" value="{{ number_format($trip->trip_cost - $trip->total_paid, 2) }}" />
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
                                                        {{-- <br><span class="digits">Paying: ${{ number_format($trip->trip_cost - $trip->total_paid, 2) }}</span> --}}
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


    {{-- add_credit_card --}}
<div class="modal fade" id="addCreditCardModal" tabindex="-1" role="dialog" aria-labelledby="addCreditCardModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCreditCardModalLabel">Add Credit Card</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="addCreditCardForm">
            @csrf

            <div class="row">

              <input type="hidden" id="account_id" name="account_id" value="{{$account_id}}">

              <div class="col-12">
                <label for="card_number">Card Number</label>
                <input type="text" class="form-control" name="card_number" id="card_number" required>
              </div>
              <div class="col-6">
                <label for="cvc">CVC</label>
                <input type="number" class="form-control" name="cvc" id="cvc" required>
              </div>
              <div class="col-6">
                <label for="expiry">Expiry (MM/YY)</label>
                <input type="text" class="form-control" name="expiry" id="expiry" required placeholder="MM/YY">
              </div>
              <div class="col-12">
                <label for="card_zip">Card Zip</label>
                <input type="text" class="form-control" name="card_zip" id="card_zip" required>
              </div>
              <div class="col-12">
                <label for="type">Card Type</label>
                <input type="text" class="form-control" name="type" id="type" value="credit" readonly>
              </div>
            </div>

            <button class="btn btn-primary mt-3" type="submit">Save</button>
          </form>
        </div>
      </div>
    </div>
</div>


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

        // $('#total_pay').html(selectedAmount.toFixed(2));
        $('#total_pay').html((selectedAmount > toGoCab ? toGoCab : selectedAmount).toFixed(2));

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

            // $('#total_pay').html(selectedAmount.toFixed(2));
            $('#total_pay').html((selectedAmount > toGoCab ? toGoCab : selectedAmount).toFixed(2));


            if (remainingAmount > unselectedAmount) {
                $('#unselected_amount_div').show();
                $('#unselected_amount').html(unselectedAmount.toFixed(2));
            } else {
                $('#unselected_amount_div').hide();
            }
        });




        $('#addCreditCardForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("admin/add/credit-card") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    //
                    if (response.status === 'success') {
                        toastr.success(response.message);
                    } else if (response.status === 'error') {
                        console.log(response.message);

                        toastr.error(response.message);
                    }
                    $('#addCreditCardModal').modal('hide');
                    $('#addCreditCardForm')[0].reset();
                },
                error: function(xhr) {
                    //
                    toastr.error('Error adding credit card: ' + xhr.responseJSON.message);
                }
            });
        });

    });
    $('#addCreditCardModal').on('shown.bs.modal', function () {
        let expiryInput = $(this).find('#expiry');
        expiryInput.on('input', function(e) {
            let input = e.target.value.replace(/\D/g, '');
            if (input.length >= 2) {
                input = input.substring(0, 2) + '/' + input.substring(2);
            }
            e.target.value = input;
        });
        expiryInput.on('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 3) {
                this.value = this.value.slice(0, -1);
            }
        });
    });

    $('#addCreditCardModal').on('hidden.bs.modal', function () {
        let expiryInput = $(this).find('#expiry');
        expiryInput.off('input');
        expiryInput.off('keydown');
    });

    function payToDriver(event) {
        event.preventDefault();

        // Serialize only the checked checkboxes and their associated hidden fields
        let selectedTrips = [];
        // debugger
        // Find each checkbox that is checked
        $('#payment_form').find('input.paycheckbox:checked').each(function() {
            let tripIndex = $(this).attr('id').split('_')[1];  // Get the index from the id of the checkbox

            // Collect all the hidden inputs related to this checked trip
            let tripData = {
                amount: $('input[name="trips[' + tripIndex + '][amount]"]').val(),
                unpaid_amount: $('input[name="trips[' + tripIndex + '][unpaid_amount]"]').val(),
                trip_id: $('input[name="trips[' + tripIndex + '][trip_id]"]').val(),
                driver_id: $('input[name="trips[' + tripIndex + '][driver_id]"]').val(),
                stripe_id: $('input[name="trips[' + tripIndex + '][stripe_id]"]').val(),
                account_id: $('input[name="trips[' + tripIndex + '][account_id]"]').val()
            };

            selectedTrips.push(tripData);
        });
        console.log(selectedTrips);
        selectedTrips.sort(function(a, b) {
            return a.unpaid_amount - b.unpaid_amount;
        });
        console.log("selectedTrips");

        console.log(selectedTrips);

        // Convert the selected trips array to a JSON string to send via AJAX
        let postData = {
            trips: selectedTrips,
            to_gocab: $('#to_gocab').val(),  // Add any other data like the total amount to be paid
            account_id: $('#main_account_id').val(),
            _token: '{{ csrf_token() }}'  // Include CSRF token
        };

        $.ajax({
            url: '{{url("/admin/pay-account-to-gocab")}}',
            method: 'POST',
            data: postData,
            success: function(response) {
                // console.log("Payment successful");
                if (response.status === 'success') {
                    toastr.success(response.message);
                    {{--window.location.href = `{{ url('admin/show/account') }}/{{ $id_of_account }}`;--}}

                } else if (response.status === 'error') {
                    console.log(response.message);

                    toastr.error(response.message);
                    {{--window.location.href = `{{ url('admin/show/account') }}/{{ $id_of_account }}`;--}}

                } else if (response.status === 'card') {
                    toastr.info('Please add a credit card to this account to proceed.');

                    $('#addCreditCardModal').modal('show');


                }
            },
            error: function(error) {
                console.error("Payment failed", error);
                toastr.error("Payment failed".error);
                {{--window.location.href = `{{ url('admin/show/account') }}/{{ $id_of_account }}`;--}}

            }
        });
    }

</script>

@endsection

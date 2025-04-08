@php
    use Illuminate\Support\Carbon;
@endphp

@extends('layout')

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <span  style="display:none;">{{now()->toDateTimeString()}}</span>
                
            </div>
            <div class="col-6">

                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/"><i data-feather="home"></i></a></li>

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
                        <h5>Summary</h5>
                        <div class="card-header-right">
                            <form id="filterForm">
                                @csrf
                                <div class="row">

                                    <div class="col">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="from_date"
                                               class="form-control digits date-field notranslate" id="from_date">
                                    </div>
                                    <div class="col">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="to_date"
                                               class="form-control digits date-field notranslate" id="to_date"
                                               value="{{ now('America/New_York')->toDateString() }}">
                                    </div>
                                </div>
                            </form>


                        </div>
                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-primary card p-10">
                                    <h5 class=" text-center font-dark">Total Trips</h5>
                                    <h6 class=" text-center font-dark" id="total_trips">Loading....</h6>
                                    
                                </div>

                            </div>
                            {{-- <div class="col-md-3">
                                <div class="bg-info card p-10">
                                  <h5 class="font-dark text-center">Total Earnings</h5>
                                  <h6 class="font-dark text-center">${{$total_earnings}} </h6>
                                  <p class="text-end font-dark show-details">Show Details</p>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="bg-success card p-10">
                                  <h5 class="font-dark text-center">Total Recived</h5>
                                  <h6 class="font-dark text-center">${{$total_recived}}</h6>
                                  <p class="text-end font-dark show-details">Show Details</p>
                                </div>
                              </div> --}}
                            <!-- <div class="col-md-6">
                                <div class="bg-primary card p-10">
                                    <h5 class=" text-center font-dark">Today GoCab Balance</h5>
                                    <h6 class=" text-center font-dark" id="gocab_paidd">Loading....</h6>
                                    
                                </div>
                            </div> -->
                            <div class="col-md-6">
                                <div class="bg-secondary card p-10">
                                    <h5 class=" text-center font-white">Total Last Week</h5>
                                    <h6 class=" text-center font-white" id="lastWeek">Loading....</h6>
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-success card p-10">
                                    <h5 class=" text-center font-dark">Total This Week</h5>
                                    <h6 class=" text-center font-dark" id="currentWeek">Loading....</h6>
                                    
                                </div>
                            </div>
                            <div class="col-md-12 d-none">
                                <div class="bg-dark card p-10">
                                    <h5 class=" text-center ">Total CarSafe Balance</h5>
                                    <h6 class=" text-center" id="total_balance">Loading....</h6>

                                </div>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="card details-table" style="display: none;">
                    <div class="card-header text-end">
                        <button class="btn details-hide">X</button>
                    </div>
                    <div class="card-body">
                        <div class=" ">
{{--                            <ul class="display" id="basic-ul">--}}

{{--                                @foreach ($trips as $trip)--}}

{{--                                    <li>--}}
{{--                                        <a href="{{url('single-trip')}}/{{$trip->id}}">--}}
{{--                                            @php--}}
{{--                                                $formattedDate = Carbon::parse($trip->date)->format('m/d/Y');--}}
{{--                                            @endphp--}}
{{--                                            <span style="display: block;"--}}
{{--                                                  class="notranslate">Date:{{$formattedDate}} </span>--}}
{{--                                            <span style="display: block;">Price:{{$trip->trip_cost}} </span>--}}
{{--                                            <span style="display: block;">From:{{$trip->location_from}} </span>--}}

{{--                                        </a>--}}
{{--                                    </li>--}}

{{--                                    @endforeach--}}
{{--                            </ul>--}}

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')

    <script>
        $(document).ready(function () {


            $('.show-details').click(function () {

                $('.details-table').show();
            })
            $('.details-hide').click(function () {

                $('.details-table').hide();
            })
        })
    </script>
    <script>
        $(document).ready(function () {
            function sendAjaxRequest() {
                var fromDate = $('#from_date').val();
                var toDate = $('#to_date').val();

                $.ajax({
                    url: "{{ url('dashboard') }}",
                    method: "GET",
                    data: {
                        from_date: fromDate,
                        to_date: toDate,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {

                        $('#total_trips').text(response.total_trip);
                        // $('.bg-info h6').text('$' + response.total_earnings);
                        // $('.bg-success h6').text('$' + response.total_recived);
                        $('#gocab_paidd').text('$' + Math.round(response.gocab_paid));
                        $('#lastWeek').text('$' + Math.round(response.lastw));
                        $('#currentWeek').text('$' + Math.round(response.currentw));
                        $('#total_balance').text('$' + Math.round(response.gocab_total));


                        var tripsTableBody = $('#basic-ul ');
                        tripsTableBody.empty();

                        $.each(response.trips, function (index, trip) {
                            const date = new Date(trip.date);


                            const formattedDate = (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
                                date.getDate().toString().padStart(2, '0') + '/' +
                                date.getFullYear();
                            tripsTableBody.append(
                                '<li>' +
                                '<a href="{{ url("single-trip") }}/' + trip.id + '">' +
                                '<span style="display: block;" class="notranslate">Date: ' + formattedDate + '</span>' +
                                '<span style="display: block;">Cost: $' + trip.trip_cost + '</span>' +
                                '<span style="display: block;" >From: ' + trip.location_from + '</span>' +
                                '<span style="display: block;" >Payment Method: ' + trip.payment_method + '</span>' +

                                '</a>' +
                                '</li>'
                            );
                        });

                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }


            $('#from_date, #to_date').on('change', function () {
                sendAjaxRequest();
            });


            var fromDateField = $('#from_date');
            var toDateField = $('#to_date');


            const options = { timeZone: 'America/New_York', year: 'numeric', month: '2-digit', day: '2-digit' };
const formatter = new Intl.DateTimeFormat('en-US', options);

// Format the date in New York time zone
const parts = formatter.formatToParts(new Date());
const today = `${parts[4].value}-${parts[0].value.padStart(2, '0')}-${parts[2].value.padStart(2, '0')}`;

// Set the fields
toDateField.val(today);
fromDateField.val(today);


            sendAjaxRequest();

        });

    </script>
    <!-- <script>
        $(document).ready(function(){
            $('#datepicker').datepicker({
                format: 'mm/dd/yyyy',  // Set the desired date format
                autoclose: true,       // Close the datepicker automatically after selection
                todayHighlight: true,  // Highlight today's date
            });
        });
    </script> -->


@endsection

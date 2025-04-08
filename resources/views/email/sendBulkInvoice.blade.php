<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">

    <link rel="icon" href="{{asset('assets/images/logo/carsafe-logo.webp')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/carsafe-logo.webp')}}" type="image/x-icon">
    <title>CarSafe</title>

    <!-- Google fonts and Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Rubik|Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}">
    <link id="color" rel="stylesheet" href="{{asset('assets/css/color-1.css')}}" media="screen">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/responsive.css')}}">

    <style>
        /* Custom styles */
        .skiptranslate > span { display: none !important; }
        .skiptranslate { color: transparent !important; }
        .VIpgJd-ZVi9od-ORHb-OEVmcd, .goog-logo-link { display: none !important; }
        .goog-te-gadget .goog-te-combo { margin: 0px 0 !important; }
        @media print { .no-print { display: none; } body { font-size: 12pt; margin: 0; padding: 0; } }
    </style>

    @php
        // Initialize variables for later use
        $utils = new \App\Utils\dateUtil();
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $account = $data['account'];

        $due_date = $utils->format_date(now()->addDays(3)->toDateString());
        $payment_status = 'Unpaid';
        $imagePath = "";
    @endphp
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice">
                            <div class="row">
                                <!-- Invoice Header with Date and Payment Status -->
                                <div class="col-sm-3" style="display:inline-block;width:33%;">
                                    <img style="width:150px" src="https://carsafe.wiedco.app/assets/images/logo/carsafe-logo.webp" alt="">
                                    <p>
                                        Issued: {{ now()->format('M d, Y') }}<br>
                                        Payment Due: <span id="due_date_span">{{ $due_date }}</span><br>

                                    </p>
                                </div>
                                <div class="col-sm-3 text-md-end" style="display:inline-block; width:33%;">
                                    <img id="img_paid" style="width:100px;" src="{{ $imagePath }}" alt="">
                                </div>
                                <div class="col-sm-6 text-md-end text-xs-center" style="display:inline-block;width:33%;">
                                    <h3 style="float:right">Invoice #<span>{{ $account->account_id }}{{ now()->format('md') }}</span></h3>
                                </div>
                            </div>
                            <hr>
                            <!-- User and Description Section -->
                            <div class="row">
                                <div class="col-md-4" style="width:50%; display:inline-block; float:left;">
                                    <div class="media">
{{--                                        <img class="media-object rounded-circle img-60" src="../assets/images/user/1.jpg" alt="">--}}
                                        <div class="media-body m-l-20">
                                            <h4 class="media-heading">{{ $account->f_name }}</h4>
                                            <p>{{ $account->account_id }}<br>{{ $account->phone }}<br>{!! nl2br(e($account->address)) !!}<br>{{ $account->company_name }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end" style="width:50%; display:inline-block; float:right;" id="project">
                                    <h4>Description</h4>
                                    <p>Here is your invoice for trips from {{ \Carbon\Carbon::parse($from_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($to_date)->format('F d, Y') }}.</p>
                                </div>
                                <div style="clear:both"></div>
                            </div>
                            </div>

                            <!-- Trips Table -->
                            <div class="table-responsive invoice-table" style="width:100%; display:block;" id="table1">
                                <table class="table table-bordered table-striped" width="100%">
                                    <tr>
                                        <td><h4>Trip Id</h4></td>
                                        <td><h4>From</h4></td>
                                        <td><h4>To</h4></td>
                                        <td><h4>Date Time</h4></td>
                                        <td><h4>Extra Charges</h4></td>
                                        <td><h4>Total Cost</h4></td>
                                        <td><h4>Paid</h4></td>
                                    </tr>
                                    @php
                                        $paidd = 0;
                                        $total_trips = 0;
                                        $cost = 0;
                                           $trips_to_be_paid = $account->trips->filter(function ($trip) use ($from_date, $to_date) {
                                        return $trip->payment_method === 'account' &&
                                            strpos($trip->status, 'Cancelled') === false &&
                                            strpos($trip->status, 'canceled') === false &&
                                            $trip->is_delete == 0 &&
                                            $trip->date >= $from_date &&
                                            $trip->date <= $to_date;
                                    });
                                    @endphp

                                    @foreach($trips_to_be_paid as $trip)
                                        <tr>
                                            <td>{{ $trip->trip_id }}</td>
                                            <td>{{ str_replace([', Nueva York, EE. UU.', ', NY, USA'], '', $trip->location_from ?: 'Flag Down') }}</td>
                                            <td>{{ str_replace([', Nueva York, EE. UU.', ', NY, USA'], '', $trip->location_to) }}</td>
                                            <td>{{ $utils->format_date($trip->date) }} {{ $utils->time_format($trip->time) }}</td>
                                            <td>
                                                @if($trip->extra_stop_amount > 0)
                                                    Extra Stop: ${{ $trip->extra_stop_amount }} ({{ $trip->stop_location }})
                                                @elseif($trip->extra_wait_amount > 0)
                                                    Extra Wait: ${{ $trip->extra_wait_amount }}
                                                @elseif($trip->extra_round_trip > 0)
                                                    Extra Round Trip: ${{ $trip->extra_round_trip }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>${{ $trip->TotalCostDiscounted }}</td>
                                            <td>${{ $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount') }}</td>
                                        </tr>
                                        @php
                                            $paidd += $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                                            $total_trips++;
                                            $cost += $trip->TotalCostDiscounted;
                                        @endphp
                                    @endforeach
                                </table>
                            </div>
                            <hr>
                            <!-- Summary Table -->
                            <div class="table-responsive invoice-table"  id="table">
                                <table class="table table-bordered table-striped"style="width:100%">
                                    <tr>
                                        <td>Total Trips</td>
                                        <td>Unpaid</td>
                                        <td>Paid</td>
                                        <td>Total</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $total_trips }}</td>
                                        <td>${{ $cost - $paidd }}</td>
                                        <td>${{ $paidd }}</td>
                                        <td>${{ $cost }}</td>
                                    </tr>
                                </table>
                            </div>

                            @php
                                if ($paidd == $cost) {
                                    $due_date = $utils->format_date(now()->toDateString());
                                    $payment_status = 'Paid';
                                    $imagePath = asset('assets/images/paid.jpg');
                                }
                            @endphp
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/config.js')}}"></script>

    <script>
        document.getElementById('due_date_span').textContent = "{{ $due_date }}";
        document.getElementById('payment_status_span').textContent = "{{ $payment_status }}";
        document.getElementById('img_paid').src = "{{ $imagePath }}";
    </script>
</body>
</html>

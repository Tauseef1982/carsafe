<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{asset('assets/images/logo/gocab-logo.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/gocab-logo.png')}}" type="image/x-icon">
    <title>GoCab -Driver Dashboard </title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
        rel="stylesheet">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/bootstrap.css')}}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}">
    <link id="color" rel="stylesheet" href="{{asset('assets/css/color-1.css')}}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/responsive.css')}}">
    <link href="{{asset('assets/css/card-js.min.css')}}" rel="stylesheet" type="text/css" />
    <script type="text/javascript"
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google_translate_element');
        }
    </script>
    <style>
        .skiptranslate>span {
            display: none !important;
        }

        .skiptranslate {
            color: transparent !important;
        }

        .VIpgJd-ZVi9od-ORHb-OEVmcd {
            display: none !important;
        }

        .goog-logo-link {
            display: none !important;
        }

        .goog-te-gadget .goog-te-combo {
            margin: 0px 0 !important;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 12pt;
            }

        }
    </style>

    @php


        $utils = new \App\Utils\dateUtil();

    @endphp
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice">
                            <div>
                                <div>
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="media">
                                                <div class="media-left"><img class="media-object" style="width:175px;"
                                                        src="{{asset('assets/images/logo/gocab-logo.png')}}" alt="">
                                                </div>
                                                <div class="media-body m-l-20 text-right">


                                                </div>
                                            </div>
                                            @php
                                                $due_date = $utils->format_date($invoice->due_date);
                                                $payment_status = $invoice->status;
                                                $imagePath = "";
                                                $totalDiscount = 0;
                                            @endphp

                                            <p>
                                                Issued: {{ \Carbon\Carbon::parse($invoice->created_at)->format('M') }}
                                                <span>
                                                    {{ \Carbon\Carbon::parse($invoice->created_at)->format('d, Y') }}</span><br>
                                                Payment Due: <span id="due_date_span">{{$due_date}}</span> <br>

                                                Status: <span id="payment_status_span">{{$invoice->status}}</span>
                                            </p>

                                            <!-- End Info-->
                                        </div>
                                        <div class="col-sm-3 text-md-end">

                                            <img id="img_paid" style="width:100px; " alt="">


                                        </div>
                                        <div class="col-sm-6">

                                            <div class="text-md-end text-xs-center">

                                                <h5>Invoice #<span>{{$invoice->ref_no}}</span></h5>


                                            </div>
                                            <!-- End Title-->
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <!-- End InvoiceTop-->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="media">

                                            <div class="media-body m-l-20">
                                                <h5 class="media-heading">{{ ucfirst($account->f_name) }}</h5>

                                                <span>{{ $account->phone }}</span><br>
                                                <span>{!! nl2br(e($account->address)) !!}</span><br>
                                                @if ($account->company_name != "")
                                                    <span>Company: {{ $account->company_name }}</span>
                                                @endif
                                                <p>Account Number: <span>{{ $account->account_id }}</span><br></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-md-end" id="project">
                                            <h6>Description</h6>
                                            <p>Here is your invoice for the trips you enjoyed with Gocab, covering the
                                                period
                                                from
                                                {{ \Carbon\Carbon::parse($invoice->invoice_from_date)->format('F d, Y') }}
                                                ,
                                                to
                                                {{ \Carbon\Carbon::parse($invoice->invoice_to_date)->format('F d, Y') }}
                                                .</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Invoice Mid-->
                                <div>
                                    <div class="table-responsive invoice-table" id="table1">
                                        <table class="table-sm table-bordered table-striped">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <b>Trip Id</b>
                                                    </td>
                                                    <td>
                                                        <b> Pin</b>
                                                    </td>

                                                    <td>
                                                        <b> From</b>
                                                    </td>
                                                    <td>
                                                        <b>To</b>
                                                    </td>
                                                    <td>
                                                        <b>Date Time</b>
                                                    </td>
                                                    <td>
                                                        <b>Stop,Wait,Round</b>
                                                    </td>
                                                    <td>
                                                        <b>Fare</b>
                                                    </td>
                                                    <td>
                                                        <b>Total Cost</b>
                                                    </td>


                                                </tr>
                                                @php
                                                    $paidd = 0;
                                                    $total_trips = 0;
                                                    $cost = 0;

                                                @endphp

                                                @foreach($trips as $trip)
                                                                                                <tr>
                                                                                                    <td>

                                                                                                        <p class="m-0">{{$trip->trip_id}}</p>
                                                                                                    </td>

                                                                                                    <td>{{$trip->cube_pin}} {{$trip->cube_pin_status}}</td>

                                                                                                    <td>
                                                                                                        @php

                                                                                                            $termsToRemove = [', Nueva York, EE. UU.', ', NY, USA'];


                                                                                                            $cleanedLocationFrom = str_replace($termsToRemove, '', $trip->location_from);
                                                                                                            $cleanedLocationTo = str_replace($termsToRemove, '', $trip->location_to);
                                                                                                        @endphp
                                                                                                        @php
                                                                                                            $single_p = $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                                                                                                            $paidd = $paidd + $single_p;
                                                                                                            $total_trips++;
                                                                                                            $cost += $trip->TotalCostDiscounted;
                                                                                                            $totalDiscount += $trip->discount_amount;
                                                                                                            $paid_fee = $paidd * 0.0375;
                                                                                                            $cost_fee = $cost * 0.0375;
                                                                                                        @endphp
                                                                                                        <p class="itemtext">{{$cleanedLocationFrom ?: 'Flag Down'}}</p>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <p class="itemtext">{{$cleanedLocationTo}}</p>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <p class="itemtext">{{$utils->format_date($trip->date)}}
                                                                                                            {{$utils->time_format($trip->time)}}</p>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <p class="itemtext">
                                                                                                            ${{number_format((float) $trip->extra_charges, 2)}}

                                                                                                        </p>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <p class="itemtext">
                                                                                                            ${{number_format($trip->trip_cost - (float) $trip->extra_charges, 2)}}
                                                                                                        </p>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <p class="itemtext">
                                                                                                            ${{ number_format($trip->TotalCostDiscounted, 2)}}</p>
                                                                                                    </td>


                                                                                                </tr>

                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-responsive invoice-table" id="table">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                                <tr>
                                                    <td class="item">
                                                        <h6 class="p-2 mb-0">Total Trips</h6>
                                                    </td>
                                                    @if ($totalDiscount > 0)
                                                        <td class="item">
                                                            <h6 class="p-2 mb-0">Total Discount</h6>
                                                        </td>
                                                    @endif

                                                    <td class="Rate">
                                                        <h6 class="p-2 mb-0">Unpaid</h6>
                                                    </td>
                                                    <td class="Rate">
                                                        <h6 class="p-2 mb-0">Paid(Inc Fee)</h6>
                                                    </td>
                                                    <td class="subtotal">
                                                        <h6 class="p-2 mb-0">Total</h6>
                                                    </td>
                                                    <td class="subtotal">
                                                        <h6 class="p-2 mb-0">Fee</h6>
                                                    </td>
                                                    <td class="subtotal">
                                                        <h6 class="p-2 mb-0">Grand Total</h6>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>

                                                        <p class="m-0">{{$total_trips}}</p>
                                                    </td>
                                                    @if ($totalDiscount > 0)
                                                        <td>
                                                            <p class="m-0">{{$totalDiscount}}</p>
                                                        </td>
                                                    @endif

                                                    <td>
                                                        <p class="itemtext">
                                                            ${{number_format($cost - $paidd, 2, '.', '')}}</p>
                                                    </td>

                                                    <td>
                                                        <p class="itemtext">
                                                            ${{number_format($paidd + $paid_fee, 2, '.', '')}}</p>
                                                    </td>
                                                    <td>
                                                        <p class="itemtext">${{number_format($cost, 2, '.', '')}}</p>
                                                    </td>
                                                    <td>
                                                        <p class="itemtext">${{number_format($cost_fee, 2, '.', '')}}
                                                        </p>
                                                    </td>
                                                    <td>
                                                        <p class="itemtext">
                                                            ${{number_format($cost + $cost_fee, 2, '.', '')}}</p>
                                                    </td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- End Table-->
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div>
                                                <p class="legal"><strong>Thank you for your business!</strong> 

                                                    @if($paidd < $cost)
                                                        <div class="row no-print">
                                                            <div class="col-md-3">
                                                            </div>
                                                            <div class="col-md-6">

                                                                <form method="post"
                                                                    action="{{url('account-invoice')}}/{{$invoice->hash_id}}">
                                                                    @csrf

                                                                    <div class="row">
                                                                        <h6>Want To Save Payment Method For Future Payments</h6>
                                                                        <div class="col-6">
                                                                            <label for="payment_type">Yes</label>
                                                                            <input class="savecardd" type="radio"
                                                                                name="save_card" value="1" checked>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label for="payment_type">No Only This Time</label>
                                                                            <input class="savecardd" type="radio"
                                                                                name="save_card" value="0">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">

                                                                        <div class="col-6">
                                                                            <label for="payment_type">Card</label>
                                                                            <input class="paywith" type="radio"
                                                                                name="payment_type" value="card" checked>
                                                                        </div>
                                                                        <!-- <div class="col-6">
                                                                            <label for="payment_type">ACH Account</label>
                                                                            <input class="paywith" type="radio" name="payment_type"
                                                                                   value="ach">
                                                                        </div> -->
                                                                    </div>
                                                                    <div class="row">

                                                                        <div id="ach_cardnox_inputs" style="display:none">
                                                                            <div>
                                                                                <input class="form-control mb-3"
                                                                                    name="ach_account_name"
                                                                                    placeholder="Account Name">
                                                                                <input class="form-control mb-3"
                                                                                    name="ach_account_number"
                                                                                    placeholder="Account Number">
                                                                                <input class="form-control mb-3"
                                                                                    name="routing_number"
                                                                                    placeholder="Routing Number">

                                                                            </div>
                                                                        </div>

                                                                        <div class="card-js" id="cardnox_inputs">
                                                                            <div class="card-js">
                                                                                <input class="card-number my-custom-class"
                                                                                    name="card_number" required>
                                                                                <input class="expiry-month" name="expiry-month"
                                                                                    required>
                                                                                <input class="expiry-year" name="expiry-year"
                                                                                    required>
                                                                                <input class="cvc" name="cvc" required>
                                                                            </div>
                                                                        </div>
                                                                        <!-- <div class="col-12">
                                                                            <label for="card_number">Card Number</label>
                                                                            <input type="text" class="form-control"
                                                                                   value=""
                                                                                   name="card_number" id="card_number">
                                                                        </div> -->
                                                                        <!-- <div class="col-6">
                                                                            <label for="cvc">CVC</label>
                                                                            <input type="number" class="form-control"
                                                                                   value=""
                                                                                   name="cvc" id="cvc">
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label for="expiry">Expiry (MM/YY)</label>
                                                                            <input type="text" class="form-control"
                                                                                   value=""
                                                                                   name="expiry" id="expiry"
                                                                                   placeholder="MM/YY">
                                                                        </div> -->


                                                                    </div>


                                                                    <button class="btn btn-success mt-3" type="submit">
                                                                        Update/Charge Card
                                                                    </button>
                                                                </form>

                                                            </div>
                                                            <div class="col-md-3">
                                                            </div>
                                                        </div>

                                                    @endif
                                            </div>
                                            @php
                                                if ($paidd == $cost) {
                                                    $due_date = $utils->format_date(now()->toDateString());
                                                    $payment_status = 'Paid';
                                                    $imagePath = asset('assets/images/paid.jpg');
                                                }
                                            @endphp
                                            <!-- End InvoiceBot-->
                                        </div>
                                        <div class="col-sm-12 text-center mt-3 no-print">

                                            <button class="btn btn btn-primary me-2" type="button"
                                                onclick="downloadInvoice()">
                                                Download
                                            </button>
                                        </div>
                                        <!-- End Invoice-->
                                        <!-- End Invoice Holder-->
                                        <!-- Container-fluid Ends-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <!-- latest jquery-->
        <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
        <!-- Bootstrap js-->
        <script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>

        <script src="{{asset('assets/js/scrollbar/simplebar.js')}}"></script>
        <script src="{{asset('assets/js/scrollbar/custom.js')}}"></script>
        <!-- Sidebar jquery-->
        <script src="{{asset('assets/js/config.js')}}"></script>
        <!-- Plugins JS start-->


        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        <script src="{{asset('assets/js/script.js')}}"></script>
        <script>
            function downloadInvoice() {
                window.print();

            }

        </script>
        <script src="{{asset('assets/js/card-js.min.js')}}"></script>
        <script>

            $(document).ready(function () {

                var myCard = $('#cardnox_inputs');


                let due_date = document.getElementById('due_date_span');
                due_date.innerHTML = "{{ $due_date }}";
                let payment_status = document.getElementById('payment_status_span');
                payment_status.innerHTML = "{{ $payment_status }}";
                let img_paid = document.getElementById('img_paid');
                img_paid.src = "{{$imagePath}}";

            });

            $(document).ready(function () {

                $('.paywith').click(function () {

                    let choosed = $('form input[name="payment_type"]:checked').val();


                    if (choosed == 'card') {

                        $('#cardnox_inputs').show();
                        $('#ach_cardnox_inputs').hide();
                    } else {

                        $('#cardnox_inputs').hide();
                        $('#ach_cardnox_inputs').show();

                    }
                });


            });

        </script>
    </div>
</body>

</html>
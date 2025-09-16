@extends('admin.admin-layout')

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard"><i data-feather="home"></i></a></li>

                </ol>
            </div>
        </div>
    </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class=" risk-col xl-100 box-col-12">
                <div class="card total-users">
                    <div class="card-header card-no-border d-flex justify-content-between">
                        <h5>Last week {{format_date($data['last_start'])}} to {{format_date($data['last_end'])}}</h5>
{{--                        <a href="{{ url('admin/export-drivers-earnings') }}" class="btn btn-primary">Download Last Week Report</a>--}}

                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="bg-primary card p-10">
                                    <h5 class=" text-center font-dark">Total Payper Trips</h5>
                                    <h6 class=" text-center font-dark">{{$data['total_papertrip']}}</h6>

                                </div>

                            </div>
                            <div class="col-md-3">
                                <div class="bg-secondary card p-10">
                                    <h5 class="font-white text-center">Total Prepaid Payments</h5>
                                    <h6 class="font-white text-center">${{$data['prepaid_amount']}}</h6>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-success card p-10">
                                    <h5 class="font-dark text-center">Total Prepaid Account Trips Amount</h5>
                                    <h6 class="font-dark text-center">${{$data['total_trip_account_method']}}</h6>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-success card p-10">
                                    <h5 class="font-dark text-center">Total Payments Drivers</h5>
                                    <h6 class="font-dark text-center">${{ $data['to_driver'] }}
                                    </h6>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-danger card p-10">
                                    <h5 class=" text-center font-dark">Total Card Trips</h5>
                                    <h6 class=" text-center font-dark">${{$data['total_trip_card_method']}}</h6>

                                </div>
                            </div>
                            <div class="col-md-3 d-none">
                                <div class="bg-danger card p-10">
                                    <h5 class=" text-center font-dark">Total Cardnox Payments</h5>
                                    <h6 class=" text-center font-dark">${{$data['total_trip_account_method'] + $data['prepaid_amount']}}</h6>

                                </div>
                            </div>
                            <div class="col-md-3 d-none">
                                <div class="bg-danger card p-10">
                                    <h5 class=" text-center font-dark">Total Cardnox Fee</h5>
                                    @php
                                    $fee = (float)($data['total_trip_account_method'] + $data['prepaid_amount']) * 0.03333333333 + .3;
                                    $fee1 = (float)($data['total_trip_account_method'] + $data['prepaid_amount']) * 0.03333333333;
                                    $fee3 = ((float)($data['total_trip_account_method'] + $data['prepaid_amount'])/100) * 3.63;
                                    @endphp
                                    <h6 class=" text-center font-dark">${{$fee}}</h6>
{{--                                    <h6 class=" text-center font-dark">${{$fee1}}</h6>--}}
{{--                                    <h6 class=" text-center font-dark">${{$fee3}}</h6>--}}

                                </div>
                            </div>
                            <div class="col-md-5">


                            </div>

                        </div>


                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection

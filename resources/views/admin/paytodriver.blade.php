
@extends('admin.admin-layout')

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

                    <form action="{{route('admin.pay-to-driver')}}" method="post" id="payment_form" >
                        @csrf
                        <div class="px-3">
                            <button type="button" onclick="submit_form()" id="submit_form_btn" class="btn btn-outline-primary text-dark btn-block w-100" >$<span id="total_pay">{{$submit_amount}}</span> Pay driver</button>
                        </div>
                        <div class="card-body pt-0" id="trip-div">
                            <h5>Select Trip</h5>
                            @php
                                $submit_amountt = $submit_amount;
                            @endphp
                            @foreach ($trips as $trip)
                                @if($trip->date > '2024-09-14')
                                    @php

                                        $paid = $trip->total_paid + $trip->total_paid_adjust;
                                       // $paid = 0;

                                    @endphp
                                    @if($paid < $trip->trip_cost)
                                    @php


                                        $left = $trip->total_paid_from_customer - $paid;
                                        $checked = '';
                                        $paying = 0;
                                         if($trip->total_paid_from_customer > $paid){
                                            if($submit_amountt <= 0){
                                                $paying = 0;
                                            }elseif($submit_amountt >= $left){

                                             $checked = 'checked';
                                             $submit_amountt = $submit_amountt - $left;
                                             $paying  = $left;

                                             }elseif($submit_amountt < $left && $submit_amountt > 0){
                                                $paying  = $submit_amountt;
                                                 $checked = 'checked';
                                                $submit_amountt = $submit_amountt - $paying;
                                             }
                                        }
                                    @endphp
                                    @if($checked == 'checked')
                                <div class="card">
                                    <div class="media p-20">


                                        <div class="form-check radio radio-primary me-3">
                                            <input class="paycheckbox" type="checkbox" data-amount="{{$paying}}" {{$checked}} name="trip[]" id="radio{{$trip->trip_id}}" value="{{$trip->trip_id}}"/>
                                            <input type="hidden" name="amount[]" value="{{$paying}}" />
                                            <input hidden name="driver_id" value="{{$trip->driver_id}}">
                                            <label class="form-check-label" for="radio{{$trip->trip_id}}">
                                                <div class="media-body">
                                                    <h6 class="mt-0 mega-title-badge">
                                                       ({{$trip->trip_id}}){{$trip->location_from}} to {{$trip->location_to}}

                                                    </h6>
                                                    <p class="notranslate">
                                                        @php
                                                            $formattedDate = format_date($trip->date);
                                                            $formattedTime = time_format($trip->time);
                                                        @endphp
                                                        Date:{{$formattedDate}} <span class="notranslate">{{$formattedTime}}</span>
                                                        <br><span class="digits">Cost: ${{$trip->trip_cost}}</span>
                                                        <br><span class="digits">Already Paid: ${{$paid}}</span>
                                                        <br><span class="digits">UnPaid: ${{$left}}</span>
                                                        <br><span class="digits">Paying: ${{$paying}}</span>
                                                    </p>

                                                </div>
                                            </label>
                                        </div>


                                    </div>
                                </div>
                                    @endif

                                @endif
                                @endif
                            @endforeach

                            @if($submit_amountt > 0)
                                <div class="alert alert-danger">
                                    Remaining Amount Not Greater Then UnSelected Cost Trips Remaining= {{$submit_amountt}}
                                </div>
                            @endif

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

        $('.paycheckbox').click(function () {
            var amount = 0;
            $('.paycheckbox').each(function () {
                if($(this).is(':checked')){
                    amount += parseFloat($(this).data('amount'));
                }
            });

            $('#total_pay').html(amount.toFixed(2));
        });

        function submit_form(){

            $('#submit_form_btn').attr('disabled', true);
            $('#payment_form').submit();

        }

    </script>
@endsection

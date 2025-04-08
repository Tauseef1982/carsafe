@extends('customer.layouts.yajra')
@section('css')
    <style>
        .icon {
            float: right;
            margin-top: -28px;
            margin-right: 20px;
        }
    </style>

@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Account Setting</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">

                    </ol>

                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">

                        <div class="card-body">

                            <form method="post" action="{{url('customer/settings/update')}}">
                                @csrf

                             @if($account->account_type == 'prepaid')
                                <div id="recharge">

                                    <label for="">Do you want to on auto fill for your account</label><br>
                                    <input type="radio" id="on-autofill" name="autofill" value="on"
                                           @if($account->autofill == 'on') checked @endif><label for="on-autofill ms-2">Auto
                                        fill On</label><br>
                                    <input type="radio" id="off-autofill" name="autofill" value="off"
                                           @if($account->autofill == 'off') checked @endif><label
                                        for="off-autofill ms-2">Auto fill Off</label> <br>
                                    <label for="">Please Enter Rechrage Amount</label>
                                    <input type="text" class="form-control mb-3" id="recharge_amount" name="recharge"
                                           value="{{$account->recharge}}" placeholder="$ 00.00">
                                </div>
                                @endif
                                <label for="">Account Number</label>
                                <input type="text" value="{{$account->account_id}}" class="form-control mb-3" disabled name="account_id"/>
                                <label for="">Account Status</label>

                                <input type="text" class="form-control" disabled value="@if ($account->status == 0)Inactive @else Active @endif">
                                <label for="">Name</label>
                                <input type="text" class="form-control mb-3" placeholder="Please enter name"
                                       value="{{$account->f_name}}" name="f_name"/>
                                <label for="">Account PINS</label>

                                <input type="text" class="form-control mb-3"  placeholder="Please enter by separator (,)"
                                       name="pins" value="{{$account->pins}}" />
                                <label for="">Email</label>
                                <input type="email" class="form-control mb-3" name="email"
                                       placeholder="Please Enter email here" value="{{$account->email}}">
                                <label for="">Phone</label>
                                <input type="phone" class="form-control mb-3" name="phone"
                                       placeholder="Please Enter phone here" value="{{$account->phone}}">
                                <label for="">Notification Setting</label> <br>
                                <input type="radio" @if($account->notification_setting == 'account_email') checked
                                       @endif value="account_email" name="notification_setting" id="account_email_n">
                                <label for="account_email_n">Account Email </label>
                                <input type="radio" @if($account->notification_setting == 'account_phone') checked
                                       @endif value="account_phone" name="notification_setting" id="account_phone_n">
                                <label for="account_phone_n">Account Phone Number</label>
                                <br>
                                <input type="radio" @if($account->notification_setting == 'passenger_phone') checked
                                       @endif  value="passenger_phone" name="notification_setting"
                                       id="passenger_phone_n">
                                <label for="passenger_phone_n">Passenger Phone Number</label>
                                <input type="radio" value="both_phone"
                                       @if($account->notification_setting == 'both_phone') checked
                                       @endif name="notification_setting" id="both_phone_n">
                                <label for="both_phone_n">Both Phone Numbers</label>
                                <br>
                                <label for="">Address</label>
                                <input name="address" class="form-control mb-3" placeholder="Please enter address here"
                                       id="address" value="{{$account->address}}">
                                <label for="">Billing Email</label>
                                <input type="email" class="form-control mb-3" name="billing_email"
                                       placeholder="Please Enter billing email here"
                                       value="{{$account->billing_email}}">
                                <label for="">Company Name</label>
                                <input type="text" class="form-control mb-3" placeholder="Please enter company name"
                                       name="company_name" value="{{$account->company_name}}"/>
                                <label for="">Notes</label>
                                <textarea name="notes" class="form-control" placeholder="Please enter notes here"
                                          id="">{{$account->notes}}</textarea>


                                <button class="btn btn-dark mt-3" type="submit">Update</button>

                            </form>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>



@endsection

@section('js')


@endsection

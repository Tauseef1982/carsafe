@extends('admin.admin-layout')
@section('css')
<style>
    #status_changed_div{
        display: none;
    }
</style>
@endsection
@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>

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
                        <h5 class="pull-left">Edit Account</h5>
                        <div class="card-body">

                            <form method="post" action="{{url('admin/update_account')}}/{{$account->id}}">
                                @csrf
                                <label for="">Account Type</label>

                            <select name="account_type" class="form-select mb-3" id="account_type">
                            <option value="">Select Account Type</option>

                            
                            <option value="prepaid" @if($account->account_type == 'prepaid') selected @endif>Pre Paid</option>
                         </select>

                                <div id="recharge">
                                <label for="">First Recharge</label>
                                <input type="text" class="form-control mb-3" name="first_refill" id="first_refill" value="{{$account->first_refill}}" placeholder="$ 00.00">

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
                                <label for="">Account Number</label>
                                <input type="text" class="form-control mb-3" disabled value="{{$account->account_id}}"
                                       name="account_id"/>
                                <label for="">Password (change password <input type="checkbox" name="change_pass" >)</label>
                                <input type="text" class="form-control mb-3" required placeholder="Please enter account password"
                                       name="password"/>
                                <label for="">Account Status</label>
                                <select name="status" id="account_status" class="form-select mb-3">
                                    <option value="{{$account->status}}" selected>
                                        @if ($account->status == 0)Inactive @else Active @endif
                                    </option>
                                    <option value="0">Inactive</option>
                                    <option value="1">Active</option>
                                </select>
                                <div id="status_changed_div">
                                    <label for="">Username <small class="text-danger">Required</small></label>
                                    <input type="text" class="form-control" name="username" id="username_status" placeholder="Please Enter Your Username ">
                                    <label for="">Reason <small class="text-danger">Requierd</small> </label>
                                    <input type="text" class="form-control" name="reason" id="reason_status" placeholder="Please Enter the reason to change status">
                                </div>

                                <label for="">Name</label>
                                <input type="text" class="form-control mb-3" placeholder="Please enter name"
                                       value="{{$account->f_name}} {{ $account->lname }}" name="f_name"/>
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

                                <button class="btn btn-primary mt-3" type="submit">Update</button>

                            </form>
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


            let account_type = $('#account_type').val()
            if (account_type == 'prepaid') {
                $('#recharge').css('display', 'block');
                $('#first_refill').prop('required', true);
            } else if (account_type == 'postpaid') {
                $('#recharge').css('display', 'none');
                $('#first_refill').prop('required', false);
            }
            $('#account_type').change(function () {
                let account_type = $('#account_type').val()
                if (account_type == 'prepaid') {
                    $('#recharge').css('display', 'block');
                    $('#first_refill').prop('required', true);
                } else if (account_type == 'postpaid') {
                    $('#recharge').css('display', 'none');
                    $('#first_refill').prop('required', false);
                }
            });

            if ($('#on-autofill').is(':checked')) {
        $('#recharge_amount').prop('required', true);
    } else {
        $('#recharge_amount').prop('required', false);
    }

    $('#on-autofill').change(function() {
    if ($('#on-autofill').is(':checked')) {
        $('#recharge_amount').prop('required', true);
    } else {
        $('#recharge_amount').prop('required', false);
    }
});
$('#account_status').change(function () {
        if ($(this).val() !== "{{$account->status}}") {
            $('#status_changed_div').show();
            $('#username_status, #reason_status').attr('required', true);
        } else {
            $('#status_changed_div').hide();
            $('#username_status, #reason_status').removeAttr('required');
        }
    });


        });
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5iwvhZmOVgCzqDOrKp_Q7SNYucsFDEd4&libraries=places"
        async defer></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const input = document.getElementById("address");

            // Initialize the Google Places Autocomplete
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ["address"], // Restrict suggestions to addresses
                componentRestrictions: {country: "us"} // Restrict to a specific country (optional)
            });

            // Listener for when a place is selected
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                console.log("Selected Place:", place);
            });
        });

    </script>
@endsection

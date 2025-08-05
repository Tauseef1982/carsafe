@extends('customer.layouts.yajra')
@section('css')
    <style>
        .icon {
            float: right;
            margin-top: -28px;
            margin-right: 20px;
        }
         .form-check-input:checked {
        background-color: #ff6600; 
        border-color: #ff6600;
    }
           .page-wrapper .page-body-wrapper .page-title {
    padding: 15px 9px !important;
    margin: 0 !important;
    
    border-bottom: none !important;
    box-shadow: none !important;
}
.img-div{
    width: 120px;
    height: 120px;
    border-radius: 50%;
   
}
.autofilldiv{
    border: #F9BBA7 1px solid;
    border-radius: 8px;
    padding: 10px 20px 0px;
    background-color: #FEEEEA;
}
    </style>

@endsection
@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card total-users">
                    
                      
                    

                        <div class="card-body">
                              <div class="page-title">
                             <div class="row">
                                 <div class="col-6">
                                    <h3 class="f-28 fw-bold">Account Setting</h3>
                                 </div>
                
                              </div>
                          </div>

                            <form method="post" action="{{url('customer/settings/update')}}" enctype="multipart/form-data">
                                @csrf
                                <!-- <div class="text-center">
                                    <div class="img-div bg-orange-g mx-auto mt-5 mb-4"></div>
                                    <img style="position:absolute;    margin-top: -62px; margin-left: 23px; cursor:pointer" src="{{ asset('assets/images/cemra-icon.png') }}" alt="">
                                </div> -->

    <!-- Hidden File Input -->
    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">


<div class="text-center position-relative">
    <!-- Image Preview Div -->
    <div class="img-div mx-auto mt-5 mb-4"
         style="width: 120px; height: 120px; border-radius: 50%; background-size: cover; background-position: center;
                background-image: url('{{ $account->image ?asset('storage/' . $account->image) : asset("assets/images/default-avatar.png") }}');">
    </div>

    <!-- Camera Icon -->
    <img id="cameraIcon"
         src="{{ asset('assets/images/cemra-icon.png') }}"
         style="position:absolute; margin-top: -62px; margin-left: 23px; cursor:pointer;"
         alt="Camera">
</div>



                                <div id="recharge">
                                   <div class="d-flex justify-content-between autofilldiv">
                                    
                                    <label for="">Do you want to on auto fill for your account</label>
                                    <div class=" ">
                                         <input type="radio" class="form-check-input" id="on-autofill" name="autofill" value="on"
                                           @if($account->autofill == 'on') checked @endif><label for="on-autofill" class="">Auto fill On</label>
                                   
                                         <input type="radio" class="ms-3 form-check-input" id="off-autofill" name="autofill" value="off"
                                           @if($account->autofill == 'off') checked @endif>
                                           <label for="off-autofill" class="">Auto fill Off</label>
                                    </div>
                                   
                                   
                                   </div>
                                   <div class="row">
                                    <div class="col-md-6 mt-3 ">
                                         <label for="">Please Enter Rechrage Amount</label>
                                    <input type="text" class="form-control mb-3" id="recharge_amount" name="recharge"
                                           value="{{$account->recharge}}" placeholder="$ 00.00">
                                    </div>
                                    <div class="col-md-6 mt-3 ">
                                        <label for="">Name</label>
                                         <input type="text" class="form-control mb-3" placeholder="Please enter name"
                                          value="{{$account->f_name}}" name="f_name"/>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                          <label for="">Account Status</label>
                                         <input type="text" class="form-control" disabled value="@if ($account->status == 0)Inactive @else Active @endif">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                         <label for="">Account Number</label>
                                <input type="text" value="{{$account->account_id}}" class="form-control mb-3" disabled name="account_id"/>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="">Account PINS</label>

                                <input type="text" class="form-control mb-3"  placeholder="Please enter by separator (,)"
                                       name="pins" value="{{$account->pins}}" />

                                    </div>
                                    <div class="col-md-6 mt-2">
                                         <label for="">Email</label>
                                <input type="email" class="form-control mb-3" name="email"
                                       placeholder="Please Enter email here" value="{{$account->email}}">

                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="">Phone</label>
                                <input type="phone" class="form-control mb-3" name="phone"
                                       placeholder="Please Enter phone here" value="{{$account->phone}}">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="">Where should you get account updates</label>
                                       
                                       <select name="notification_setting" class="form-select" id="">
                                     <option value="">Select Please</option>
                                     <option value="account_email" @if($account->notification_setting == 'account_email') selected @endif>Account Email</option>
                                      <option value="account_phone" @if($account->notification_setting == 'account_phone') selected @endif>Account Phone Number</option>
                                     <option value="passenger_phone" @if($account->notification_setting == 'passenger_phone') selected @endif>Passenger Phone Number</option>
                                     <option value="both_phone" @if($account->notification_setting == 'both_phone') selected @endif>Both Phone Numbers</option>
                                    </select>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                         <label for="">Company Name</label>
                                <input type="text" class="form-control mb-3" placeholder="Please enter company name"
                                       name="company_name" value="{{$account->company_name}}"/>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                         <label for="">Billing Email</label>
                                <input type="email" class="form-control mb-3" name="billing_email"
                                       placeholder="Please Enter billing email here"
                                       value="{{$account->billing_email}}">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                     <label for="">Address</label>
                                <input name="address" class="form-control mb-3" placeholder="Please enter address here"
                                       id="address" value="{{$account->address}}">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                          <label for="">Notes</label>
                                <textarea name="notes" class="form-control" placeholder="Please enter notes here"
                                          id="">{{$account->notes}}</textarea>
                                    </div>
                                   </div>
                            </div>
                              <button class="btn bg-orange-g b-r-6 text-white mt-3 float-end" type="submit">Update</button>

                            </form>
                        </div>

                    

                </div>
            </div>
        </div>
    </div>



@endsection

@section('js')

<script>
$(document).ready(function() {
    $('#cameraIcon').on('click', function() {
        $('#imageInput').click();
    });

    $('#imageInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();

            reader.onload = function(e) {
                $('.img-div').css('background-image', 'url(' + e.target.result + ')');
            };

            reader.readAsDataURL(file);
        }
    });
});
</script>


@endsection

@extends('customer.layouts.yajra')
@section('css')
<style>
    .page-wrapper .page-body-wrapper .page-title {
    padding: 15px 9px !important;
    margin: 0 !important;
    
    border-bottom: none !important;
    box-shadow: none !important;
}
.green{
    margin-left: 5px;
    width: 17px;
    height: 17px;
    background-color: #00C42A;
    border-radius: 50%;
}
.red{
    margin-left: 5px;
    width: 17px;
    height: 17px;
    background-color: #FF4141;
    border-radius: 50%;
}
</style>
@endsection
@section('content')
   
    <div class="card total-users" style="padding-bottom:70px;">

           <div class="container-fluid">
              <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3 class="f-28 fw-bold">Book a Ride</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Booking</li>

                      </ol>
                    </div>
                  </div>
                  
                </div>
                
            <div class="row">
                 <!-- Zero Configuration  Starts-->
                 <div class="col-sm-12 mt-5">
                    <div class="">
                     <p class="d-flex">
                        <span class="pt-2"><img class="img-fluid" src="{{asset('assets/images/map-pin-3-line.png')}}" alt=""></span>
                        <span class="f-24 fw-bold ms-2 ">Select Pickup & Drop Location</span>
                    </p>
                   
                    </div>
                    <form action="" method="post">
                        @csrf
                    <div class="d-flex">
                        <div class="green"></div>

                        <p class="f-14 f-w-700 ms-3" style="line-height:17px;">Pickup Location</p>
                    </div>
                    <img src="{{ asset('assets/images/Line 4.png') }}" style="    position: absolute; margin-left: 12px; margin-top: -14px; height:100px;" alt="">
                    <div class="form-group mb-5 ps-5">
                    
                    <div class="input-group ">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                      <input class="form-control" type="text" name="pickup_location" placeholder="Please enter your full address. Address, City, State and Zip.">
                    </div>
                  </div>
                    <div class="d-flex mt-5">
                        <div class="red"></div>
                        <p class="f-14 f-w-700 ms-3" style="line-height:17px;">Drop Location</p>
                    </div>
                    <div class="form-group ps-5 mb-5">
                    
                    <div class="input-group">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                      <input class="form-control" type="text" name="drop_location" placeholder="Please enter your full address. Address, City, State and Zip.">
                    </div>
                  </div>
                  <div class="d-flex text-end">
                    <button class="btn btn-light me-2 text-gray b-r-8 ms-auto">Cancel</button>
                    <button class="btn bg-orange-g text-white b-r-8" type="submit">Countinue</button>
                     
                  </div>
                  </form>

                  </div>

                 

                  <!-- Zero Configuration  Ends-->
            </div>
           </div>
          </div>

@endsection
@section('js')

   


@endsection

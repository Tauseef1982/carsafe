@extends('customer.layouts.yajra')
@section('css')
<style>
     .page-wrapper .page-body-wrapper .page-title {
    padding: 15px 9px !important;
    margin: 0 !important;
    
    border-bottom: none !important;
    box-shadow: none !important;
}
</style>

@endsection

@section('content')
    <div class="container-fluid">
               
              </div>
    <div class="card total-users">

           <div class="container-fluid">
            <div class="row">
                 <!-- Zero Configuration  Starts-->
                 <div class="col-sm-12">
                    <div class="">

                    <div class="card-body">
                       <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3 class="f-28 fw-bold">Account Pins</h3>
                    </div>
                    <div class="col-6">
                      <!-- <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Account Pins</li>

                      </ol> -->
                    </div>
                  </div>
                </div>
                        <form action="{{ url('customer/pins/update') }}" method="post">
                       @csrf
                        <label for="">Account PINS</label>
                        <div class="d-flex justify-content-between">
                          <div class="form-group w-75">
                            <div class="input-group">
                               <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                             <input type="text" class="form-control" placeholder="Please enter by separator (,)" name="pins"
                            value="{{$pins}}" />
                            </div>
                          </div>
                         
                        <input type="submit" class="btn bg-orange-g b-r-6 text-white" value="Update Your Pins">

                        </div>
                        
                        <small>You can add more pin numbers here just add , and your pin number</small>
                            
                            
                        </form>
                    </div>
                    </div>
                  </div>
                  <!-- Zero Configuration  Ends-->
            </div>
           </div>
          </div>

@endsection
@section('js')

   


@endsection

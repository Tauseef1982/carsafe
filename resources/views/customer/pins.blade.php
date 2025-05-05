@extends('customer.layouts.yajra')

@section('content')
    <div class="container-fluid">
                <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3>Pins</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Pins</li>

                      </ol>
                    </div>
                  </div>
                </div>
              </div>
    <div class="card total-users">

           <div class="container-fluid">
            <div class="row">
                 <!-- Zero Configuration  Starts-->
                 <div class="col-sm-12">
                    <div class="">

                    <div class="card-body">
                        <form action="{{ url('customer/pins/update') }}" method="post">
                       @csrf
                        <label for="">Account PINS</label>
        
                        <input type="text" class="form-control mb-3" placeholder="Please enter by separator (,)" name="pins"
                            value="{{$pins}}" />
                            <small>You can add more pin numbers here just add , and your pin number</small>
                            <br>
                            <input type="submit" class="btn btn-primary" value="Update Your Pins">
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

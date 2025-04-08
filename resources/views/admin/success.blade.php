@extends('admin.admin-layout')

@section('content')
<div class="page-title">
  <div class="row">
    <div class="col-6">

    </div>
    <div class="col-6">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}"><i data-feather="home"></i></a></li>
        <li class="breadcrumb-item"><a href="">Payment Success</a></li>
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
        <div class="card-header card-no-border">


        </div>
        <div class="card-body pt-0 ">


          <div class="row ">
            <div class="col-6 m-auto text-center ">
              @if (session('error'))
          <div class="alert alert-danger">
          {{ session('error') }}
          </div>
        @endif
              <img src="assets/images/svg-icon/check-circle.svg" class="img-fluid" width="200px" alt="" srcset="">
              <h3>Trip has been paid</h3>
              @if(isset($paid_cost))
    <h6>Amount paid = {{ $paid_cost }}</h6>
@endif
@if(isset($trip_id))
              <label for="" style="cursor: pointer; text-decoration:underline " id="show-complaint-field">Have You Any Complaint</label><br>
              <div class="form-group mt-3 " style="display: none;" id="complaint-field">
                               <form action="{{url('admin/register-complaint')}}?is_admin=1" method="post">
                                @csrf
                                <input type="text" hidden name="driver_id" value="{{$id}}">
                                <input type="text" hidden name="trip_id" value="{{$trip_id}}">
                               <select class="form-control" id="complaint-select" name="complaint">
                                   <option value=""> Select Complaint </option>
                                   <option value="wrong_address">Wrong Address</option>
                                   <option value="incorrect_fare">Incorrect Fare</option>
                               </select>
                               <input type="submit" class="btn btn-success mt-3" value="Register Your Complaint">
                               </form>
                           </div>
                           @endif
              <a href="{{url('admin/driver')}}/{{$id}}" class="btn btn-primary mt-3">Go Back</a>
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
</div>
<!-- Container-fluid Ends-->
@endsection


@section('js')
<script>
  $(document).ready(function(){
    $('#show-complaint-field').click(function(){
   $('#complaint-field').toggle(); 
   });
  })
</script>

@endsection

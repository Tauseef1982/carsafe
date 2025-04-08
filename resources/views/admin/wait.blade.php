@extends('admin.admin-layout')

@section('content')
<div class="page-title">
  <div class="row">
    <div class="col-6">

    </div>
    <div class="col-6">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}"><i data-feather="home"></i></a></li>
        <li class="breadcrumb-item"><a href="">Payment Waiting</a></li>
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
              <img src="assets/images/svg-icon/waiting.svg" class="img-fluid" width="200px" alt="" srcset="">
              <h3>Waiting for the Trip to be Paid.</h3>
              @if(isset($paid_cost))
    <h6>Amount To be paid = {{ $paid_cost }}</h6>
@endif

              {{-- <a href="{{url('admin/driver')}}/{{$id}}" class="btn btn-primary mt-3">Go Back</a> --}}
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

@extends('layout')

@section('content')
<div class="page-title">
  <div class="row">
    <div class="col-6">

    </div>
    <div class="col-6">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{url('dashboard')}}"><i data-feather="home"></i></a></li>
        <li class="breadcrumb-item"><a href="">Payment Waiting </a></li>
      </ol>
    </div>
  </div>
</div>
</div>
<!-- Container-fluid starts-->
<div class="container-fluid">
  <div class="row size-column">
    <div class="col-xl-3 risk-col xl-100 box-col-12">
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
            <h6>Amount to be paid = {{ $paid_cost }}</h6>
        @endif


               <a href="{{url('start-again')}}/{{$trip_id}}" class="btn btn-danger mt-5">Cancel Payment</a>
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

      function fetchData() {
          $.ajax({
              url: "{{ url('checkwebhook') }}/{{$trip_id}}",
              method: "GET",
              success: function(response) {
                 if(response.status == true){

                     swal({
                         title: 'Paid!',
                         text: response.msg,
                         icon: 'success',
                         confirmButtonText: 'Cool'
                     });
                     window.location.href = "{{url('/success')}}/{{$trip_id}}";
                 }else{
                     console.log('no no');
                 }
              },
              error: function(error) {
                  alert('errorr');
              }
          });
      }

      // Call fetchData every 2 seconds (2000 milliseconds)
      setInterval(fetchData, 2000);

  });
</script>

@endsection

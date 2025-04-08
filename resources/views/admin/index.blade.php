@extends('admin.admin-layout')

@section('content')
<div class="page-title">
  <div class="row">
    <div class="col-6">

    </div>
    <div class="col-6">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard"><i data-feather="home"></i></a></li>

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
        <div class="card-header card-no-border d-flex justify-content-between">
          <h5>Last week {{$util->format_date($data['last_start'])}} to {{$util->format_date($data['last_end'])}}</h5>
          <a href="{{ url('admin/export-drivers-earnings') }}" class="btn btn-primary">Download Last Week Report</a>

        </div>
        <div class="card-body pt-0 ">
          <div class="row">
            <div class="col-md-3">
              <div class="bg-primary card p-10">
                <h5 class=" text-center font-dark">Total Trips</h5>
                <h6 class=" text-center font-dark">{{$data['last_trips']}}</h6>

              </div>

            </div>
            <div class="col-md-3">
              <div class="bg-secondary card p-10">
                <h5 class="font-white text-center">Total Drivers Earnings</h5>
                <h6 class="font-white text-center">${{$data['last_earning']}}</h6>

              </div>
            </div>
            <div class="col-md-3">
              <div class="bg-success card p-10">
                <h5 class="font-dark text-center">Total Owed to Drivers</h5>
                <h6 class="font-dark text-center">${{$data['last_owed_driver']}}</h6>

              </div>
            </div>
            <div class="col-md-3">
              <div class="bg-success card p-10">
                <h5 class="font-dark text-center">Unpaid Trips</h5>
                <h6 class="font-dark text-center">${{ $data['tripsCount'] }}
                </h6>

              </div>
            </div>
            <div class="col-md-3">
              <div class="bg-danger card p-10">
                <h5 class=" text-center font-dark">Total Weekly Fee Paid</h5>
                <h6 class=" text-center font-dark">${{$data['last_weekly']}}</h6>

              </div>
            </div>
            <div class="col-md-5">
           

            </div>

          </div>


        </div>
      </div>

    </div>

  </div>
  <div class="row size-column">
    <div class=" risk-col xl-100 box-col-12">
      <div class="card total-users">
        <div class="card-header card-no-border">
          <h5>Current week {{$util->format_date($data['current_start'])}} to till today</h5>

        </div>
        <div class="card-body pt-0 ">
          <div class="row">
            <div class="col-md-3">
              <div class="bg-primary card p-10">
                <h5 class=" text-center font-dark">Total Trips</h5>
                <h6 class=" text-center font-dark">{{$data['current_trips']}}</h6>

              </div>

            </div>
            <div class="col-md-3">
              <div class="bg-secondary card p-10">
                <h5 class="font-white text-center">Total Drivers Earnings</h5>
                <h6 class="font-white text-center">${{$data['current_earning']}}</h6>

              </div>
            </div>
            <div class="col-md-3">
              <div class="bg-success card p-10">
                <h5 class="font-dark text-center">Total Owed to Drivers</h5>
                <h6 class="font-dark text-center">${{$data['current_owed_driver']}}</h6>

              </div>
            </div>
            <div class="col-md-3">
              <div class="bg-danger card p-10">
                <h5 class=" text-center font-dark">Total Weekly Fee Paid</h5>
                <h6 class=" text-center font-dark">${{$data['current_weekly']}}</h6>

              </div>
            </div>

          </div>


        </div>
      </div>

    </div>

  </div>
</div>
@endsection

@extends('customer.layouts.yajra')

@section('content')
<div class="container-fluid">
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  <h3>Trips History</h3>
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item text-primary">Trips History</li>

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
                    <div class="table-responsive">
                      <table class="display" id="history">
                      <thead class="bg-dark">
                      <tr class="text-primary">
                            <th>Trip ID</th>
                            <th>Pin Status</th>
                            <th>Driver ID</th>
                            <th>From</th>
                            <th>To</th>
                            <!-- <th>Cost</th>
                            <th>Extra</th>
                            <th>Extra Description</th> -->
                            <th>Total Cost</th>
{{----}}
                            <!-- <th>Payment Method</th> -->
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <!-- <th>Complaint</th>
                            <th>Update Reason</th>
                            <th>Accepted By</th> -->
                          </tr>
                        </thead>
                        <tbody>

{{--                        <tr>--}}
{{--                            <td>260409951</td>--}}
{{--                            <td>4444</td>--}}
{{--                            <td>592009</td>--}}
{{--                            <td>24 Witzel Court, Monsey, NY, USA</td>--}}
{{--                            <td>29 Witzel Court, Monsey, NY, USA</td>--}}
{{--                            <td>10.00</td>--}}
{{--                            <td>4.00</td>--}}
{{--                            <td>Stop =$0.00,Stop Location =,Wait =$4.00,Round Trip = $0.00</td>--}}
{{--                            <td>14.00</td>--}}

{{--                            <td>accountAcc #:9246</td>--}}
{{--                            <td>03/11/2025</td>--}}
{{--                            <td>12:22 AM</td>--}}
{{--                            <td>Broadcated</td>--}}
{{--                            <td></td>--}}
{{--                            <td></td>--}}
{{--                            <td>Admin Name</td>--}}
{{--                          </tr>--}}

                        </tbody>

                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Zero Configuration  Ends-->
        </div>
       </div>
      </div>

@endsection


@section('js')

    <script>

        var table = $('#history').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url('customer/trips')}}",
                type: 'GET',
                data: function (d) {
                    d.account_id = "{{$account_id}}";
                }
            },
            columns: [
                {data: 'trip_id', name: 'trip_id'},
                {data: 'cube_status', name: 'cube_status'},
                {data: 'driver_id', name: 'driver_id'},
                {data: 'location_from', name: 'location_from'},
                {data: 'location_to', name: 'location_to'},
              //  {data: 'trip_cost', name: 'trip_cost'},
              //  {data: 'extra_charges', name: 'extra_charges'},
              //  {data: 'ExtraDescription', name: 'ExtraDescription'},
                {data: 'trip_cost', name: 'trip_cost'},
              //  {data: 'payment_method', name: 'payment_method'},
                {data: 'date', name: 'date'},
                {data: 'time', name: 'time'},
                {data: 'status', name: 'status'},
               // {data: 'complaint', name: 'complaint'},
               // {data: 'reason', name: 'reason'},
               // {data: 'accepted_by', name: 'accepted_by'},


            ], // Dynamically assigned columns
            order: [[1, 'desc']]
        });
    </script>

@endsection

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
       <div class="row mb-3 p-3">
    <div class="col-md-3">
        <input type="date" id="from_date" class="form-control">
    </div>
    <div class="col-md-3">
        <input type="date" id="to_date" class="form-control">
    </div>
    <div class="col-md-2">
        <button id="filter_btn" class="btn btn-primary">Filter</button>
    </div>
</div>
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
                            <!-- <th>Status</th> -->
                            <!-- <th>Complaint</th>
                            <th>Update Reason</th> -->
                            <th>Action</th> 
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
<!-- complaint modal -->
<div class="modal fade" id="tripModal" tabindex="-1" aria-labelledby="tripModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tripModalLabel">Register Your Complaint </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="tripModalBody">
        <form action="{{ url('add_account_complaint') }}" method="post">
          @csrf
         <input type="hidden" name="trip_id" id="trip_id">
         <input type="hidden" name="account_id" value="{{ Auth::guard('customer')->user()->account_id }}">
         <label for="">Please add Details</label>
         <textarea name="complaint" class="form-control mb-3" id="" placeholder="Please Enter Here.."></textarea>
         <input type="submit" class="btn btn-primary" value="Submit">
        </form>
        </div>
    </div>
  </div>
</div>

@endsection


@section('js')

    <script>
          function loadTrips(from_date = '', to_date = '') {
            if ($.fn.DataTable.isDataTable('#history')) {
        $('#history').DataTable().destroy();
    }
         $('#history').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url('customer/trips')}}",
                type: 'GET',
                data: function (d) {
        d.account_id = "{{$account_id}}";
        d.from_date = from_date;
        d.to_date = to_date;
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
               // {data: 'status', name: 'status'},
               // {data: 'complaint', name: 'complaint'},
               // {data: 'reason', name: 'reason'},
                {data: 'action', name: 'action'},


            ], // Dynamically assigned columns
            order: [[1, 'desc']]
        }
          );
        };
        let today = new Date().toISOString().split('T')[0];
    let lastWeek = new Date();
    lastWeek.setDate(lastWeek.getDate() - 7);
    let lastWeekStr = lastWeek.toISOString().split('T')[0];

    $('#from_date').val(lastWeekStr);
    $('#to_date').val(today);
   loadTrips(lastWeekStr, today);

        $('#filter_btn').click(function () {
        let from = $('#from_date').val();
        let to = $('#to_date').val();
        loadTrips(from, to);
    });
    </script>

    <script>
      $(document).ready(function() {
        $(document).on('click', '.openTripModal', function () {
    const trip_id = $(this).data('trip');
     $('#trip_id').val(trip_id);
     console.log(trip_id);
  
    $('#tripModal').modal('show');
});

      });
    </script>

@endsection

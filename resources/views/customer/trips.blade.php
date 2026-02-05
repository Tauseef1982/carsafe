@extends('customer.layouts.yajra')
@section('css')
<style>
  #history {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}
  #history thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
#history thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}
   .even{
    background-color: #FEEEEA !important;
}
.even > .sorting_1{
    background-color: #FEEEEA!important;
}
</style>

@endsection

@section('content')

  <div class="card total-users">

    <div class="container-fluid">
    <div class="row pt-3">
      <div class="col-md-3">
      <h1 class="f-28 fw-bold">Trips History</h1>

      </div>
      <div class="col-md-3 pt-2" id="datatable_length_holder"></div>
      <div class="col-md-3">
      <input type="date" id="from_date" class="form-control">
      </div>
      <div class="col-md-3">
      <input type="date" id="to_date" class="form-control">
      </div>

    </div>
    <div class="row">
      <!-- Zero Configuration  Starts-->
        <div class="col-md-12" id="datatable_search_holder"></div>
      <div class="col-sm-12 m-0 pt-0">
      <div class="">

        <div class="">
        <div class="table-responsive ">
          <table class="display" id="history">
          <thead class="table-header-light" >
            <tr class="">
            <th>Trip ID</th>
            <th>Pin Status</th>
            <th>Driver ID</th>
            <th>From</th>
            <th>To</th>

            <th>Total Cost</th>
            <th>Payment Method</th>

            <th>Date</th>
            <th>Time</th>

            <th>Action</th>
            </tr>
          </thead>
          <tbody>


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
         initComplete: function () {

         $('#history_length').appendTo('#datatable_length_holder');
         $('#history_filter').appendTo('#datatable_search_holder');

         const $searchInput = $('#history_filter input');


         const customSearch = `
  <div class="input-group mt-3 mb-3">
    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
    <input type="search" id="custom-search" class="form-control border-start-0" placeholder="Search Trips" />
  </div>
  `;


         $('#datatable_search_holder').html(customSearch);


         $('#custom-search').on('keyup', function () {
           $('#history').DataTable().search(this.value).draw();
         });



         $('#history_filter label').addClass('w-100');
         $('#history_length label').addClass('float-end');
         },


        columns: [
          {data: 'trip_id', name: 'trip_id'},
          {data: 'cube_status', name: 'cube_status'},
          {data: 'driver_id', name: 'driver_id'},
          {data: 'location_from', name: 'location_from'},
          {data: 'location_to', name: 'location_to'},

          {data: 'trip_cost', name: 'trip_cost'},
          {data: 'payment_method', name: 'payment_method'},
          {data: 'date', name: 'date'},
          {data: 'time', name: 'time'},

          {data: 'action', name: 'action'},


        ],
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

      $('#to_date').change(function () {
      let from = $('#from_date').val();
      let to = $('#to_date').val();
      loadTrips(from, to);
    });
        $('#from_date').change(function () {
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

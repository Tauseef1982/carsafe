@extends('customer.layouts.yajra')
@php

$util = new \App\Utils\dateUtil();

@endphp
@section('css')
<style>
       #complain-1 {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}
  #complain thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
#complain thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}
.page-wrapper .page-body-wrapper .page-title {
    margin: 0 !important;
    background-color: none !important;
    border-bottom: 1px solid #D9D9E1 !important;
    box-shadow: none !important;
}

  .active-filter {
    background-color: #F3744D !important;
    color: #ffffff !important ;
    border-color: #F3744D !important;
  }
.rounded-custom {
  border-radius: 12px !important;
}
#complaint_length{
  display:  none !important;
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
        <div class="row p-3">
        <div class="col-6">
        <h3 class="f-28 fw-bold">Complaints</h3>
        </div>
        <div class="col-6">
       <div class="mb-3">
        Filters:
  <button class="btn border filter-btn rounded-custom active-filter me-2" data-status="">All</button>
  <button class="btn border filter-btn rounded-custom me-2" data-status="pending">Pending</button>
  <button class="btn border filter-btn rounded-custom" data-status="solved">Solved</button>
</div>

        </ol>
        </div>
        </div>
      </div>
              <div class="col-md-12" id="Complaint_search_holder"></div>

        <div class="table-responsive mt-4" >
          <table class="display w-100" id="complaint">
          <thead class="table-header-light">
          <tr class="">

            <th class="p-3">Trip ID</th>
            <th>Complaint</th>
            <th>Date</th>
            <th>Status</th>
             <th> Actions</th>

          </tr>
          </thead>
          <tbody>
          @foreach ($complaints as $complaint)
          <tr @if($loop->even) style="background-color: #FEEEEA;" @endif>

          <th class="p-3">{{$complaint->trip_id}}</th>

          <th>
          {{ \Illuminate\Support\Str::words($complaint->complaint, 8, '...') }}
          </th>

          <th>{{$util->format_date($complaint->created_at)}}</th>
          <th>
          @if ($complaint->status === 'pending')
        <span style="color: #FFA600; font-weight: 600;">{{ ucfirst($complaint->status) }}</span>
        @elseif ($complaint->status === 'solved')
        <span style="color: #00C42A; font-weight: 600;">{{ ucfirst($complaint->status) }}</span>
        @else
        <span>{{ ucfirst($complaint->status) }}</span>
        @endif
          </th>


          <th>
          <a href="{{ url('customer/complaint') }}/{{ $complaint->id }}" class="btn bg-orange-g text-white rounded-custom"
            target="_blank">View Details</a>
          </th>

          </tr>
      @endforeach


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
 $(document).ready(function () {
  $('.filter-btn').on('click', function () {
    var selectedStatus = $(this).data('status');

    
    $('.filter-btn').removeClass('active-filter');

   
    $(this).addClass('active-filter');

   
    $('#complain tbody tr').each(function () {
      var statusText = $(this).find('td:nth-child(4), th:nth-child(4)').text().toLowerCase().trim();
      if (!selectedStatus || statusText.includes(selectedStatus)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
});


  $('#complaint').DataTable({
    initComplete: function () {
      const $table = $('#complaint');

      $('#complaint_filter').hide();
    

      const customSearch = `
        <div class="input-group mt-3 mb-3">
          <span class="input-group-text bg-white border-end-0">
            <i class="fa fa-search text-muted"></i>
          </span>
          <input type="search" id="custom-search" class="form-control border-start-0" placeholder="Search Complaints" />
        </div>
      `;

      $('#Complaint_search_holder').html(customSearch);

      $('#custom-search').on('keyup', function () {
        $table.DataTable().search(this.value).draw();
      });
    }
  });



    </script>


@endsection

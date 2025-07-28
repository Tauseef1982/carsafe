@extends('customer.layouts.yajra')
@section('css')
<style>
        #batchesTable {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}
  #batchesTable thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
#batchesTable thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}
.page-wrapper .page-body-wrapper .page-title {
    margin: 0 !important;
    background-color: none !important;
    border-bottom: 1px solid #D9D9E1 !important;
    box-shadow: none !important;
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
      <h1 class="f-28 fw-bold">Payments</h1>
      
      </div>
      <div class="col-md-3 pt-2" id="payment_length_holder"></div>
      <div class="col-md-3">
      <input type="date" id="from_date" class="form-control">
      </div>
      <div class="col-md-3">
      <input type="date" id="to_date" class="form-control">
      </div>
     
    </div>
        <div class="row">
        

             <!-- Zero Configuration  Starts-->
             <div class="col-sm-12">
                <div class="">

                  <div class="card-body">
                    <div class="col-md-12" id="payment_search_holder"></div>
                
                    <div class="table-responsive mt-2">
                      <table class="display" id="batchesTable">
                        <thead class="table-header-light">
                          <tr class="">
                              <th>Batch Number</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Date</th>
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

@endsection
@section('js')

    <script>

      $(document).ready(function () {

        var account_id = "{{$account->account_id}}";

        if ($.fn.DataTable.isDataTable('#batchesTable')) {
          // $('#batchesTable').DataTable().destroy();
          $('#batchesTable').empty();
        }
        var accountType = "{{ $account->account_type }}"; // Get the account type from Blade

        var columns = [

          {data: 'id', name: 'id'},
          {data: 'amount', name: 'amount'},
          {data: 'payment_type', name: 'payment_type'},
          {data: 'created_at', name: 'created_at'},
          {
            data: null,
            className: 'dt-control',
            orderable: false,
            searchable: false,
            defaultContent: '<i class="fa fa-plus-circle" aria-hidden="true"></i>'
          }
        ];

 

        var table = $('#batchesTable').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{url('customer/payments')}}",
            type: 'GET',
            data: function (d) {
              d.account_id = account_id;
            }
          },
      initComplete: function () {

       $('#batchesTable_length').appendTo('#payment_length_holder');
       $('#batchesTable_filter').appendTo('#payment_search_holder');
       const $defaultFilter = $('#batchesTable_filter').clone();
       $('#batchesTable_filter').hide();

       const $searchInput = $('#batchesTable_filter input');


    const customSearch = `
    <div class="input-group mt-3 mb-3">
    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
    <input type="search" id="custom-search" class="form-control border-start-0" placeholder="Search Payments" />
    </div>
    `;

    $('#payment_search_holder').append(customSearch);


       $('#custom-search').on('keyup', function () {
         $('#batchesTable').DataTable().search(this.value).draw();
       });



       $('#batchesTable_filter label').addClass('w-100');
       $('#batchesTable_length label').addClass('float-end');
       },
        
       columns: columns, // Dynamically assigned columns
          order: [[3, 'desc']]
        });


        $('#batchesTable tbody').on('click', 'td.dt-control', function () {
          var tr = $(this).closest('tr');
          var row = table.row(tr);

          if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).html('<i class="fa fa-plus-circle" aria-hidden="true"></i>');
          } else {
            var batch_id = row.data().id;

            $.ajax({
              url: "{{ url('admin/ajax-payments-for-batch') }}",
              type: "GET",
              data: {batch_id: batch_id},
              success: function (response) {
                row.child(formatPaymentsTable(response)).show();
                tr.addClass('shown');
                $(tr).find('td.dt-control').html('<i class="fa fa-minus-circle" aria-hidden="true"></i>'); // Change icon to minus
              }
            });
          }
        });

        function formatPaymentsTable(payments) {
          var table = '<table class="table table-sm custom-bg"><thead><tr><th>Driver ID</th><th>Trip ID</th><th>Payment Date</th><th>Amount</th></tr></thead><tbody>';

          $.each(payments, function (index, payment) {


            let createdAt = payment.created_at; // Timestamp from DB
            let date = new Date(createdAt.replace(" ", "T")); // Ensure correct parsing

            let hours = date.getHours() % 12 || 12; // Convert 24-hour to 12-hour format
            let minutes = date.getMinutes().toString().padStart(2, '0'); // Two-digit minutes
            let ampm = date.getHours() >= 12 ? 'PM' : 'AM';

            let formattedTime = `${hours}:${minutes} ${ampm}`;

            table += '<tr><td>' + payment.driver_id + '</td><td>' + payment.trip_id + '</td><td>' + payment.payment_date + ' ' + formattedTime + '</td><td>$' + payment.amount + '</td></tr>';
          });

          table += '</tbody></table>';
          return table;
        }

        $('.showajax-payments').on('click', function () {

          table.ajax.reload();
        });

      });


    </script>
  <script>


  </script>
@endsection

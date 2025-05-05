@extends('customer.layouts.yajra')

@section('content')
<div class="container-fluid">
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  <h3>Payments History</h3>
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item text-primary">Payments History</li>

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
                      <table class="display" id="batchesTable">
                        <thead class="bg-dark">
                          <tr class="text-primary">
                              <th>Batch Number</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th></th>

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

// Add "created_at" column only if account type is "prepaid"
            if (accountType === "prepaid") {
                columns.push({data: 'created_at', name: 'created_at'});
            }

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

@endsection

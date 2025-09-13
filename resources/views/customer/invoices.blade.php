@extends('customer.layouts.yajra')
@section('css')
<style>
  #accounts {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}
  #accounts thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
#accounts thead tr:first-child th:last-child {
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

       <div class="container-fluid mt-3">

      <div class="row">
         <!-- Zero Configuration  Starts-->
         <div class="col-sm-12">
          <div class="">

            <div class="card-body">
            <div class="page-title ">
          <div class="row">
          <div class="col-6">
            <h3 class="f-28 fw-bold">Invoices</h3>
          </div>
          <div class="col-6">
            <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
            <li class="breadcrumb-item text-primary">Invoices</li>

                  </ol>
                </div>
              </div>
            </div>
          </div>
<div class="card total-users">

       <div class="container-fluid">
           <div class="row mb-3 px-3">
    <div class="col-md-3">
        <label for="from_date">From Date</label>
        <input type="date" id="from_date" class="form-control" />
    </div>
    <div class="col-md-3">
        <label for="to_date">To Date</label>
        <input type="date" id="to_date" class="form-control" />
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary" style="color:white !important;" onclick="download_invoice_link('{{ $account->id }}')">Get invoice</button>
    </div>
</div>
        <div class="row">
             <!-- Zero Configuration  Starts-->
             <div class="col-sm-12">
                <div class="">

                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="display" id="accounts">
                        <thead class="bg-dark">
                          <tr class="text-primary">
                            <th>Ref No</th>
                            <th>Amount</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Ref Id</th>
                            <th>Due Date</th>
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
        $(document).ready(function(){


            var accounts = $('#accounts').DataTable({
                processing: true,
                serverSide: true,
                dom: "Blfrtip",
                // scrollX: '100%',
                // autoWidth: true,
                // responsive: true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 1000]],
                order: [[2, 'desc']],
                ajax: {
                    url: "{{ url('customer/invoices') }}",
                    method: "Get",
                    data: function(d) {
                        d.from = $('#from_date').val();
                        d.to = $('#to_date').val();

                    }
                },
                columnDefs: [
                    {
                        targets: [0,1], // Indexes of the columns you want to hide (0-based)
                        visible: false   // Set visibility to false
                    }
                ],
                columns: [

                    {data: 'id', name: 'id'},
                    {data: 'amount', name: 'amount'},
                    {data: 'invoice_from_date', name: 'invoice_from_date'},
                    {data: 'invoice_to_date', name: 'invoice_to_date'},
                    {data: 'status', name: 'status'},
                    {data: 'transaction_id', name: 'transaction_id'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'action', name: 'action'},


                ],
                buttons: [
                    'excel', 'colvis', 'pdf', 'print'
                ],
                language: {},

            });

            $('#accounts_filter input[type="search"]').on('keyup', function() {
                accounts.column(3).search(this.value).draw(); // Filters only the 'account_id' column (index 3)
            });



               $('#filter').on('click', function(){
                accounts.ajax.reload();
            });
             $('#reset').click(function () {
        $('#from_date').val('');
        $('#to_date').val('');
        accounts.ajax.reload();
    });

        });


function download_invoice_link(id) {
            let from = $('#from_date').val();
            let to = $('#to_date').val();

            window.open("{{url('/')}}" + "/customer/invoice/preview?id=" + id + "&from_date=" + from + "&to_date=" + to, '_blank');
        }


    </script>


@endsection

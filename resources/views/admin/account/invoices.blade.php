@extends('admin.layout.yajra')

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>

                </ol>
            </div>
        </div>
    </div>
    </div>
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="pull-left">Accounts Invoices</h5>


                            <div class="row">
                                <div class="col-4">

                                    <label for="">From</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" value="{{now()->subMonth(1)->toDateString()}}">
                                </div>
                                <div class="col-4">
                                    <label for="">To</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date" value="{{now()->toDateString()}}">

                                </div>
                                <div class="col-4">
                                    <br>
                                    <button class="btn-sm btn-success" id="filter">Filter</button>

                                </div>

                            </div>
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <h5>Total Unpaid Amount : ${{number_format($unpaid_sum,2,'.',',')}}</h5>
                            </div>
                        </div>



                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="accounts">
                                <thead>
                                <tr>
                                    <th>charge10</th>

                                    <th>Del</th>
                                    <th>RefNo</th>
                                    <th>Account</th>
                                    <th>Billing Email</th>
                                    <th>Amount</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                    <th>RefID</th>
                                    <th>Due Date</th>
                                    <th>Tried</th>
                                    <th>Email Send</th>
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
                url: "{{ url('admin/accounts/invoices') }}",
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
                {data: 'charge10', name: 'charge10'},
                {data: 'deldel', name: 'deldel'},
                {data: 'id', name: 'id'},
                {data: 'account_id', name: 'account_id',},
                {data: 'billing_email', name: 'billing_email'},
                {data: 'amount', name: 'amount'},
                {data: 'invoice_from_date', name: 'invoice_from_date'},
                {data: 'invoice_to_date', name: 'invoice_to_date'},
                {data: 'status', name: 'status'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'due_date', name: 'due_date'},
                {data: 'try', name: 'try'},
                {data: 'email_sends', name: 'email_sends'},
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

    });





</script>


@endsection

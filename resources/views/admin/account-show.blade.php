@extends('admin.admin-layout')
@section('css')
    <style>
        .cost-update-btn {
            display: none;
        }

        .extra-update-btn {
            display: none;
        }

        .extra-td {
            cursor: pointer;
        }

        .cost-td {
            cursor: pointer;
        }

        .cost-td:hover .cost-update-btn {
            display: block;

        }

        .extra-td:hover .extra-update-btn {
            display: block;

        }

        .custom-bg {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            margin-top: 10px;
        }

        .custom-bg th,
        .custom-bg td {
            padding: 8px;
            color: #333
        }

        .custom-bg th {
            background-color: #e0e0e0;
        }

        #ajax-loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            z-index: 9999;
        }

    </style>

@endsection
@section('content')
    @php

        $util = new \App\Utils\dateUtil();

    @endphp
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{url('admin/accounts')}}">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/#')}}">Account</a></li>
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
                    @if (session('status') && session('message'))
                        <div
                            class="alert @if (session('status') == "error") alert-danger @else alert-success @endif  }}">
                            {{ session('message') }}
                        </div>
                    @endif
                    <div class="card-header card-no-border">
                        <h5>{{$account->f_name}} {{$account->lname}}</h5>
                         <a href="{{ url('admin/edit/account/') }}/{{$account->id  }}" target="_blank" class="btn btn-primary">Edit Account</a>
                        <div class="card-header-right">

                            <form id="filterForm">
                                @csrf
                                <div class="row">
                                    <div class="col">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="from_date" class="form-control" id="from_date"
                                               value="">
                                    </div>
                                    <div class="col">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="to_date" class="form-control digits date-field"
                                               id="to_date" value="">
                                    </div>
                                </div>
                            </form>
                            
                                <button class="btn-sm btn-dark mt-3"
                                        onclick="download_invoice_link(' {{$account->id }} ')"
                                        data-modalid="invoiceModal" data-original-title="test"
                                        data-modalcontent="' . htmlentities($modalinvoice) . '">
                                    Click Here for invoice
                                </button>
                            

                        </div>
                    </div>
                    {{-- {{dd($account->account_id);}} --}}
                    <input hidden value="{{$account->account_id}}" name="account_id" id="account_id"/>

                    <div class="card-body pt-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-primary card p-10">
                                    <h5 class=" text-center font-dark">Total Trips</h5>
                                    <h6 id="total_trip" class="text-center font-dark">0</h6>

                                </div>

                            </div>
                            <!-- <div class="col-md-3">
                                <div class="bg-info card p-10">
                                    <h5 class="font-dark text-center">Total Driver Earnings</h5>
                                    <h6 id="total_earnings" class="font-dark text-center">$0</h6>

                                </div>
                            </div> -->
                            <!-- <div class="col-md-3">
                                <div class="bg-success card p-10">
                                    <h5 class="font-dark text-center">Total Driver Received</h5>
                                    <h6 id="total_recived" class="font-dark text-center">$0</h6>

                                </div>
                            </div> -->

                            <div class="col-md-6">
                                <div class="bg-secondary card p-10">
                                    <h5 class=" text-center " id="balance_headin">
                                        @if($account->account_type != 'prepaid')
                                            Owed to CarSafe
                                        @else
                                            Prepaid Balance
                                        @endif
                                    </h5>
                                    <h6 id="total_gocab_paid" class=" text-center ">$0</h6>

                                </div>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="card total-users">
                    <div class="card-header card-no-border">
                        <h5>Profile</h5>
                        <div class="card-header-right">


                        </div>
                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <table>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Pins</th>
                                    <th>Action</th>

                                </tr>
                                <tr>
                                    <td>{{$account->f_name}}</td>

                                    <td>{{$account->account_id}}</td>
                                    <td>{{$account->phone}}</td>
                                    <td>{{$account->email}}</td>
                                    <td>{{$account->status == 1  ? 'Active' : 'Inactive'}}</td>
                                    <td>{{$account->pins}}</td>
                                     <td>
                                            <button class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                            Change Status 
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="changeStatusModalLabel">Change Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ url('admin/account/status') }}/ {{  $account->id}} " method="post">
                                                            @csrf
                                                            <input type="hidden" name="account_id" value="{{  $account->account_id}}">
                                                            @if ($account->status == 0)
                                                            <input type="hidden" name="status" value="1">
                                                            @elseif($account->status == 1)
                                                            <input type="hidden" name="status" value="0">
                                                            @endif
                                                            <label for="">Username</label>
                                                            <input type="text" class="form-control mb-3" name="username_change_status" placeholder="Please Enter Your Username" >
                                                            <label for="">Reason</label>
                                                            <input type="text" class="form-control mb-3" name="reason_change_status" placeholder="Please Enter Reason" >
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                      </td>
                                </tr>
                            </table>
                            <hr>

                            <div class="col-md-6 mt-3">
                                <div class="mb-3">
                                    <form action="{{url('admin/pay-to-refill')}}" method="POST">
                                        @csrf
                                        @if ($account->account_type == "prepaid")
                                            <label for="">Select Payment Method to refill </label> <br>
                                            <input type="radio" name="refill_method" value="cash" id="refill_cash">
                                            <label for="refill_cash">Cash</label>
                                            <input type="radio" name="refill_method" value="card" id="refill_card">
                                            <label for="refill_card">Card</label><br>
                                        @endif
                                        <label class="form-label" for="to_amount">Payment Refill</label>
                                        <input hidden value="{{$account->account_id}}" name="account_id"/>
                                        <input class="form-control btn-square" id="to_refill" type="number"
                                               name="to_refill"
                                               placeholder="Enter payment to Refill here" data-bs-original-title=""
                                               title="">
                                        <input type="submit" class="btn btn-primary mt-3" value="save">
                                    </form>
                                </div>
                            </div>


                        </div>


                    </div>
                </div>
                <div class="card total-users">
                    <div class="card-header card-no-border">
                        <h5>Card Information</h5>
                        <div class="card-header-right">
                        @if ($account->account_type == "prepaid")
                         <button class="btn btn-primary open-card-modal" data-bs-toggle="modal" data-id="{{ $account->account_id }}" data-bs-target="#addCreditCardModal">Add New Card</button>
                         @endif

                        </div>
                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <table>
                                <tr>
                                <th>Card Number</th>
                                    <th>cvc</th>
                                    <th>Pirority</th>
                                    <th>Expiry</th>

                                </tr>
                                @foreach($account->cards->where('is_deleted',0) as $card)

                                    <tr>

                                        <td>{{$card->card_number}}</td>
                                        <td>{{$card->cvc}}</td>
                                        <td>{{$card->charge_priority == 1 ? 'primary' : 'secondary' }}</td>
                                        <td>{{$card->expiry}}</td>
                                        @if ($account->account_type == "prepaid")
                                            <td>
                                                <form action="{{url('admin/delete/card/')}}/{{$card->id}}"
                                                      method="post">
                                                    @csrf
                                                    <input type="text" hidden>
                                                    <input type="submit" class="btn btn-danger" value="Delete">
                                                </form>
                                            </td>

                                        @else
                                            <td><a href="{{url('admin/edit/creditcard/')}}/{{$card->id}}"
                                                   class="btn btn-primary">Edit</a></td>
                                        @endif


                                    </tr>
                                @endforeach

                            </table>


                        </div>


                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-primary">

                    </div>
                    <div class="card-body">

                        <div class="tabbed-card">
                            <ul class="pull-left nav nav-pills nav-primary" id="pills-clrtab1" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-trips" data-type="all" data-idd="pills-trips"
                                       id="pills-trips-tab1" data-bs-toggle="pill" href="#pills-trips" role="tab"
                                       aria-controls="pills-trips" aria-selected="false" data-bs-original-title=""
                                       title="">
                                        Trips
                                    </a>
                                </li>
                                @if($account->account_type != 'prepaid')
                                    <li class="nav-item">
                                        <a class="nav-link text-dark showajax-trips" data-type="paid"
                                           data-idd="pills-paidtrips"
                                           id="pills-paidtrips-tab1" data-bs-toggle="pill" href="#pills-paidtrips"
                                           role="tab"
                                           aria-controls="pills-trips" aria-selected="false" data-bs-original-title=""
                                           title="">
                                            Paid Trips
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-dark showajax-trips" data-type="partial"
                                           data-idd="pills-partialtrips" id="pills-partialtrips-tab1"
                                           data-bs-toggle="pill"
                                           href="#pills-partialtrips" role="tab" aria-controls="pills-trips"
                                           aria-selected="false" data-bs-original-title="" title="">
                                            Partial/Unpaid Trips
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-payments" id="pills-tripsbatch-tab1"
                                       data-bs-toggle="pill" href="#pills-tripsbatch" role="tab"
                                       aria-controls="pills-tripsbatch" aria-selected="false" data-bs-original-title=""
                                       title="">
                                        Payments To CarSafe
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link text-dark" id="pills-updated-cost-tab" data-bs-toggle="pill"
                                       href="#pills-updated-cost" role="tab" aria-controls="pills-updated-cost"
                                       aria-selected="false" data-bs-original-title="" title="">
                                        Invoices
                                    </a>
                                </li>


                            </ul>
                            <div class="tab-content" id="pills-clrtabContent1">


                                <div class="tab-pane fade active show showed_ajax_trips" id="pills-trips"
                                     role="tabpanel"
                                     aria-labelledby="">

                                </div>

                                <div class="tab-pane fade showed_ajax_trips" id="pills-paidtrips" role="tabpanel">

                                </div>

                                <div class="tab-pane fade showed_ajax_trips" id="pills-partialtrips" role="tabpanel">

                                </div>

                                <div class="tab-pane fade " id="pills-tripsbatch" role="tabpanel"
                                     aria-labelledby="pills-clrprofile-tab1">
                                    <div class="table-responsive">
                                        <table class="display" id="batchesTable">
                                            <thead>
                                            <tr>
                                                <th></th> <!-- For the expand button -->
                                                @if($account->account_type != 'prepaid')
                                                    <th>Batch ID</th>
                                                @else
                                                    <th>Payment ID</th>
                                                @endif

                                                <th>Account ID</th>
                                                <th>Payment Type</th>
                                                <th>Amount</th>
                                                <th></th>
                                                @if($account->account_type == 'prepaid')
                                                    <th>Date</th>

                                                @endif

                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pills-todriver" role="tabpanel" aria-labelledby="">
                                    <div class="table-responsive">
                                        <table class="display" id="advance-3">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Amount</th>

                                            </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Amount</th>

                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade " id="pills-updated-cost" role="tabpanel" aria-labelledby="">
                                    <div class="table-responsive">
                                        <table class="display table table-sm" id="">
                                            <thead>
                                            <tr>

                                                <th>Ref No</th>
                                                <th>Amount</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                <th>Ref Id</th>
                                                <th>Due Date</th>

                                                <th>Veiw</th>

                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach ($invoices as $invoice )
                                                <tr>

                                                    <td>{{$invoice->ref_no}}</td>
                                                    <td>{{$invoice->amount}}</td>
                                                    <td>{{$invoice->invoice_from_date}}</td>
                                                    <td>{{$invoice->invoice_to_date}}</td>
                                                    <td>{{$invoice->status}}</td>
                                                    <td>{{$invoice->transaction_id}}</td>
                                                    <td>{{$invoice->due_date}}</td>
                                                    <td><a href="{{url('account-invoice')}}/{{$invoice->hash_id}}"
                                                           target="_blank" class="btn btn-primary">View</a></td>

                                                </tr>

                                            @endforeach


                                            <tfoot>
                                            <tr>

                                                <th>Ref No</th>
                                                <th>Amount</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                <th>Ref Id</th>
                                                <th>Due Date</th>

                                                <th>Veiw</th>

                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
    <div id="ajax-loader" style="display: none;">
        Loading...
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update Fee</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.change-driver-fee')}}" method="post">

                    <div class="modal-body">
                        @csrf
                        <label for="">Weekly Fee</label>
                        <input hidden value="{{$account->driver_id}}" name="driver_id"/>
                        <input type="text" class="form-control" name="fee" placeholder="Please Enter new fee here"
                               value="">

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
     <!-- card modal -->
     <div class="modal fade" id="addCreditCardModal" tabindex="-1" role="dialog" aria-labelledby="addCreditCardModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addCreditCardModalLabel">Add Credit Card</h5>
              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              
              <form method="post" action="{{ url('admin/add/credit-card') }}">
                @csrf

                <div class="row">
                  <div class="col-12">
                    
                    <input type="hidden" class="form-control mb-3" name="account_id" id="account_id_card" required>
                  </div> 
               


                 
                  <div class="col-12">
                    <label for="card_number">Card Number</label>
                    <input type="text" class="form-control" name="card_number" id="card_number" required>
                  </div>
                  <div class="col-6">
                    <label for="cvc">CVC</label>
                    <input type="number" class="form-control" name="cvc" id="cvc" required>
                  </div>
                  <div class="col-6">
                    <label for="expiry">Expiry (MM/YY)</label>
                    <input type="text" class="form-control" name="expiry" id="expiry" required placeholder="MM/YY">
                  </div>
                  <div class="col-12">
                    <label for="card_zip">Card Zip</label>
                    <input type="text" class="form-control" name="card_zip" id="card_zip" required>
                  </div>
                  <div class="col-12">
                    <label for="type">Card Type</label>
                    <input type="text" class="form-control" name="type" id="type" value="credit" readonly>
                  </div>
                </div>

                <button class="btn btn-primary mt-3" type="submit">Save</button>
              </form>
            </div>
          </div>
        </div>
      </div>
@endsection

@section('js')
    <script>

        function get_driver_total() {
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var account_id = $('#account_id').val();

            $.ajax({
                url: "{{url('admin/ajax-get-totals')}}",
                method: "GET",
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    account_id: account_id
                },
                success: function (response) {

                    $('#total_trip').text(response.total_trips);
                    // $('#total_earnings').text('$' + response.total_earnings);
                    // $('#total_recived').text('$' + response.total_recived);
                    $('#total_gocab_paid').text('$' + response.total_payments);
                    console.log(response);
                    if (response.gocab_paid >= 0) {
                        $('#balance_heading').html('Amount owed to driver');
                    } else {
                        $('#balance_heading').html('Amount owed to CarSafe');
                    }


                },
                error: function (xhr) {

                }
            });
        }


        function get_trips(type, fromm, too, driver_id, show_id, account_id) {

            $('.showed_ajax_trips').html('');
            $.ajax({
                url: "{{url('admin/ajax-trips-account')}}",
                method: "GET",
                data: {
                    from_date: fromm,
                    to_date: too,
                    driver: driver_id,
                    type: type,
                    show_id: show_id,
                    account_id: account_id,
                },
                success: function (response) {
                    $('#' + show_id).html(response);
                    $('#trips_ajax').dataTable({});
                },
                error: function (xhr) {

                }
            });
        }

        $(document).ready(function () {
            $(document).on('click', '.open-card-modal', function () {
    var accountId = $(this).data('id'); // get data-id value
    $('#account_id_card').val(accountId); // set it as value of input
});


            $(document).ajaxStart(function () {
                $("#ajax-loader").show();
            });


            $(document).ajaxStop(function () {
                $("#ajax-loader").hide();
            });

            var account_id = $('#account_id').val();

            if ($.fn.DataTable.isDataTable('#batchesTable')) {
                // $('#batchesTable').DataTable().destroy();
                $('#batchesTable').empty();
            }
            var accountType = "{{ $account->account_type }}"; // Get the account type from Blade

            var columns = [
                {
                    data: null,
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {data: 'id', name: 'id'},
                {data: 'account_id', name: 'account_id'},
                {data: 'payment_type', name: 'payment_type'},
                {data: 'amount', name: 'amount'},
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
                    url: "{{url('admin/ajax-batch-payments')}}",
                    type: 'GET',
                    data: function (d) {
                        d.account_id = account_id;
                    }
                },
                columns: columns, // Dynamically assigned columns
                order: [[1, 'desc']]
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

            get_trips('all', "{{now()->subDays(7)->format('Y-m-d')}}", "{{now()->format('Y-m-d')}}", null, "pills-trips", account_id);
            $('.showajax-trips').click(function () {

                let type = $(this).data('type');
                let id = $(this).data('idd');
                let account_id = $('#account_id').val();
                get_trips(type, "{{now()->subDays(7)->toDateString()}}", "{{now()->toDateString()}}", null, id, account_id);

            });


            $('#from_date, #to_date').on('change', function () {
                get_driver_total();
            });
            get_driver_total();

            $('#tripsbatch').dataTable({});


        });


        function download_invoice_link(id) {
            let from = $('#from_date').val();
            let to = $('#to_date').val();

            window.open("{{url('/')}}" + "/admin/invoice/preview?id=" + id + "&from_date=" + from + "&to_date=" + to, '_blank');
        }

    </script>



@endsection

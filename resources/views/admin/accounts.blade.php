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
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="pull-left">Accounts</h5>


                        <button class="pull-right btn btn-primary" data-bs-toggle="modal" data-original-title="test"
                                data-bs-target="#invoiceModal">Add Account
                        </button>
                        <button class="pull-right btn btn-primary me-3">Submit Invoices</button>

                        <form method="get" action="' . url("admin/invoice/preview") .'">

                        <div class="row">
                            <div class="col-4">
                                <input type="hidden" class="form-control mb-3" value="'. $row->id .'" name="id"/>
                                <label for="">From</label>
                                <input type="date" class="form-control" name="from_date" id="from_date">
                            </div>
                            <div class="col-4">
                                <label for="">To</label>
                                <input type="date" class="form-control" name="to_date" id="to_date">

                            </div>

                        </div>


                        </form>


                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="accounts">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Account</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Total Cost(From 6th sep)</th>
                                    <th>Total Paid</th>
                                    <th>Remaining</th>
                                    <th>Balance</th>

                                    <th>Status</th>
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
    {{-- <div class="modal fade" id="exampleModal" tabindex="-1"
        role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title"
                       id="exampleModalLabel">Delete
                       Account</h5>
                   <button class="btn-close" type="button"
                           data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form method="post" action="{{url('admin/delete/account')}}">

                   <div class="modal-body">
                       @csrf

                       <input hidden class="form-control mb-3"
                              value="{{$account->id}}" name="id"/>
                       <h3 class="text-center">Are you sure to delete this
                           account</h3>

                   </div>
                   <div class="modal-footer">
                       <button class="btn btn-dark" type="button"
                               data-bs-dismiss="modal">Close
                       </button>
                       <button class="btn btn-primary" type="submit">Delete
                       </button>
                   </div>
               </form>

           </div>
       </div>
   </div> --}}

    {{-- invoice modal --}}
    <div class="modal fade" id="invoiceModal" tabindex="-1"
         role="dialog" aria-labelledby="invoiceModalLabel"
         aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New Account</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{url('admin/add_account')}}">

                    <div class="modal-body">
                        @csrf
                        <label for="">Account Type</label>

                         <select name="account_type" class="form-select mb-3" id="account_type">
                            <option value="">Select Account Type</option>
                             <option value="prepaid" selected>Pre Paid</option>
                         </select>
                         <div id="recharge">


                            <label for="">Please Enter Rechrage Amount</label>
                         <input type="text"  class="form-control mb-3" name="recharge" placeholder="$ 00.00">
                            <label for="">Do you want to on auto fill for your account</label><br>
                              <input type="radio" id="on-autofill" name="autofill" value="on"><label for="on-autofill ms-2">Auto fill On</label><br>
                              <input type="radio" id="off-autofill"  name="autofill" value="off"><label for="off-autofill ms-2">Auto fill Off</label>
                         </div>

                        <label for="">Account Number</label>
                        <input type="text" class="form-control mb-3" required placeholder="Please enter account number"
                               name="account_id"/>
                        <label for="">Name</label>
                        <input type="text" class="form-control mb-3" required placeholder="Please enter name"
                               name="f_name"/>
                        <label for="">Email</label>
                        <input type="email" class="form-control mb-3" required name="email"
                               placeholder="Please Enter email here" value="">
                        <label for="">Phone</label>
                        <input type="phone" class="form-control mb-3" required name="phone"
                               placeholder="Please Enter phone here" value="">
                         <label for="">Notification Setting</label> <br>
                         <input type="radio" value="account_email" name="notification_setting" id="account_email_n">
                         <label for="account_email_n">Account Email </label>
                         <input type="radio" value="account_phone" name="notification_setting" id="account_phone_n">
                         <label for="account_phone_n">Account Phone Number</label>
                         <br>
                         <input type="radio" value="passenger_phone" name="notification_setting" id="passenger_phone_n">
                         <label for="passenger_phone_n">Passenger Phone Number</label>
                         <input type="radio" value="both_phone" name="notification_setting" id="both_phone_n">
                         <label for="both_phone_n">Both Phone Numbers</label>
                         <br>
                        <label for="">Address</label>
                        <textarea name="address" class="form-control" placeholder="Please enter address here" id=""
                                  required></textarea>
                        <label for="">Billing Email</label>
                        <input type="email" class="form-control mb-3" name="billing_email"
                               placeholder="Please Enter billing email here" value="">
                        <label for="">Company Name</label>
                        <input type="text" class="form-control mb-3" placeholder="Please enter company name"
                               name="company_name"/>
                        <label for="">Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Please enter notes here"
                                  id=""></textarea>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button"
                                data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" type="submit">Save
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    {{-- invoice modal --}}




    <div class="modal fade" id="addCardModal" tabindex="-1"
         role="dialog" aria-labelledby="addCardLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document" id="addCardModal_append_modal_body">

        </div>
    </div>

@endsection

@section('js')

<script>
    $(document).ready(function(){
         $('#recharge').css('display', 'none');

       $('#account_type').change(function(){
        let account_type = $('#account_type').val()
            if(account_type == 'prepaid'){
                $('#recharge').css('display', 'block');
            }else if(account_type == 'postpaid'){
                $('#recharge').css('display', 'none');
            }
       })


       var accounts = $('#accounts').DataTable({
            processing: true,
            serverSide: true,
            dom: "Blfrtip",
            // scrollX: '100%',
            // autoWidth: true,
            // responsive: true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 1000]],

            ajax: {
                url: "{{ url('admin/accounts') }}",
                method: "Get",
            },
            columns: [
                {data: 'f_name', name: 'f_name'},
                {data: 'account_id', name: 'account_id',},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'totalCost', name: 'totalCost'},
                {data: 'totalPaidAmount', name: 'totalPaidAmount'},
                {
                    data: null,
                    name: 'remainingAmount',
                    render: function(data, type, row) {
                        // Assuming 'totalCost' and 'totalPaidAmount' are part of 'row'
                        var remaining = row.totalCost - row.totalPaidAmount;
                        return remaining.toFixed(2); // Return remaining amount with 2 decimal places
                    }
                    ,searchable:false
                },
                {data: 'balance', name: 'balance'},

                {data: 'status', name: 'status'},
                {data: 'actions', name: 'actions'},


            ],
            buttons: [
                'excel', 'colvis', 'pdf', 'print'
            ],
            language: {},

        });

        $(document).on('click', '.loadmodal', function () {

            var modall = $(this).data('modalid');
            var modalContent = $(this).data('modalcontent');

            $('#' + modall + '_append_modal_body').html(modalContent);

        })
    });

        function download_invoice_link(id) {
            let from = $('#from_date').val();
            let to = $('#to_date').val();

            window.open("{{url('/')}}" + "/admin/invoice/preview?id=" + id + "&from_date=" + from + "&to_date=" + to, '_blank');
        }



</script>


@endsection

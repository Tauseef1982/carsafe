@extends('admin.layout.yajra')

@section('css')
<style>
    .pac-container {
    z-index: 10000 !important; /* Ensure it is above Bootstrap modal (1050) */
  }
</style>

@endsection

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
                        <form method="post" onsubmit="return confirmSubmit()" action="{{url('admin/accounts/sendBulkInvoiceEmail')}}" id="bulkemailsubmit-form">
                            @csrf
                        <button type="submit" id="bulkemailsubmit" class="pull-right btn btn-primary me-3">Submit Invoices</button>


                        <div class="row">
                            <div class="col-4">
                                <input type="hidden" class="form-control mb-3" value="" name="id"/>
                                <label for="">From</label>
                                <input type="date" class="form-control" name="from_date" id="from_date">
                            </div>
                            <div class="col-4">
                                <label for="">To</label>
                                <input type="date" class="form-control" name="to_date" id="to_date">

                            </div>

                        </div>


                        </form>
                        <div class="row mt-3">
                            <div class="col-4">
                                <label>Status</label>
                                <select class="form-control" id="status">
                                    <option value="">All</option>
                                    <option value="0">Inactive</option>
                                    <option value="1">Active</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="open_invoices">Open Invoices</label><br>
                                <input type="checkbox" id="open_invoices">
                            </div>
                            <div class="col-4">
                                <label for="have_card">Have Card</label><br>

                                <select class="form-control" id="have_card">
                                    <option value="">All</option>
                                    <option value="yes">Have Card</option>
                                    <option value="no">NO Card</option>
                                </select>
                            </div>

                        </div>


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
{{--                                    <th>Total Cost(From 6th sep)</th>--}}
{{--                                    <th>Total Paid</th>--}}
                                    <th>Cards</th>
                                    <th>Balance</th>
                                     <th>Type</th>
                                    <th>Status</th>
                                    <th>Username</th>
                                    <th>Reason</th>
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


    {{-- invoice modal --}}
    <div class="modal fade" id="invoiceModal" tabindex="-1"
         role="dialog" aria-labelledby="invoiceModalLabel"
         aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">New Account</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{url('admin/add_account')}}">

                    <div class="modal-body">
                        @csrf
                        <label for="">Account Type</label>

                         <select name="account_type" class="form-select mb-3" id="account_type">
                            <option value="">Select Account Type</option>
                           
                            <option value="prepaid">Pre Paid</option>
                         </select>
                         <div id="recharge">
                         <label for="">First Recharge</label>
                         <input type="text" class="form-control mb-3" name="first_refill" id="first_refill" placeholder="$ 00.00">

                            <label for="">Do you want to on auto fill for your account</label><br>
                              <input type="radio" id="on-autofill" name="autofill" value="on"><label for="on-autofill ms-2">Auto fill On</label><br>
                              <input type="radio" id="off-autofill"  name="autofill" value="off"><label for="off-autofill ms-2">Auto fill Off</label><br>
                              <label for="">Please Enter Rechrage Amount</label>
                              <input type="text"  class="form-control mb-3" name="recharge" id="recharge_amount" placeholder="$ 00.00">
                         </div>

                        <label for="">Account Number</label>
                        <input type="number" class="form-control mb-3" required placeholder="Please enter account number"
                               name="account_id" id="account_id"/>
                            <div id="errorDiv" style="color: red;"></div>
                        <label for="">Password</label>
                        <input type="text" class="form-control mb-3" required placeholder="Please enter account password"
                               name="password"/>
                        <label for="">Account PINS</label>
                        <input type="text" class="form-control mb-3"  placeholder="Please enter by separator (,)"
                               name="pins"/>
                        <label for="">Name</label>
                        <input type="text" class="form-control mb-3" required placeholder="Please enter name"
                               name="f_name"/>
                        <label for="">Email</label>
                        <input type="email" class="form-control mb-3" required name="email"
                               placeholder="Please Enter email here" value="">
                        <label for="">Phone</label>
                        <input type="phone" class="form-control mb-3 masked" required name="phone"
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
                        <input name="address" class="form-control" placeholder="Please enter address here" id="address" required>
                        <label for="">Billing Email</label>
                        <input type="email" class="form-control mb-3" name="billing_email"
                               placeholder="Please Enter billing email here" value="">
                        <label for="">Company Name</label>
                        <input type="text" class="form-control mb-3" placeholder="Please enter company name"
                               name="company_name"/>
                        <label for="">Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Please enter notes here"
                                  id=""></textarea>
                        <h4>Card Details</h4>
                        <div class="col-12">
                            <label for="card_number">Card Number</label>
                            <input type="text" class="form-control" name="card_number" id="card_number" required>
                        </div>
                        <div class="row">
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

                        </div>
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

    <!-- delete account Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1"
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
                              value="" name="id" id="delete-id"/>
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
   </div>

@endsection

@section('js')

<script>
    $(document).ready(function(){

        $(document).on('click', '.open-delete-modal', function () {
        const id = $(this).data('id');
        $('#delete-id').val(id);
    });
        $('.masked').inputmask("(999) 999-9999");
         $('#recharge').css('display', 'none');
         $('#account_id').on('input', function () {

        var value = $(this).val();

        if (value.length >= 4) {
            $('#errorDiv').text('');
        }
        if (value.length < 4) {
            $('#errorDiv').text('Account number must be at least 4 digits long.');
        }
    });



         $('#account_type').change(function(){
        let account_type = $('#account_type').val()
            if(account_type == 'prepaid'){
                $('#recharge').css('display', 'block');
                $('#first_refill').prop('required', true);
            }else if(account_type == 'postpaid'){
                $('#recharge').css('display', 'none');
                $('#first_refill').prop('required', false);
            }
       });

       $('#on-autofill').change(function() {
    if ($('#on-autofill').is(':checked')) {
        $('#recharge_amount').prop('required', true);
    } else {
        $('#recharge_amount').prop('required', false);
    }
});




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
                
                data: function (data) {
                    data.status = $('#status').val();
                    data.unpaid_postpaid = $('#open_invoices').is(':checked') ? 1 : 0;
                    data.have_card = $('#have_card').val();
                }
            },
         
            columns: [
                {
    data: 'full_name',
    name: 'full_name'
},
        
                        // {data: 'f_name', name: 'f_name', render: function(data, type, row) {
                        //      return data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                        //  }
                        // },
//         { 
//     data: null, // use null when combining multiple fields
//     name: 'f_name', // or a custom name
//     render: function(data, type, row) {
//         let firstName = row.f_name.charAt(0).toUpperCase() + row.f_name.slice(1).toLowerCase();
//         let lastName = row.l_name.charAt(0).toUpperCase() + row.l_name.slice(1).toLowerCase();
//         return firstName + ' ' + lastName;
//     }
// },

                {data: 'account_id', name: 'account_id',},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'cards', name: 'cards'},
                // {data: 'totalCost', name: 'totalCost'},
                // {data: 'totalPaidAmount', name: 'totalPaidAmount'},
                // {
                //     data: null,
                //     name: 'remainingAmount',
                //     render: function(data, type, row) {
                //         // Assuming 'totalCost' and 'totalPaidAmount' are part of 'row'
                //         var remaining = row.totalCost - row.totalPaidAmount;
                //         return remaining.toFixed(2); // Return remaining amount with 2 decimal places
                //     }
                //     ,searchable:false
                // },
                {data: 'balance', name: 'balance'},
                {data: 'account_type', name: 'account_type'},
                {data: 'status', name: 'status'},
                {data: 'username', name: 'username',searchable: false},
                {data: 'reason', name: 'reason'},
                {data: 'actions', name: 'actions'},


            ],
            buttons: [
                'excel', 'colvis', 'pdf', 'print'
            ],
            language: {},

        });

        $('#status,#open_invoices,#have_card').on('change', function () {
            accounts.draw();
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

        // $(document).on('click','#bulkemailsubmit',function (e) {
        //
        //     e.preventDefault();
        //     confirm()
        //     $('#bulkemailsubmit-form').submit();
        // });
    function confirmSubmit() {
        return confirm("Are you sure to send invoices to all accounts?");
    }


</script>
<script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5iwvhZmOVgCzqDOrKp_Q7SNYucsFDEd4&libraries=places"
        async defer></script>
        <script>
  // Flag to track if autocomplete is initialized
  let autocomplete;

  // Initialize autocomplete when the modal is shown
  document.getElementById('invoiceModal').addEventListener('shown.bs.modal', function () {
    const input = document.getElementById('address');

    // Initialize Google Places Autocomplete
    if (!autocomplete) {
      autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address'], // Restrict suggestions to addresses
        componentRestrictions: { country: 'us' } // Restrict to a specific country (optional)
      });

      // Event listener for place selection
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        console.log("Selected Address:", place.formatted_address);
        console.log("Latitude:", place.geometry.location.lat());
        console.log("Longitude:", place.geometry.location.lng());
      });
    }
  });

  // Ensure the Google Maps API script is loaded before adding listeners
  window.addEventListener('load', () => {
    if (!google || !google.maps) {
      console.error("Google Maps API failed to load.");
    }
  });
</script>


@endsection

@extends('admin.admin-layout')

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
                    <button type="button" class="pull-left btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addCreditCardModal">
                            Add Credit Card
                        </button>
                        <button type="button" class="pull-left btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addAchModal">
                            Add ACH
                        </button>

                    </div>
                    <div class="card-body">

                    <div class="tabbed-card">
                        <ul class="pull-right nav nav-pills nav-primary" id="pills-clrtab1" role="tablist">
                            <li class="nav-item"><a
                                    class="nav-link {{request()->tab == 'all' || !isset(request()->tab) ? 'active' : ''}}"
                                    id="pills-all-tab1" href="{{url('admin/accounts/cards')}}?tab=all" role="tab"
                                    aria-controls="pills-all" aria-selected="true" data-bs-original-title=""
                                    title="">Credit Cards</a></li>

                            <li class="nav-item"><a class="nav-link {{request()->tab == 'from_driver' ? 'active' : ''}}"
                                    id="pills-fromdriver-tab1" href="{{url('admin/accounts/cards')}}?tab=from_driver"
                                    role="tab" aria-controls="pills-fromdriver" aria-selected="false"
                                    data-bs-original-title="" title="">ACHs</a></li>




                        </ul>


                        <div class="tab-content" id="pills-clrtabContent1">

                            <div class="tab-pane fade {{request()->tab == 'all' || !isset(request()->tab) ? 'active show' : ''}} "
                                id="pills-all" role="tabpanel" aria-labelledby="pills-clrhome-tab1">
                                <div class="table-responsive">
                            <table class="table table-sm" id="cards_table">
                                <thead>
                                <tr>
                                    <th>Account id</th>
                                    <th>Card Number</th>
                                    <th>Account Type</th>
                                    <th>Type</th>
                                    <th>Expiry</th>
                                    <th>Token</th>
                                    <th>Action</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($creditcards as $creditcard)
                                <tr>
                                 <td>{{$creditcard->account_id}}</td>
                                 <td>{{$creditcard->card_number}}</td>
                                 <td>{{$creditcard->account ? $creditcard->account->account_type : '' }}</td>
                                 <td>{{$creditcard->charge_priority == 1 ? 'primary' : 'secondary' }}</td>
                                 <td>{{ \Carbon\Carbon::parse($creditcard->expiry)->format('m/y') }}</td>
                                 <td>{{$creditcard->cardnox_token}}</td>
                                 <td>
                                    <a href="{{url('admin/edit/creditcard')}}/{{$creditcard->id}}" class="btn btn-primary">Edit</a>
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                                    data-original-title="test"
                                                    data-bs-target="#exampleModal{{$creditcard->id}}">Delete</button>
                                    <div class="modal fade" id="exampleModal{{$creditcard->id}}" tabindex="-1"
                                                 role="dialog" aria-labelledby="exampleModalLabel{{$creditcard->id}}"
                                                 aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="exampleModalLabel{{$creditcard->id}}">Delete
                                                                Card</h5>
                                                            <button class="btn-close" type="button"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="post" action="{{url('admin/delete/card')}}/{{$creditcard->id}}">

                                                            <div class="modal-body">
                                                                @csrf

                                                                <input hidden class="form-control mb-3"
                                                                       value="{{$creditcard->id}}" name="id"/>
                                                                       <input hidden class="form-control mb-3"
                                                                       value="{{$creditcard->account_id}}" name="account_id"/>
                                                                <h3 class="text-center">Are you sure to delete this
                                                                    card</h3>

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
                                 </td>

                               </tr>

                                @endforeach

                            </tbody>



                            </table>
                        </div>
                            </div>
                            <div class="tab-pane fade {{request()->tab == 'from_driver' ? 'active show' : ''}}"
                                id="pills-fromdriver" role="tabpanel" aria-labelledby="pills-clrprofile-tab1">
                                <div class="table-responsive">
                            <table class="table table-sm" id="advance-2">
                                <thead>
                                <tr>
                                    <th>Account id</th>
                                    <th>Account Number</th>
                                    <th>Routing Number</th>
                                    <th>Token</th>
                                    <th>Action</th>

                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($creditcards as $creditcard)
                                @if ($creditcard->type == 'ach')
                                <tr>
                                 <td>{{$creditcard->account_id}}</td>
                                 <td>{{$creditcard->account_number}}</td>
                                 <td>{{$creditcard->routing_number}}</td>
                                 <td>{{$creditcard->cardnox_token}}</td>
                                 <td>
                                    <a href="{{url('admin/edit/creditcard')}}/{{$creditcard->id}}" class="btn btn-primary">Edit</a>
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                                    data-original-title="test"
                                                    data-bs-target="#exampleModal{{$creditcard->id}}">Delete</button>
                                    <div class="modal fade" id="exampleModal{{$creditcard->id}}" tabindex="-1"
                                                 role="dialog" aria-labelledby="exampleModalLabel{{$creditcard->id}}"
                                                 aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="exampleModalLabel{{$creditcard->id}}">Delete
                                                                Card</h5>
                                                            <button class="btn-close" type="button"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="post" action="{{url('admin/delete/card')}}/{{$creditcard->id}}">

                                                            <div class="modal-body">
                                                                @csrf

                                                                <input hidden class="form-control mb-3"
                                                                       value="{{$creditcard->id}}" name="id"/>
                                                                       <input hidden class="form-control mb-3"
                                                                       value="{{$creditcard->account_id}}" name="account_id"/>
                                                                <h3 class="text-center">Are you sure to delete this
                                                                    card</h3>

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
                                 </td>

                               </tr>

                                @endif


                                @endforeach

                            </tbody>
                            <tfoot>
                            <tr>
                            <th>Account id</th>
                                    <th>Account Number</th>
                                    <th>Routing Number</th>
                                    <th>Token</th>
                                    <th>Action</th>
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
    <!-- card modal -->
    <div class="modal fade" id="addCreditCardModal" tabindex="-1" role="dialog" aria-labelledby="addCreditCardModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addCreditCardModalLabel">Add Credit Card</h5>
              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- @php

                    // Get all account IDs from the database
                    $accountIds = DB::table('accounts')
                        ->where('is_deleted', 0)
                        ->pluck('account_id');
                @endphp --}}
              <form method="post" action="{{ url('admin/add/credit-card') }}">
                @csrf

                <div class="row">
                  {{-- <div class="col-12">
                    <label for="account_id">Account ID</label>
                    <input type="text" class="form-control mb-3" name="account_id" id="account_id" required>
                  </div> --}}
                <div class="col-12">
                    <label for="account_id">Account ID</label>
                    <select class="form-control mb-3 select2" name="account_id" id="account_id" required>
                        <option value="">Select Account ID</option>
                        @foreach($accountIds as $id)
                            <option value="{{ $id }}">{{ $id }}</option>
                        @endforeach
                    </select>
                </div>


                  {{-- <div class="col-12">
                    <label for="account_name">Account Name</label>
                    <input type="text" class="form-control mb-3" name="account_name" id="account_name" required>
                  </div>
                  <div class="col-12">
                    <label for="account_number">Account Number</label>
                    <input type="text" class="form-control mb-3" name="account_number" id="account_number" required>
                  </div> --}}
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

      <!-- ach modal -->
      <div class="modal fade" id="addAchModal" tabindex="-1" role="dialog" aria-labelledby="addAchModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addAchModalLabel">Add Ach</h5>
              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

              <form method="post" action="{{ url('admin/add/ach') }}">
                @csrf

                <div class="row">

                <div class="col-12">
                    <label for="account_id">Account ID</label>
                    <select class="form-control mb-3 select2" name="account_id" id="account_id" required>
                        <option value="">Select Account ID</option>
                        @foreach($accountIds as $id)
                            <option value="{{ $id }}">{{ $id }}</option>
                        @endforeach
                    </select>
                </div>



                  <div class="col-12">
                    <label for="card_number">Account Number</label>
                    <input type="text" class="form-control" name="account_number" id="account_number" required>
                  </div>
                  <div class="col-12">
                    <label for="routing">Routing Number</label>
                    <input type="number" class="form-control" name="routing_number" id="routing" required>
                  </div>

                  <div class="col-12">
                    <label for="type">Card Type</label>
                    <input type="text" class="form-control" name="type" id="type" value="ach" readonly>
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
    // Wait for the modal to be shown before adding the event listener
$('#addCreditCardModal').on('shown.bs.modal', function () {
    $('#account_id').select2({
            dropdownParent: $('#addCreditCardModal') // Ensure the dropdown is within the modal
        });

    // Use .find() to target the expiry input inside the modal
    let expiryInput = $(this).find('#expiry');

    // Add input event listener for formatting expiry (MM/YY)
    expiryInput.on('input', function(e) {
        let input = e.target.value.replace(/\D/g, ''); // Remove non-digits

        if (input.length >= 2) {
            input = input.substring(0, 2) + '/' + input.substring(2); // Insert slash after MM
        }

        e.target.value = input; // Update the input field value
    });

    // Add keydown event listener for handling backspace
    expiryInput.on('keydown', function(e) {
        if (e.key === 'Backspace' && this.value.length === 3) {
            this.value = this.value.slice(0, -1); // Remove the slash when backspacing
        }
    });
});

// Optional: Remove event listeners when the modal is closed to avoid memory leaks
$('#addCreditCardModal').on('hidden.bs.modal', function () {
    // Use .find() to target the expiry input inside the modal
    let expiryInput = $(this).find('#expiry');

    // Remove the event listeners when the modal is hidden
    expiryInput.off('input');
    expiryInput.off('keydown');
});

var cards_table = $('#cards_table').DataTable({
         
            dom: "Blfrtip",
            
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 1000]],
            order: [[2, 'desc']],
           
           
           
            buttons: [
                'excel', 'colvis', 'pdf', 'print'
            ],
            language: {},

        });

        $('#cards_table_filter input[type="search"]').on('keyup', function() {
            cards_table.column(0).search(this.value).draw(); // Filters only the 'account_id' column (index 3)
});

  </script>
@endsection

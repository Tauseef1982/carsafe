<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Trip Payments</h5>
        <button class="btn-close" type="button"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
    </div>
    <form method="post" class="update_trip_payments" data-trip-id="{{$trip->trip_id}}">

        <div class="modal-body">
               <table class="table">
                   <thead>
                   <tr>
                       <th>Type</th>
                       <th>Amount</th>
                       <th>Date</th>
                       <th>Activity</th>
                       <th></th>
                   </tr>
                   </thead>
                   <tbody>
                   @foreach($trip->payments->where('is_delete',0) as $payment)
                   <tr>

                       <td>
                           @if($payment->type == 'debit' && $payment->user_type == 'admin')
                               Paid To Driver
                           @elseif($payment->type == 'credit' && $payment->user_type == 'driver')
                               From Driver
                           @elseif($payment->type == 'debit' && $payment->user_type == 'customer' && $payment->account_id != '')
                               Customer Paid From Account
                           @endif

                       </td>
                       <td>{{$payment->amount}}</td>
                       <td>{{$payment->payment_date}}</td>
                       <td>{{$payment->description}}</td>
                       <td>
                           <button class="btn-sm btn-primary" type="button" onclick="edit_single_payment('type','{{$payment->id}}')" >Edit</button>
                           <button class="btn-sm btn-danger" type="button" onclick="delete_single_payment('type','{{$payment->id}}')" >Delete</button>
                       </td>

                   </tr>
                   @endforeach
                   </tbody>
               </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-dark" type="button"
                    data-bs-dismiss="modal">Close
            </button>
{{--            <button class="btn btn-primary"--}}
{{--                    type="button" data-trip-id="{{$trip->trip_id}}">Save--}}
{{--            </button>--}}
        </div>
    </form>

</div>

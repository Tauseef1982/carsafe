<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Trip({{$data->trip_id}}) Payment</h5>
        <button class="btn-close" type="button"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
    </div>
    <form method="post" action="{{url('admin/update-single-payment-modal')}}" class="update_trip_payment" >
            @csrf
        <div class="modal-body">
               <div class="row">
                   <div class="col-md-6">
                       <input hidden name="id" value="{{$data->id}}" />
                       <input class="form-control" name="amount" value="{{$data->amount}}" />
                   </div>
               </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-dark" type="button"
                    data-bs-dismiss="modal">Close
            </button>
            <button class="btn btn-primary"
                    type="submit">Save
            </button>
        </div>
    </form>

</div>

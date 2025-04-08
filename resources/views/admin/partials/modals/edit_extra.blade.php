<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"
            id="extraModalLabel{{$trip->trip_id}}">Update
            Extra Charges</h5>
        <button class="btn-close" type="button"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
    </div>
    <form method="post" class="extra_update_form" data-trip-id="{{$trip->trip_id}}" >

        <div class="modal-body">
            @csrf

            <input hidden class="form-control mb-3"
                   value="{{$trip->trip_id}}" name="trip_id"/>


            <input type="number" name="cost"
                   class="form-control mb-3" hidden
                   value="{{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}">
            <label>Stop</label>
            <input type="text" name="stop" class="form-control" value="{{$trip->extra_stop_amount}}">
            <label>Wait</label>
            <input type="text" name="wait" class="form-control" value="{{$trip->extra_wait_amount}}">
            <label>Round</label>
            <input type="text" name="round" class="form-control" value="{{$trip->extra_round_trip}}">
            <label for="">Please Enter Username</label>
            <input type="text" name="username" required
                   value =""
                   class="form-control mb-3"
                   placeholder="">
            <label for="">Please Enter Reason</label>
            <textarea name="reason"
                      class="form-control" required
                      placeholder="Enter Here">{{$trip->reason}}</textarea>

        </div>
        <div class="modal-footer">
            <button class="btn btn-dark" type="button"
                    data-bs-dismiss="modal">Close
            </button>
            <button class="btn btn-primary extra_form_submit_btn"
                    type="button" data-trip-id="{{$trip->trip_id}}">Save
            </button>
        </div>
    </form>

</div>

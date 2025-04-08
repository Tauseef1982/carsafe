<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"
            id="exampleModalLabel">Update
            Cost</h5>
        <button class="btn-close" type="button"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
    </div>
    <form method="post" class="cost_update_form" data-trip-id="{{$trip->trip_id}}">

        <div class="modal-body">
            @csrf

            <input hidden class="form-control mb-3"
                   value="{{$trip->trip_id}}" name="trip_id"/>

            <label for="">Please Enter New Cost</label>
            <input type="number" name="cost" required
                   value ="{{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}"
                   class="form-control mb-3"
                   placeholder="$ 00.00">
            <label for="">Please Enter Username</label>
            <input type="text" name="username" required
                   value =""
                   class="form-control mb-3"
                   placeholder="">
            <input type="number" name="extra"
                   class="form-control mb-3" hidden
                   value="{{number_format($trip->extra_charges, 2, '.', ',')}}">
            <label for="">Please Enter Reason</label>
            <textarea name="reason"
                      class="form-control" required
                      placeholder="Enter Here">{{$trip->reason}}</textarea>

        </div>
        <div class="modal-footer">
            <button class="btn btn-dark" type="button"
                    data-bs-dismiss="modal">Close
            </button>
            <button class="btn btn-primary cost_form_submit_btn"
                    type="button" data-trip-id="{{$trip->trip_id}}">Save
            </button>
        </div>
    </form>

</div>

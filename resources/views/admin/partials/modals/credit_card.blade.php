<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"
            id="addCardLabel">Add Credit Card to this
            Account</h5>
        <button class="btn-close" type="button"
                data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form method="post" action="{{url("admin/add/credit-card")}}">

    <div class="modal-body">@csrf

        <input type="hidden" class="form-control mb-3" value="{{$row->id}}" name="id"/>

        <input hidden class="form-control mb-3"
               value="{{$row->f_name}}" name="account_name"/>
        <input hidden class="form-control mb-3"
               value="' . $row->account_id . '" name="account_number"/>
        <div class="row">
            <div class="col-12">
                <label for="">Card Number</label>
                <input type="text" class="form-control" name="card_number"
                       placeholder="Please Enter Card number here">
            </div>
            <div class="col-6">
                <label for="">CVC</label>
                <input type="number" class="form-control" name="cvc" placeholder="123">
            </div>
            <div class="col-6">
                <label for="">Expiry</label>
                <input type="text" class="form-control" name="expiry" placeholder="MM/YY">
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

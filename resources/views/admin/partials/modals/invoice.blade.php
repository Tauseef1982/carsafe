<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Select Date Range for Invoice</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form method="get" action="{{url("admin/invoice/preview")}}">

    <div class="modal-body">

        <input type="hidden" class="form-control mb-3" value="{{$row->id}}" name="id"/>
        <label for="">From</label>
        <input type="date" class="form-control" name="from_date">
        <label for="">To</label>
        <input type="date" class="form-control" name="to_date">
    </div>
    <div class="modal-footer">
        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Preview</button>
    </div>
    </form>
</div>

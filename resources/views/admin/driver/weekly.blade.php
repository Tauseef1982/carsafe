

@foreach($payments as $week)
    <tr>

        <td>${{$week->amount}}</td>
        <td>{{$util->format_date($week->payment_date)}}</td>
        <td>paid</td>
{{--        <td>{{$data->balance() < 0 ? 'Unpaid ' : 'paid'}}</td>--}}
        <td>{{$util->format_date($week->payment_date)}}</td>
        <td>
            <Button class="btn btn-danger" data-bs-toggle="modal"
                    data-original-title="test"
                    data-bs-target="#exampleModal{{$week->id}}">
                Delete
            </Button>
            <div class="modal fade" id="exampleModal{{$week->id}}"
                 tabindex="-1"
                 role="dialog"
                 aria-labelledby="exampleModalLabel{{$week->id}}"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"
                                id="exampleModalLabel{{$week->id}}">Delete
                                Fee</h5>
                            <button class="btn-close" type="button"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <form method="post"
                              action="{{url('admin/delete/weekfee')}}">

                            <div class="modal-body">
                                @csrf

                                <input hidden class="form-control mb-3"
                                       value="{{$week->id}}" name="id"/>
                                <h3 class="text-center">Are you sure to
                                    delete this
                                    weekly fee</h3>

                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-dark" type="button"
                                        data-bs-dismiss="modal">Close
                                </button>
                                <button class="btn btn-primary"
                                        type="submit">Delete
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </td>
    </tr>
@endforeach

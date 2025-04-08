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
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="pull-left">Edit Credit Card</h5>
                        <div class="card-body">
                            <form method="post" action="{{url('admin/update_creditcard')}}/{{$creditcard->id}}">
                                @csrf
                                <input hidden class="form-control mb-3"
                                       value="{{$creditcard->account_id}}" name="account_id"/>
                                <input hidden class="form-control mb-3"
                                       value="{{$creditcard->account_name}}" name="account_name"/>
                                <input hidden class="form-control mb-3"
                                       value="{{$creditcard->account_number}}" name="account_number"/>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="">Card Number</label>
                                        <input type="text" class="form-control" name="card_number"
                                               value="{{$creditcard->card_number}}">
                                    </div>
                                    <div class="col-6">
                                        <label for="">CVC</label>
                                        <input type="number" class="form-control" name="cvc"
                                               value="{{$creditcard->cvc}}">
                                    </div>
                                    <div class="col-6">
                                        <label for="">Expiry</label>
                                        <input type="text" class="form-control" name="expiry"
                                               value="{{ \Carbon\Carbon::parse($creditcard->expiry)->format('m/y') }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="">Type</label>
                                        <select class="form-control" name="charge_priority">
                                            <option value="1" {{$creditcard->charge_priority == 1 ? 'selected' : ''}} >
                                                Primary
                                            </option>
                                            <option value="0" {{$creditcard->charge_priority == 0 ? 'selected' : ''}}>
                                                Secondary
                                            </option>
                                        </select>
                                    </div>
                                </div>


                                <button class="btn btn-primary mt-3" type="submit">Update</button>

                            </form>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

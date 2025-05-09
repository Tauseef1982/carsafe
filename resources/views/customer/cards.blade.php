@extends('customer.layouts.yajra')
@section('css')
    <style>
        .icon {
            float: right;
            margin-top: -28px;
            margin-right: 20px;
        }
    </style>

@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Payment Methods</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="me-2">
                            <a class="btn btn-primary btn-air-primary" type="button" data-bs-toggle="modal"
                               data-bs-target="#myModal"><i class="fa fa-plus me-2"></i>Add New Card</a>
                        </li>
                    <!-- <li class="breadcrumb-item"><a href="{{ url('customer-portal') }}">                                       <i data-feather="home"></i></a></li>
        <li class="breadcrumb-item text-primary">Credit Cards</li> -->

                    </ol>

                </div>
            </div>
        </div>
    </div>
    <div class="card total-users">

        <div class="container-fluid">
            <div class="row">
                <!-- Zero Configuration  Starts-->
                <div class="col-sm-12">
                    <div class="">

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="display" id="basic-1">
                                    <thead class="bg-dark">
                                    <tr class="text-primary">
                                        <th>Card No</th>
                                        <th>Expiry</th>
                                        <th>Type</th>
                                        <th>Priority</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($creditcards as $creditcard)

                                        <tr>

                                              @php
                                                $cardNumber = $creditcard->card_number;
                                                $masked = substr($cardNumber, 0, 2) . str_repeat('*', strlen($cardNumber) - 6) . substr($cardNumber, -4);
                                            @endphp
                                            <td>{{$masked}}</td>
                                            <td>{{$creditcard->expiry}}</td>
                                            <td>{{$creditcard->type}}</td>
                                            <td>@if ($creditcard->charge_priority == 1)
                                             Primary
                                             @else
                                             Secondary
                                            @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('customer/editcard') }}/{{$creditcard->id}}" class="btn btn-xs btn-primary btn-air-primary">
                                                    <i class="fa fa-edit me-2"></i>Edit</a>

                                                {{--                                                    <button class="btn btn-danger" data-bs-toggle="modal"--}}
                                                {{--                                                            data-original-title="test"--}}
                                                {{--                                                            data-bs-target="#exampleModal{{$creditcard->id}}">Delete</button>--}}

                                                <!-- <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal{{$creditcard->id}}" class="btn btn-danger btn-xs btn-air-danger">
                                                    <i class="fa fa-trash me-2"  ></i>Delete</a> -->

                                                <div class="modal fade" id="exampleModal{{$creditcard->id}}"
                                                     tabindex="-1"
                                                     role="dialog"
                                                     aria-labelledby="exampleModalLabel{{$creditcard->id}}"
                                                     aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="exampleModalLabel{{$creditcard->id}}">Delete
                                                                    Card</h5>
                                                                <button class="btn-close" type="button"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                            </div>
                                                            <form method="post" action="{{url('customer/delete/card')}}/{{$creditcard->id}}">

                                                                <div class="modal-body">
                                                                    @csrf

                                                                    <input hidden class="form-control mb-3"
                                                                           value="{{$creditcard->id}}" name="id"/>
                                                                    <input hidden class="form-control mb-3"
                                                                           value="{{$creditcard->account_id}}"
                                                                           name="account_id"/>
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

                                    {{--                                    <tr>--}}
                                    {{--                                        <td>4246 3154 1044 2238</td>--}}
                                    {{--                                        <td>06/29</td>--}}
                                    {{--                                        <td>Primary</td>--}}
                                    {{--                                        <td>--}}
                                    {{--                                            <a href="{{ url('customer/edit_credit_card') }}"--}}
                                    {{--                                               class="btn btn-xs btn-primary btn-air-primary"><i--}}
                                    {{--                                                    class="fa fa-edit me-2"></i>Edit</a>--}}
                                    {{--                                            <a href="" class="btn btn-danger btn-xs btn-air-danger"><i--}}
                                    {{--                                                    class="fa fa-trash me-2"></i>Delete</a>--}}
                                    {{--                                        </td>--}}

                                    {{--                                    </tr>--}}
                                    </tbody>
                                    <thead>
                                    <tr>
                                        <th>Card No</th>
                                        <th>Expiry</th>
                                        <th>Type</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Zero Configuration  Ends-->
            </div>
        </div>
    </div>
    <!-- Modal-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!-- Close Button -->
                <div class="modal-header bg-dark">
                    <h5 class="text-primary">Please Add New Credit Card</h5>
                    <button class="btn-close  btn-primary" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <form method="post" action="{{url('customer/add/credit-card')}}" id="add_card_form">

                <div class="modal-body">
                    <div class="card">
                        <div class="animate-widget">
                            <div>
                                    @csrf
                                <input hidden name="account_id" value="{{$account_id}}"/>
                                    <div class="card-js" id="cardnox_inputs">
                                        <div class="card-js">
                                            <input class="card-number my-custom-class form-control" name="card_number"
                                                   placeholder="Card Number" value="">
                                            <input class="expiry-month" name="month" placeholder="MM">
                                            <input class="expiry-year " name="year" placeholder="YYYY" value="">
                                            <input class="cvc form-control mt-3" name="cvc" placeholder="CVC" value="">
                                        </div>
                                    </div>
                                    <!-- <label for="" class="mt-3">Please Select Priority</label>
                                    <select name="charge_priority" class="form-select" id="">
                                        <option value="1">Primary</option>
                                        <option value="0">Secondary</option>
                                    </select> -->

                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer ">
                    <input type="submit" class="btn btn-dark mt-3 ms-auto text-primary" value="Submit">
                </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $(".expiry").addClass("form-control mt-3");
        });

    </script>

@endsection

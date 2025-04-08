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
                        <h5 class="pull-left">Adjustments</h5>
                        <!-- <button class="pull-right btn btn-primary" data-bs-toggle="modal" data-original-title="test"
                                data-bs-target="#exampleModal">Add Adjustment
                        </button> -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display" id="advance-1">
                                <thead>
                                <tr>
                                    <th>Trip Id</th>
                                    <th>Driver Id</th>
                                    <th>Date</th>
                                    <th>Trip Cost</th>
                                    {{--                            <th>Account</th>--}}
                                    <th>Adjustment</th>
                                    <th>Reason</th>

                                </tr>
                                </thead>
                                <tbody>

                                @foreach($data as $adj)
                                    <tr>
                                        <td>{{$adj->trip_id}}</td>
                                        <td>{{$adj->trip->driver_id}}</td>
                                        <td>{{$adj->date}}</td>
                                        <td>${{$adj->trip->trip_cost}}</td>
                                        {{--                            <td>4444</td>--}}
                                        <td>${{$adj->amount}}</td>
                                        <td>{{$adj->reason}}</td>

                                    </tr>
                                @endforeach

                                <tfoot>
                                <tr>
                                    <th>Trip Id</th>
                                    <th>Driver Id</th>
                                    <th>Date</th>
                                    <th>Trip Cost</th>
                                    {{--                            <th>Account</th>--}}
                                    <th>Adjustment</th>
                                    <th>Reason</th>

                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New Adjustment</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.adjustments.save')}}" method="post">

                    @csrf
                    <div class="modal-body">
                        @csrf
                        <label for="">Trip id</label>
                        <input type="text" class="form-control mb-3" required placeholder="Please enter trip id"
                               name="trip_id"/>
                        <label for="">Adjustment</label>
                        <span class="prifix">$</span>
                        <input type="tel" class="form-control mb-3" required name="amount"
                               placeholder="00.00" value="">
                        <label for="">Reason</label>

                        <textarea name="reason" class="form-control" placeholder="Please enter adjustment reason here"
                                  id="" required></textarea>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

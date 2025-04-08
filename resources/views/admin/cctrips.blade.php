@extends('admin.admin-layout')

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                @php
                    $util = new \App\Utils\dateUtil();
                @endphp
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
                        <h5 class="pull-left">Trips</h5>
                    </div>
                    <div class="card-body">
                        <form action="">
                            <input hidden name="tab" value="{{request()->tab}}">

                            <div class="row">
                                <div class="col-md-3">
                                    <label>Search</label>
                                    <input class="form-control" name="search ">
                                </div>
                                <div class="col-md-2">
                                    <label>Driver</label>
                                    <select class="form-control" name="driver">

                                        <option value="">All</option>
                                        @foreach($drivers as $driver)
                                            <option
                                                value="{{$driver->driver_id}}">{{$driver->first_name}} {{$driver->last_name}}</option>

                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Payment Method</label>
                                    <select class="form-control" name="type">
                                        <option value="">All</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="account">Account</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>From Date</label>
                                    <input type="date" value="{{request()->from_date ? request()->from_date : ''}}"
                                           name="from_date" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label>To Date</label>
                                    <input type="date" name="to_date"
                                           value="{{request()->to_date ? request()->to_date : ''}}"
                                           class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <label>Filter</label>
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                </div>
                            </div>
                        </form>
                        <div class="tabbed-card">
                            <ul class="pull-right nav nav-pills nav-primary" id="pills-clrtab1" role="tablist">
                                <li class="nav-item"><a class="nav-link {{request()->tab == 'all' ? 'active' : ''}}" id="pills-clrhome-tab1"
                                                         href="{{url('admin/trips')}}?tab=all" role="tab"
                                                        aria-controls="pills-clrhome1" aria-selected="true"
                                                        data-bs-original-title="" title="">All Trips</a></li>
                                <li class="nav-item"><a class="nav-link {{request()->tab == 'paid' ? 'active' : ''}} " id="pills-clrprofile-tab1"
                                                         href="{{url('admin/trips')}}?tab=paid" role="tab"
                                                        aria-controls="pills-clrprofile1" aria-selected="false"
                                                        data-bs-original-title="" title="">Paid Trips</a></li>
                                <li class="nav-item"><a class="nav-link {{request()->tab == 'half' ? 'active' : ''}}" id="pills-clrcontact-tab1"
                                                         href="{{url('admin/trips')}}?tab=half" role="tab"
                                                        aria-controls="pills-clrcontact1" aria-selected="false"
                                                        data-bs-original-title="" title="">Partily Paid Trips</a></li>
                            </ul>
                            <div class="tab-content" id="pills-clrtabContent1">
                                <div class="tab-pane fade {{request()->tab == 'all' ? 'active show' : ''}}" id="pills-clrhome1" role="tabpanel"
                                     aria-labelledby="pills-clrhome-tab1">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="alltrips">
                                            <thead>
                                            <tr>
                                                <th>Trip Id</th>
                                                <th>Driver Id</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Amount</th>
                                                <th>Extra</th>
                                                <th>Paid</th>
                                                <th>Payment Method</th>
                                                <th>Passenger Phone</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($trips as $trip)
                                                <tr>
                                                    <td>{{$trip->trip_id}}</td>
                                                    <td>{{$trip->driver_id}}</td>
                                                    <td>{{$trip->location_from}}</td>
                                                    <td>{{$trip->location_to}}</td>
                                                    <td>{{$trip->trip_cost}}</td>
                                                    <td>{{number_format($trip->extra_charges, 2, '.', ',')}}</td>
                                                    <td>{{number_format($trip->total_paid, 2, '.', ',')}}</td>
                                                    <td>
                                                        {{$trip->payment_method}}
                                                        @if($trip->payment_method == 'cash')
                                                            <a target="_blank"
                                                               href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                                                               class="btn-sm btn-primary w-100">Accept Customer
                                                                Payment</a>
                                                        @elseif($trip->payment_method == 'account')
                                                            Acc #:{{$trip->account_number}}
                                                        @endif
                                                    </td>
                                                    <td>{{$trip->passenger_phone}}</td>
                                                    @php
                                                        $formattedDate = $util->format_date($trip->date);
                                                        $formattedTime = $util->time_format($trip->time);
                                                    @endphp
                                                    <td>{{$formattedDate}}</td>
                                                    <td>{{$formattedTime}}</td>

                                                </tr>
                                            @endforeach

                                            @include('partials.paginations',['data_links'=>$trips,'colspan'=>7])


                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{request()->tab == 'paid' ? 'active show' : ''}}" id="pills-clrprofile1" role="tabpanel"
                                     aria-labelledby="pills-clrprofile-tab1">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="paidtrips">
                                            <thead>
                                            <tr>
                                                <th>Trip Id</th>
                                                <th>Driver Id</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Amount</th>
                                                <th>Extra</th>
                                                <th>Paid</th>
                                                <th>Payment Method</th>
                                                <th>Passenger Phone</th>
                                                <th>Date</th>

                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($paid as $trip)
                                                <tr>
                                                    <td>{{$trip->trip_id}}</td>
                                                    <td>{{$trip->driver_id}}</td>
                                                    <td>{{$trip->location_from}}</td>
                                                    <td>{{$trip->location_to}}</td>
                                                    <td>{{$trip->trip_cost}}</td>
                                                    <td>{{$trip->extra_charges}}</td>
                                                    <td>{{number_format($trip->total_paid, 2, '.', ',')}}</td>
                                                    <td>
                                                        {{$trip->payment_method}}
                                                        @if($trip->payment_method == 'cash')
                                                            <a target="_blank"
                                                               href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                                                               class="btn-sm btn-primary w-100">Payment</a>
                                                        @elseif($trip->payment_method == 'account')
                                                            Acc #:{{$trip->account_number}}
                                                        @endif
                                                    </td>
                                                    <td>{{$trip->passenger_phone}}</td>
                                                    <td>{{$trip->date}}</td>

                                                </tr>
                                            @endforeach

                                            @include('partials.paginations',['data_links'=>$paid,'colspan'=>7])

                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{request()->tab == 'half' ? 'active show' : ''}}" id="pills-clrcontact1" role="tabpanel"
                                     aria-labelledby="pills-clrcontact-tab1">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="halftrips">
                                            <thead>
                                            <tr>
                                                <th>Trip Id</th>
                                                <th>Driver Id</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Amount</th>
                                                <th>Extra</th>
                                                <th>Paid</th>
                                                <th>Payment Method</th>
                                                <th>Passenger Phone</th>
                                                <th>Date</th>

                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($half as $trip)
                                                <tr>
                                                    <td>{{$trip->trip_id}}</td>
                                                    <td>{{$trip->driver_id}}</td>
                                                    <td>{{$trip->location_from}}</td>
                                                    <td>{{$trip->location_to}}</td>
                                                    <td>{{$trip->trip_cost}}</td>
                                                    <td>{{$trip->extra_charges}}</td>
                                                    <td>{{number_format($trip->total_paid, 2, '.', ',')}}</td>
                                                    <td>
                                                        {{$trip->payment_method}}
                                                        @if($trip->payment_method == 'account')
                                                            Acc #:{{$trip->account_number}}
                                                        @endif
                                                        <a target="_blank"
                                                           href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                                                           class="btn-sm btn-primary w-100">Payment</a>

                                                    </td>
                                                    <td>{{$trip->passenger_phone}}</td>
                                                    <td>{{$trip->date}}</td>

                                                </tr>
                                            @endforeach

                                            @include('partials.paginations',['data_links'=>$half,'colspan'=>7])

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <script>

        $('#alltrips').dataTable({
            paging: false, // Disable pagination
            searching: true, // Keep searching enabled
            ordering: true, // Keep column ordering
            info: false, // Keep table info displayed
            lengthChange: false, // Disable page length option
        });

        $('#halftrips').dataTable({
            paging: false, // Disable pagination
            searching: true, // Keep searching enabled
            ordering: true, // Keep column ordering
            info: false, // Keep table info displayed
            lengthChange: false, // Disable page length option
        });

        $('#paidtrips').dataTable({
            paging: false, // Disable pagination
            searching: true, // Keep searching enabled
            ordering: true, // Keep column ordering
            info: false, // Keep table info displayed
            lengthChange: false, // Disable page length option
        });

    </script>



@endsection

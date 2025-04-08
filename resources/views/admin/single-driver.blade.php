@extends('admin.layout.yajra')
@section('css')
    <style>
#deduction_history_div{
    display: none;
}
    .nested-table {
        display: none;
    }

        .cost-update-btn {
            display: none;
        }

        .extra-update-btn {
            display: none;
        }

        .extra-td {
            cursor: pointer;
        }

        .cost-td {
            cursor: pointer;
        }

        .cost-td:hover .cost-update-btn {
            display: block;

        }

        .extra-td:hover .extra-update-btn {
            display: block;

        }
        #weekly_from_balance{
            display: none;
        }
        #pay_method_div{
            display: none;
        }
        #driver_info_div{
                display: none;
        }
        #driver_info_div_btn{
           cursor: pointer;
           text-decoration: underline;
           display: inline;
           width: auto;
        }
        #from_driver{
            display: none;
        }
        #to_driver{
              display: none;
        }
        #driver_settelment_div_btn{
            cursor: pointer;
           text-decoration: underline;
           display: inline;
           width: auto;
        }
        #pay_to_driver_btn{
            cursor: pointer;
           text-decoration: underline;
           display: inline;
           width: auto;
        }
        #loading-message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    z-index: 9999;
}


    </style>

@endsection
@section('content')

    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{url('admin/drivers')}}">Drivers</a></li>
                    <li class="breadcrumb-item"><a href="{{url('admin/driver')}}">Driver</a></li>
                </ol>
            </div>
        </div>
    </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class=" risk-col xl-100 box-col-12">
                <div class="card total-users">
                    <div class="card-header card-no-border">
                        <h5>{{$data->first_name}} {{$data->last_name}}</h5>
                        <div class="card-header-right">
                            <form id="filterForm">
                                @csrf
                                <div class="row">
                                    <div class="col">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="from_date" class="form-control" id="from_date"
                                               value="">
                                    </div>
                                    <div class="col">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="to_date" class="form-control digits date-field"
                                               id="to_date" value="">
                                    </div>
                                </div>
                            </form>


                        </div>
                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-primary card p-8">
                                    <h5 class=" text-center font-dark">Total Trips</h5>
                                    <h6 id="total_trip" class="text-center font-dark">0</h6>

                                </div>

                            </div>
                            <div class="col-md-6" style="display:block">
                                <div class="bg-secondary card p-8">
                                    <h5 class=" text-center">Unpaid From {{\Carbon\Carbon::now()->subWeek()->startOfWeek(\Carbon\Carbon::SUNDAY)->format('d M')}} to {{\Carbon\Carbon::now()->subWeek()->endOfWeek(\Carbon\Carbon::SATURDAY)->format('d M')}}</h5>
                                    <h6 id="total_lastw" class=" text-center">$0</h6>
                                    <h7 id="paid_unpaid" class=" text-center">$0</h7>

                                </div>
                            </div>
                            <div class="col-md-6" style="display:block">
                                <div class="bg-primary card p-8">
                                    <h5 class="font-dark text-center">Unpaid From {{\Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::SUNDAY)->format('d M')}} to till today</h5>

                                    <h6 id="total_currentw" class="font-dark text-center">$0</h6>
                                    <h7 id="paid_unpaid_current" class="font-dark text-center">$0</h7>


                                </div>
                            </div>

                            <div class="col-md-6" >
                                <div class="bg-secondary card p-8">
                                    <h5 class=" text-center">Deductions From {{\Carbon\Carbon::now()->subWeek()->startOfWeek(\Carbon\Carbon::SUNDAY)->format('d M')}} to {{\Carbon\Carbon::now()->subWeek()->endOfWeek(\Carbon\Carbon::SATURDAY)->format('d M')}}</h5>


                                    <h6 id="total_deductions" class="text-center">$0</h6>
                                    <button class="btn text-white" id="deduction_history_btn">See Details</button>


                                </div>
                            </div>

{{--                            <div class="col-md-3" style="display:none">--}}
{{--                                <div class="bg-info card p-8">--}}
{{--                                    <h5 class="font-dark text-center">Total Driver Earnings</h5>--}}
{{--                                    <h6 id="total_earnings" class="font-dark text-center">$0</h6>--}}

{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3" style="display:none">--}}
{{--                                <div class="bg-success card p-8">--}}
{{--                                    <h5 class="font-dark text-center">Total Driver Received</h5>--}}
{{--                                    <h6 id="total_recived" class="font-dark text-center">$0</h6>--}}

{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-md-6 d-none">
                                <div class="bg-danger card p-8">
                                    <h5 class=" text-center font-dark" id="balance_heading"></h5>
                                    @if($data->weeklyFeeBalance() == 0)<small class=" text-center font-dark">With Weekly Deduction</small>@endif
                                    <h6 id="total_gocab_paid" class=" text-center font-dark">$0</h6>

                                </div>
                            </div>

                            <div class="col-12" id="deduction_history_div" >
                                <table class="table-sm table-responsive" >
                                    <thead>
                                    <tr>
                                        <th>TripID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>

                                    @foreach($historyOfThisWeek as $history)
                                        <tr id="row_{{$history->id}}">
                                            <td>{{$history->trip_id}}</td>
                                            <td>{{$history->date}}</td>
                                            <td>{{$history->amount}}</td>
                                            <td class="history_descr">{{$history->description}}</td>
                                            <td>
                                                @if($history->is_return == 0)

                                                <i class="btn btn-danger remove-history" data-id="{{$history->id}}" >x</i>

                                            <textarea class="form-control hide reason">

                                            </textarea>
                                                <button data-id="{{$history->id}}" class="btn-sm btn-success confirm hide">Confirm</button>
                                                  @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                </table>
                            </div>

                        </div>

                        <h5 id="total_description" style="display:none"></h5>
                    </div>
                </div>
                <div class="card onloadmakeblur">
                    <div class="card-header card-no-border">
                        <h5>Driver Profile</h5>
                        <div class="card-header-right">

                        </div>

                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <table>
                                <tr>
                                    <th>Name</th>
                                    <th>Driver Id</th>
                                    <th>Username</th>
                                    <th>Phone</th>
                                    <th>Weekly Fee</th>
                                    <th>Plate</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Action</th>

                                </tr>
                                <tr>

                                    <td>{{$data->first_name}}</td>
                                    <td>{{$data->driver_id}}</td>
                                    <td>{{$data->username}}</td>
                                    <td>{{$data->phone}}</td>
                                    <td>${{$data->fee_amount->fee}}<span data-bs-toggle="modal"
                                                                         data-original-title="test"
                                                                         data-bs-target="#exampleModal"><i
                                                                         class="fa fa-pencil-square-o btn text-primary"></i></span></td>
                                    @if ($data->plate)
                                    <td>{{$data->plate}} <span  data-bs-toggle="modal" data-bs-target="#plateModal"> <i class="fa fa-pencil-square-o btn text-primary"></i></span></td>
                                    @else
                                    <td><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#plateModal">Add Plate</button></td>
                                    @endif

                                    <td>{{$data->created_at->format('m-d-Y')}}</td>
                                    <td>{{$data->status == 1 || $data->status === null ? 'Active' : 'Inactive'}}
                                    </td>
                                      <td>
                                      @if(strpos($data->username, 'inactive') !== false)
                                              <a href="{{ url('admin/driver/active') }}/{{$data->id}}"
                                                 class="btn btn-success"
                                                 onclick="return confirm('Are you sure you want to activate this driver?')">
                                                  Activate
                                              </a>

                                          @else
                                <a href="{{url('admin/driver/inactive')}}/{{$data->id}}"
                                   onclick="return confirm('Are you sure you want to deactivate this driver?')"

                                    class="btn btn-danger">
                                    Inactivate
                                </a>
                                @endif
                                      </td>
                                </tr>
                            </table>

                            <hr>
                            <p id="driver_info_div_btn">Show More Info</p>
                           {{-- <p id="driver_settelment_div_btn">Driver Settlement</p> --}}


                            <div id="driver_info_div">
                            <h3 class="my-3">Last Trip</h3>
                            @if($latestTrip)
                            <p>Trip Id: {{$latestTrip->trip_id}}</p>
                            <p>Time: {{$util->time_format($latestTrip->time)}}</p>
                            <p>Date: {{$util->format_date($latestTrip->time)}}</p>
                            @else
                        <p>No recent trip found.</p>
                             @endif
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <form id="imageForm" action="" method="post"  enctype="multipart/form-data">
                                    @csrf
                                    <label for="" class="fs-5 fw-bold">Upload Car Image</label>
                                    <div id="input-container">
                                     <input type="file" class="form-control mb-3" name="images[]" accept="image/*" onchange="addInputField()" required>
                                     </div>
                                    <!-- <ul id="file-list" class="file-list"></ul> -->
                                     <input type="hidden" name="driver_id" value="{{$data->driver_id}}">
                                    <button onclick="submitForm()" class="btn btn-primary">Save Images</button>
                                    </form>
                                </div>
                                <div class="row mt-3" id="uploaded-images">

                                </div>
                            </div>
                            </div>
                            <div class="" id="pay_method_div">
                            @if($data->weeklyFeeBalance() == 0)
                            <button class="btn btn-primary" id="show_to_pay_driver_balance">Pay Driver balance</button>
                            @else
                            <button class="btn btn-primary" id="show_from_driver_form">Pay Weekly Fee with cash</button>
                            <button class="btn btn-primary" id="show_to_driver_form">Pay Weekly Fee from balance</button>
                            @endif


                            </div>
                            <div class="row">
                            <div class="col-md-6 mt-3" id="from_driv">
                                <div class="mb-3" >
                                    <form action="{{route('admin.pay-from-driver')}}" method="post">
                                        @csrf
                                        <label class="form-label" for="from_amount">Payment From Driver</label>
                                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                                        <input class="form-control btn-square" id="from_amount" type="number"
                                               name="from_amount" placeholder="Enter payment from driver here" value=""
                                               >
                                        <input type="submit" class="btn btn-primary mt-3" value="save">
                                    </form>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3" >
                                <div class="mb-3" >
                                    <form action="{{route('admin.pay-to-driver')}}" method="post">
                                        @csrf
                                        <label class="form-label" for="to_amount">Payment to
                                            Driver</label>
                                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                                        <input hidden value="last_week" name="priority" id="priority" />
                                        <input class="form-control btn-square" id="to_amount" type="number"
                                               name="to_amount"
                                               placeholder="Enter payment to driver here" data-bs-original-title=""
                                               title="">
                                        <input type="submit" class="btn btn-primary mt-3" value="save">
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3" id="weekly_from_balan">
                                <div class="mb-3" >
                                    <form action="{{route('admin.weekly-fee-from-balance')}}" method="post">
                                        @csrf
                                        <label class="form-label" for="to_amount">Deduct Weekly Fee From Balance</label>
                                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                                        <input class="form-control btn-square" id="to_amount" type="number"
                                               name="weekly_fee"
                                               value="{{$data->fee_amount->fee}}" data-bs-original-title=""
                                               title="">
                                        <input type="submit" class="btn btn-primary mt-3" value="save">
                                    </form>
                                </div>
                            </div>
                            <!-- <div class="col-md-3 mt-3">
                                <button class="btn btn-primary w-100 mt-4" data-bs-toggle="modal"  data-bs-target="#settelModal">Settlement</button>
                            </div> -->
                        </div>
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                       </div>
                 </div>
                </div>
                <div class="card">
                    <div class="card-header bg-primary">

                    </div>
                    <div class="card-body">

                        <div class="tabbed-card">
                            <ul class="pull-left nav nav-pills nav-primary" id="pills-clrtab1" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-trips" data-type="all" data-idd="pills-trips"
                                       id="pills-trips-tab1" data-bs-toggle="pill"
                                       href="#pills-trips" role="tab" aria-controls="pills-trips" aria-selected="false"
                                       >
                                        ALL Trips
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-trips" data-type="paid"
                                       data-idd="pills-paidtrips" id="pills-paidtrips-tab1" data-bs-toggle="pill"
                                       href="#pills-paidtrips" role="tab" aria-controls="pills-trips"
                                       aria-selected="false"
                                       >
                                        Paid Trips
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-trips" data-type="partial"
                                       data-idd="pills-partialtrips" id="pills-partialtrips-tab1" data-bs-toggle="pill"
                                       href="#pills-partialtrips" role="tab" aria-controls="pills-trips"
                                       aria-selected="false"
                                       >
                                        Partial/Unpaid trips
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark showajax-trips" data-type="extacost"
                                       data-idd="pills-extacost" id="pills-extacost-tab1" data-bs-toggle="pill"
                                       href="#pills-extacost" role="tab" aria-controls="pills-trips"
                                       aria-selected="false"
                                       >
                                        Trips Have Extras
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark" id="pills-updated-cost-tab" data-bs-toggle="pill"
                                       href="#pills-updated-cost" role="tab" aria-controls="pills-updated-cost"
                                       aria-selected="false">
                                        Edited Trips
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark" id="pills-tripsbatch-tab1" data-bs-toggle="pill"
                                       href="#pills-tripsbatch" role="tab" aria-controls="pills-tripsbatch"
                                       aria-selected="false" >
                                        Payment to Driver
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a onclick="driver_fetch_tabs_data('from')" class="nav-link text-dark" id="pills-fromdriver-tab1" data-bs-toggle="pill"
                                       href="#pills-fromdriver" role="tab" aria-controls="pills-fromdriver"
                                       aria-selected="false" >
                                        Payments from Driver
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a onclick="driver_fetch_tabs_data('weekly')" class="nav-link text-dark" id="pills-fee-tab1" data-bs-toggle="pill"
                                       href="#pills-fee" role="tab" aria-controls="pills-fee" aria-selected="false"
                                       >
                                        Driver's Weekly Fee
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a onclick="driver_fetch_tabs_data('from_customer')" class="nav-link text-dark" id="pills-from_customer-cost-tab"
                                       data-bs-toggle="pill"
                                       href="#pills-from_customer-cost" role="tab" aria-controls="pills-from_customer"
                                       aria-selected="false"
                                       >
                                        From Customer
                                    </a>
                                </li>


                            </ul>
                            <div class="tab-content" id="pills-clrtabContent1">

                                <div id="loading" style="display:none;">
                                    <p>Loading.....</p> <!-- Your spinner image -->
                                </div>
                                <div class="tab-pane fade active show showed_ajax_trips" id="pills-trips"
                                     role="tabpanel"
                                     aria-labelledby="">


                                </div>

                                <div class="tab-pane fade showed_ajax_trips" id="pills-paidtrips" role="tabpanel">

                                </div>

                                <div class="tab-pane fade showed_ajax_trips" id="pills-partialtrips" role="tabpanel">

                                </div>


                                <div class="tab-pane fade showed_ajax_trips" id="pills-extacost" role="tabpanel">

                                </div>
                                <div class="tab-pane fade tripsbatch" id="pills-tripsbatch" role="tabpanel"
                                     aria-labelledby="pills-clrprofile-tab1">
                                    <div class="table-responsive">
                                        <table class="display" id="batch_payments">
                                            <thead>
                                            <tr>

                                                <th></th>
                                                <th>Batch Id</th>
                                                <th>Date</th>
                                                <th>Total Amount</th>

                                            </tr>
                                            </thead>
                                            <tbody id="set_tab_result_batch">

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade tripsbatch" id="pills-fromdriver" role="tabpanel"
                                     aria-labelledby="pills-clrprofile-tab1">
                                    <div class="table-responsive">
                                        <table class="display" id="advance-2">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Amount</th>

                                            </tr>
                                            </thead>
                                            <tbody id="set_tab_result_from">

                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Amount</th>

                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade tripsbatch" id="pills-fee" role="tabpanel"
                                     aria-labelledby="">
                                    <div class="table-responsive">
                                        <table class="display table table-sm" id="advance-4">
                                            <thead>
                                            <tr>

                                                <th>Fee</th>
                                                <th>Due Date</th>
                                                <th>status</th>
                                                <th>Paid Date</th>
                                                <th>Action</th>

                                            </tr>
                                            </thead>
                                            <tbody id="set_tab_result_weekly">

                                            <tfoot>
                                            <tr>

                                                <th>Fee</th>
                                                <th>Due Date</th>
                                                <th>status</th>
                                                <th>Paid Date</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade tripsbatch" id="pills-updated-cost" role="tabpanel"
                                     aria-labelledby="">
                                    <div class="table-responsive">
                                        <table class="display table table-sm" >
                                            <thead>
                                            <tr>

                                                <th>Trip Id</th>
                                                <th>From</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Account</th>
                                                <th>Payment Method</th>
                                                <th>Cost</th>
                                                <th>Extra</th>
                                                <th>Total Coat</th>
                                                <th>Paid</th>
                                                <th>Status</th>
                                                <th>Update Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($data->trips->where('is_delete',0) as $trip)
                                                @if ($trip->reason != '')
                                                    <tr>

                                                        <td>{{$trip->trip_id}}</td>
                                                        <td>{{$trip->location_from}}<br> To <br> {{$trip->location_to}}
                                                        </td>
                                                        <td>{{$util->format_date($trip->date)}}</td>
                                                        <td>{{$util->time_format($trip->time)}}</td>
                                                        <td>
                                                            @if($trip->payment_method == 'cash')
                                                                <a target="_blank"
                                                                   href="{{url('admin/trip/pay')}}/{{$trip->trip_id}}"
                                                                   class="btn-sm btn-primary w-100">Accept Customer
                                                                    Payment</a>
                                                            @else
                                                                {{$trip->account_number}}
                                                            @endif
                                                        </td>
                                                        <td>{{$trip->payment_method}}</td>

                                                        <td class="cost-td">
                                                            {{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}
                                                            @if (Auth::guard('admin')->user()->role == 'admin')
                                                                <button class="btn cost-update-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-original-title="test"
                                                                        data-bs-target="#exampleModal{{$trip->trip_id}}">
                                                                    <i
                                                                        class="fa fa-pencil"></i></button>
                                                            @endif
                                                            <div class="modal fade" id="exampleModal{{$trip->trip_id}}"
                                                                 tabindex="-1" role="dialog"
                                                                 aria-labelledby="exampleModalLabel{{$trip->trip_id}}"
                                                                 aria-hidden="true">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title"
                                                                                id="exampleModalLabel{{$trip->trip_id}}">
                                                                                Update
                                                                                Cost</h5>
                                                                            <button class="btn-close" type="button"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close"></button>
                                                                        </div>
                                                                        <form method="post"
                                                                              action="{{url('admin/update-cost')}}">

                                                                            <div class="modal-body">
                                                                                @csrf

                                                                                <input hidden class="form-control mb-3"
                                                                                       value="{{$trip->trip_id}}"
                                                                                       name="trip_id"/>

                                                                                <label for="">Please Enter New
                                                                                    Cost</label>
                                                                                <input type="number" name="cost"
                                                                                       required
                                                                                       class="form-control mb-3"
                                                                                       placeholder="$ 00.00">
                                                                                <input type="number" name="extra"
                                                                                       class="form-control mb-3" hidden
                                                                                       value="{{number_format($trip->extra_charges, 2, '.', ',')}}">
                                                                                <label for="">Please Enter
                                                                                    Reason</label>
                                                                                <textarea name="reason"
                                                                                          class="form-control" required
                                                                                          placeholder="Enter Here"></textarea>

                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button class="btn btn-dark"
                                                                                        type="button"
                                                                                        data-bs-dismiss="modal">Close
                                                                                </button>
                                                                                <button class="btn btn-primary"
                                                                                        type="submit">Save
                                                                                </button>
                                                                            </div>
                                                                        </form>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="extra-td">
                                                            {{number_format($trip->extra_charges, 2, '.', ',')}}
                                                            @if (Auth::guard('admin')->user()->role == 'admin')
                                                                <button class="btn extra-update-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-original-title="test"
                                                                        data-bs-target="#extraModal{{$trip->trip_id}}">
                                                                    <i
                                                                        class="fa fa-pencil"></i></button>
                                                            @endif
                                                            <div class="modal fade" id="extraModal{{$trip->trip_id}}"
                                                                 tabindex="-1" role="dialog"
                                                                 aria-labelledby="extraModalLabel{{$trip->trip_id}}"
                                                                 aria-hidden="true">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title"
                                                                                id="extraModalLabel{{$trip->trip_id}}">
                                                                                Update
                                                                                Extra Charges</h5>
                                                                            <button class="btn-close" type="button"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close"></button>
                                                                        </div>
                                                                        <form method="post"
                                                                              action="{{url('admin/update-charges')}}">

                                                                            <div class="modal-body">
                                                                                @csrf

                                                                                <input hidden class="form-control mb-3"
                                                                                       value="{{$trip->trip_id}}"
                                                                                       name="trip_id"/>

                                                                                <label for="">Please Enter New
                                                                                    Charges</label>
                                                                                <input type="number" name="extra"
                                                                                       required
                                                                                       class="form-control mb-3"
                                                                                       placeholder="$ 00.00">
                                                                                <input type="number" name="cost"
                                                                                       class="form-control mb-3" hidden
                                                                                       value="{{number_format($trip->trip_cost - $trip->extra_charges, 2, '.', ',')}}">
                                                                                <label for="">Please Enter
                                                                                    Reason</label>
                                                                                <textarea name="reason"
                                                                                          class="form-control" required
                                                                                          placeholder="Enter Here"></textarea>

                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button class="btn btn-dark"
                                                                                        type="button"
                                                                                        data-bs-dismiss="modal">Close
                                                                                </button>
                                                                                <button class="btn btn-primary"
                                                                                        type="submit">Save
                                                                                </button>
                                                                            </div>
                                                                        </form>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{$trip->trip_cost}}</td>
                                                        <td>{{number_format($trip->total_paid, 2, '.', ',')}}</td>
                                                        <td>
                                                            {{$trip->status}}
                                                        </td>
                                                        <td>{{$trip->reason}}</td>
                                                    </tr>
                                            @endif

                                            @endforeach
                                            <tfoot>
                                            <tr>
                                                <th>Trip Id</th>
                                                <th>From</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Account</th>
                                                <th>Payment Method</th>
                                                <th>Cost</th>
                                                <th>Extra</th>
                                                <th>Total Coat</th>
                                                <th>Paid</th>
                                                <th>Status</th>
                                                <th>Update Reason</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade tripsbatch" id="pills-from_customer-cost" role="tabpanel"
                                     aria-labelledby="">
                                    <div class="table-responsive" id="set_tab_result_from_customer">

                                    </div>
                                </div>

                            </div>
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
                    <h5 class="modal-title" id="exampleModalLabel">Update Fee</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.change-driver-fee')}}" method="post">

                    <div class="modal-body">
                        @csrf
                        <label for="">Weekly Fee</label>
                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                        <input type="text" class="form-control" name="fee" placeholder="Please Enter new fee here"
                               value="{{$data->fee_amount->fee}}">

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="plateModal" tabindex="-1" role="dialog" aria-labelledby="plateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="plateModalLabel">Add Plate</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.change-driver-plate')}}" method="post">

                    <div class="modal-body">
                        @csrf
                        <label for="">Add Plate</label>
                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                        <input type="text" class="form-control" name="plate" placeholder="Please Enter Plate Number Here"
                               value="{{$data->plate}}">

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="settelModal" tabindex="-1" role="dialog" aria-labelledby="settelModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settelModalLabel">Settlement</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">

                    <div class="modal-body">
                        @csrf
                        <label for="">Weekly Balance</label>
                        <input hidden value="{{$data->driver_id}}" name="driver_id"/>
                        <input type="text" class="form-control" name="weekly-balance" value="500">
                        <label for="">Weekly Fee</label>
                        <input type="text" class="form-control mb-3" name="weekly-fee" value="90">
                        <button class="btn btn-primary">Weekly fee Pay With cash</button>
                        <button class="btn btn-primary">Weekly fee Pay from Balance</button>
                        <h5>Cupons</h5>
                        <label for="">First Cupon</label>
                        <input type="number" class="form-control" value="2" placeholder="Enter cupon Quantity">
                        <label for="">Second Cupon</label>
                        <input type="number" class="form-control" value="3" placeholder="Enter cupon Quantity">

                        <h5>Summary</h5>
                        <p>Balanc: <span>500</span> </p>
                        <p>Fee: <span>90</span> </p>
                        <p>First Cupon price: <span>2 x 9 = 18</span> </p>
                        <p>Second Cupon price: <span>3 x 8 = 24</span> </p>
                        <p>Grand Total = 452</p>

                        <label for="">Clearing Amount</label>
                        <input type="text" class="form-control" name="" value="$452" id="">
                        <label for="">Added By</label>
                        <input type="text" class="form-control" readonly value="User name who is logged in">
                        <label for="">Note</label>
                        <textarea class="form-control" name="" id="" placeholder="Reason">Admin can reduce clearing amount but he will put reason here why he is reduicing </textarea>
                        <h5>Please Choose Payment Method</h5>
                        
                        <input type="radio" value="cheque" name="payment-method" id="cheque">
                        <label for="cheque">cheque</label>
                        
                        <input type="radio" name="payment-method" value="cash" id="cash">
                        <label for="cash">Cash</label>



                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Settel</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div id="loading-message" style="display: none;">
    Loading...
</div>

@endsection

@section('js')
    <script>

        function get_driver_total() {
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();

            $('.onloadmakeblur').addClass('blurred');

            $.ajax({
                url: "{{url('admin/ajax-driver')}}",
                method: "GET",
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    driver_id: "{{$data->driver_id}}"
                },
                success: function (response) {

                    $('#total_trip').text(response.total_trip);
                    $('#total_earnings').text('$' + response.total_earnings);
                    // $('#total_recived').text('$' + response.total_recived);

                    $('#total_lastw').text('$' + response.lastw);
                    // $('#paid_unpaid').text('Total Trip = ' + response.total_last_week_trip + ', Paid $' + response.paidlastweek + ', Unpaid $' + response.unpaidlastweek);
                    $('#paid_unpaid').text('');


                    if(response.lastw > 0){
                        $('#priority').val('last_week');
                    }else if(response.currentw > 0){
                        $('#priority').val('current_week');

                    }else{
                        $('#priority').val('default');

                    }
                    $('#total_gocab_paid').text('$' + response.gocab_paid);
                    if (response.gocab_paid > 0) {

                        $('#pay_to_driver_btn').show();
                      } else {

                       $('#pay_to_driver_btn').hide();
                         }

                     $('#total_currentw').text('$' + Math.round(response.currentw));
                    // $('#paid_unpaid_current').text('Total Trip = ' + response.total_Current_week_trip + ', Paid $' + response.paidCurrentweek + ', Unpaid $' + response.unpaidCurrentweek);
                    $('#paid_unpaid_current').text('');
                    $('#total_deductions').text('$'+response.deductions);

                    $('#total_description').html(response.description);
                    if (response.gocab_paid >= 0) {
                        $('#balance_heading').html('Amount owed to driver');
                    } else {
                        $('#balance_heading').html('Amount owed to CarSafe');
                    }

                    $('.onloadmakeblur').removeClass('blurred');

                },
                error: function (xhr) {

                }
            });
        }


        function get_trips(type, fromm, too, driver_id, show_id) {

            $('.showed_ajax_trips').html('Loading...');
            $.ajax({
                url: "{{url('admin/ajax-trips')}}",
                method: "GET",
                data: {
                    from_date: fromm,
                    to_date: too,
                    driver: driver_id,
                    type: type,
                    show_id: show_id
                },

                success: function (response) {
                    $('#' + show_id).html(response);
                    $('#trips_ajax').DataTable({
                        order: [],
                        columns: [
                            {data: 'location_from'},
                            {data: 'location_to'},
                            {data: 'date', orderable: false},
                            {data: 'time', orderable: false},
                            {data: 'trip_cost'},
                            {data: 'status'},

                        ],
                    });

                },

                error: function (xhr) {

                }
            });
        }

        $(document).ready(function () {
         $('#deduction_history_btn').click(function(){

               $('#deduction_history_div').toggle();
         });

        // diver settlment divs functions
         $('#driver_settelment_div_btn').click(function(){
            $('#pay_method_div').toggle();
            $('#from_driver').hide();
            $('#to_driver').hide();
            $('#weekly_from_balance').hide();

         });
         $('#show_from_driver_form').click(function () {
            $('#weekly_from_balance').hide();
            $('#from_driver').toggle();
            $('#to_driver').hide();


         });
         $('#show_to_driver_form').click(function () {
            $('#weekly_from_balance').toggle();
            $('#to_driver').hide();
            $('#from_driver').hide();
         });

         $('#show_to_pay_driver_balance').click(function(){
            $('#weekly_from_balance').hide();
            $('#to_driver').toggle();
            $('#from_driver').hide();

         });

        // driver settlment

            $('#driver_info_div_btn').click(function () {
                $('#driver_info_div').toggle();
            });
            loadImages();
            get_trips('all',"{{now()->format('Y-m-d')}}","{{now()->format('Y-m-d')}}","{{$data->driver_id}}", "pills-trips");
            $('.showajax-trips').click(function () {

                let type = $(this).data('type');
                let id = $(this).data('idd');

                get_trips(type,"{{now()->format('Y-m-d')}}","{{now()->format('Y-m-d')}}", "{{$data->driver_id}}", id);

            });


            $('#from_date, #to_date').on('change', function () {
                get_driver_total();
            });
            get_driver_total();

            $('#tripsbatch').dataTable({});


            var dynamic_columns =
                [
                    {
                        class: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: '',
                        render: function(){
                            return '<i class="fa fa-plus-square" aria-hidden="trued"></i>'
                        }
                    },
                    {data: 'id', name: 'id'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'amount', name: 'amount'},

                ];


            const batch_payments = $('#batch_payments').DataTable({
                processing: true,
                serverSide: true,
                dom: "Blfrtip",
                // scrollX: '100%',
                autoWidth: true,
                responsive: true,
                "lengthMenu": [ [10, 25, 50,100, -1], [10, 25, 50,100, "All"] ],

                ajax: {
                    url : "{{url('admin/driver')}}/{{$data->id}}",
                    method: "GET",
                    data: {
                        tab: 'batch',
                        // filterDate : $('#filter_date').val();
                    },


                },
                columns: dynamic_columns,


                language: {
                    'paginate': {
                        'previous': '<i class="fas fa-arrow-left" ></i>',
                        'next': '<i class="fas fa-arrow-right"></i>'
                    }
                },

            });

            const detailRows = [];

            batch_payments.on('click', 'tbody td.dt-control', function () {
                let tr = event.target.closest('tr');
                let row = batch_payments.row(tr);
                let idx = detailRows.indexOf(tr.id);

                if (row.child.isShown()) {
                    tr.classList.remove('details');
                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                }
                else {
                    tr.classList.add('details');
                    row.child(bookdetail(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.id);
                    }
                }
            });

            batch_payments.on('draw', () => {
                detailRows.forEach((id, i) => {
                    let el = document.querySelector('#' + id + ' td.dt-control');

                    if (el) {
                        el.dispatchEvent(new Event('click', { bubbles: true }));
                    }
                });
            });


            $('#pills-tripsbatch-tab1').on('click', function(){
                batch_payments.draw();
            });
        });

        function bookdetail(data) {


            var div = '';

            div +=  '<table class="table">' +
                '<thead><tr>' +
                '<th>Driver Id</th>' +
                '<th>Trip Id</th>' +
                '<th>Date</th>' +
                '<th>Trip</th>' +
                '</tr></thead>' +
                '<tbody>';

            data.PTrips.forEach(element => {

                let formattedDate = new Date(element.created_at).toLocaleDateString('en-GB'); // DD/MM/YYYY format

                div += '<tr>' +

                    '<td>' + element.driver_id + '</td>' +
                    '<td>' + element.trip_id+'</td>' +
                    '<td>' + formattedDate + '</td>' +
                    '<td>' + element.trip_cost + '</td>' +
                    '</tr>';

            });


            div +=  '</tbody>' +
                '</table>';


            return div;
        }

        function driver_fetch_tabs_data(tabb){


                const loader = $("#loading-message");
                $.ajax({
                    url: "{{url('admin/driver')}}/{{$data->id}}",
                    method: "GET",
                    data: {
                        tab: tabb
                    },
                    beforeSend: function () {
                        // Display the loader
                        loader.show();
                    },
                    success: function (response) {
                        $('#set_tab_result_' + tabb).html(response);

                        if (tabb == 'from_customer') {
                            $('#from_customer').DataTable({});
                        }
                    },
                    error: function (xhr) {

                    },
                    complete: function () {
                        // Hide the loader after the request completes (success or error)
                        loader.hide();
                    }
                });



        }

        $('.remove-history').click(function () {

            var idd = $(this).data('id');
            var roww = $('#row_'+idd);
            $('.reason').addClass('hide');
            $('.confirm').addClass('hide');
             roww.find('.reason').removeClass('hide');
            roww.find('.confirm').removeClass('hide');

        });

        $('.confirm').click(function () {

            $('.reason').addClass('hide');
            $('.confirm').addClass('hide');
            var idd = $(this).data('id');
            var roww = $('#row_'+idd);
            var reason = roww.find('.reason').val();

            $.ajax({
                url: "{{url('admin/deduction-history-remove')}}",
                method: "GET",
                data: {
                    id: idd,
                    reason:reason
                },
                success: function (response) {

                    roww.find('.history_descr').html(reason);
                    roww.find('.remove-history').addClass('hide');
                },
                error: function (xhr) {

                },
                complete: function () {
                    // Hide the loader after the request completes (success or error)
                    loader.hide();
                }
            });

        });


    </script>
     <script>
  // Function to add a new input field for the next image
  function addInputField() {
    // Get the container where inputs are added
    const container = document.getElementById('input-container');
    const fileList = document.getElementById('file-list');

    // Create a new input field
    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.name = 'images[]';
    newInput.accept = 'image/*';
    newInput.required = true;
    newInput.classList.add('form-control','mb-3');
    newInput.onchange = addInputField;

    // Add the new input field to the container
    container.appendChild(newInput);

    // Display the selected file in the list
    const fileName = event.target.files[0].name;
    const li = document.createElement('li');
    li.textContent = fileName;
    fileList.appendChild(li);

    // Disable the current input field so the user cannot modify it
    event.target.disabled = true;
  }

  // add images
  function submitForm() {
    const formData = new FormData(document.getElementById('imageForm'));

    $.ajax({
      url: '{{url("admin/add_images")}}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        alert('Images uploaded successfully!');

        loadImages();
        resetInputFields();
      },
      error: function(xhr, status, error) {
        alert('An error occurred while uploading images.');
        console.log(xhr.responseText);
      }
    });
  }
  // reset form
  function resetInputFields() {
    const container = document.getElementById('input-container');




    // Remove all input fields
    container.innerHTML = '';

    // Add one empty input field
    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.name = 'images[]';
    newInput.accept = 'image/*';
    newInput.required = true;
    newInput.classList.add('form-control','mb-3');
    newInput.onchange = addInputField;

    // Append the single empty input field to the container
    container.appendChild(newInput);
}

  // show images
  function loadImages() {
    $('#uploaded-images').empty();
    $.get('{{url("admin/get-uploaded-images/")}}/{{$data->driver_id}}', function(images) {
      images.forEach(image => {


        $('#uploaded-images').append(`
          <div class="image-wrapper col-md-4 mt-3" data-id="${image.id}">
            <img src="{{ asset('storage/${image.name}') }}" class="img-fluid" alt="Image">
            <br>
            <button class="btn btn-danger mt-3" onclick="deleteImage(${image.id})">Delete</button>
          </div>
        `);
      });
    });
  }

  // delete image
  function deleteImage(id) {
    $.ajax({
        url: `{{ url('admin/delete-image') }}/${id}`,

      type: 'GET',
      success: function(response) {
        alert('Image deleted successfully.');
        $(`.image-wrapper[data-id="${id}"]`).remove(); // Remove the image from the display
      },
      error: function(xhr, status, error) {
        alert('An error occurred while deleting the image.');
        console.log(xhr.responseText);
      }
    });
  }

</script>



@endsection

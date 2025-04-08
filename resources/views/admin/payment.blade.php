@extends('admin.admin-layout')

@section('content')

@php

    $util = new \App\Utils\dateUtil();
    use App\Models\Driver;

@endphp
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
                    <h5 class="pull-left">Payments</h5>
                </div>
                <div class="card-body">
                    <div class="tabbed-card">
                        <ul class="pull-right nav nav-pills nav-primary" id="pills-clrtab1" role="tablist">
                            <li class="nav-item"><a
                                    class="nav-link {{request()->tab == 'all' || !isset(request()->tab) ? 'active' : ''}}"
                                    id="pills-all-tab1" href="{{url('admin/payments')}}?tab=all" role="tab"
                                    aria-controls="pills-all" aria-selected="true" data-bs-original-title=""
                                    title="">All Payments</a></li>

                            <li class="nav-item"><a class="nav-link {{request()->tab == 'from_driver' ? 'active' : ''}}"
                                    id="pills-fromdriver-tab1" href="{{url('admin/payments')}}?tab=from_driver"
                                    role="tab" aria-controls="pills-fromdriver" aria-selected="false"
                                    data-bs-original-title="" title="">Payments from driver</a></li>

                            <li class="nav-item"><a class="nav-link {{request()->tab == 'to_driver' ? 'active' : ''}}"
                                    id="pills-todriver-tab1" href="{{url('admin/payments')}}?tab=to_driver" role="tab"
                                    aria-controls="pills-todriver" aria-selected="false" data-bs-original-title=""
                                    title="">Payments to drivers</a></li>

                            <li class="nav-item"><a class="nav-link {{request()->tab == 'weekly' ? 'active' : ''}}"
                                    id="pills-weekly-tab1" href="{{url('admin/payments')}}?tab=weekly" role="tab"
                                    aria-controls="pills-weekly" aria-selected="false" data-bs-original-title=""
                                    title="">Weekly Fees</a></li>
                        </ul>


                        <div class="tab-content" id="pills-clrtabContent1">

                            <div class="tab-pane fade {{request()->tab == 'all' || !isset(request()->tab) ? 'active show' : ''}} "
                                id="pills-all" role="tabpanel" aria-labelledby="pills-clrhome-tab1">
                                <div class="table-responsive">
                                    <table class="display" id="advance-1">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Driver Username</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th>Description</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $payment)

                                                                                        <tr>
                                                                                            <td>{{$payment->id}}</td>
                                                                                            <td>{{$payment->driver_id}}</td>
                                                                                            @php
                                                                                                $driver = Driver::where('driver_id', $payment->driver_id)->first();
                                                                                            @endphp
                                                                                            <td>
                                                                                                @if ($driver)
                                                                                                {{ $driver->username  }}
                                                                                                @endif

                                                                                            </td>
                                                                                            <td>{{$payment->trip_id}}</td>
                                                                                            <td>
                                                                                                @if($payment->user_type == 'admin' && $payment->type == 'credit')
                                                                                                    From Driver
                                                                                                @elseif($payment->user_type == 'admin' && $payment->type == 'debit')
                                                                                                    To Driver
                                                                                                @elseif($payment->user_type == 'driver' && $payment->type == 'debit')
                                                                                                    Auto Weekly Fee
                                                                                                @else
                                                                                                    From customer
                                                                                                @endif
                                                                                            </td>
                                                                                            <td>{{$util->format_date($payment->created_at)}}</td>
                                                                                            <td>{{$util->time_format2($payment->created_at)}}</td>
                                                                                            <td>{{$payment->amount}}</td>
                                                                                            <td>{{$payment->edited_prices_by}}</td>

                                                                                        </tr>

                                            @endforeach
                                        </tbody>
                                        @include('partials.paginations', ['data_links' => $payments, 'colspan' => 4])

                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{request()->tab == 'from_driver' ? 'active show' : ''}}"
                                id="pills-fromdriver" role="tabpanel" aria-labelledby="pills-clrprofile-tab1">
                                <div class="table-responsive">
                                    <table class="display" id="advance-2">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Driver Username</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th>Description</th>


                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($from as $payment)
                                                                                        <tr>
                                                                                            <td>{{$payment->id}}</td>
                                                                                            <td>{{$payment->driver_id}}</td>
                                                                                            @php
                                                                                                $driver = Driver::where('driver_id', $payment->driver_id)->first();
                                                                                            @endphp

                                                                                            <td> @if ($driver)

                                                                                                {{ $driver->username }}
                                                                                            @else
                                                                                                N/A
                                                                                            @endif
                                                                                            </td>
                                                                                            <td>{{$payment->trip_id}}</td>
                                                                                            <td>From Driver
                                                                                            </td>
                                                                                            <td>{{$util->format_date($payment->created_at)}}</td>
                                                                                            <td>{{$util->time_format2($payment->created_at)}}</td>
                                                                                            <td>{{$payment->amount}}</td>
                                                                                            <td>{{$payment->edited_prices_by}}</td>


                                                                                        </tr>
                                            @endforeach
                                        </tbody>
                                        @include('partials.paginations', ['data_links' => $from, 'colspan' => 4])

                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{request()->tab == 'to_driver' ? 'active show' : ''}}"
                                id="pills-todriver" role="tabpanel" aria-labelledby="pills-clrcontact-tab1">
                                <div class="table-responsive">
                                    <table class="display" id="advance-3">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Driver Username</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th>Description</th>


                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($to as $payment)
                                                                                        <tr>
                                                                                            <td>{{$payment->id}}</td>
                                                                                            <td>{{$payment->driver_id}}</td>
                                                                                            @php
                                                                                                $driver = Driver::where('driver_id', $payment->driver_id)->first();
                                                                                            @endphp
                                                                                            <td>{{ $driver->username}}</td>
                                                                                            <td>{{$payment->trip_id}}</td>
                                                                                            <td>
                                                                                                To Driver
                                                                                            </td>
                                                                                            <td>{{$util->format_date($payment->created_at)}}</td>
                                                                                            <td>{{$util->time_format2($payment->created_at)}}</td>
                                                                                            <td>{{$payment->amount}}</td>
                                                                                            <td>{{$payment->edited_prices_by}}</td>
                                                                                        </tr>
                                            @endforeach
                                        </tbody>
                                        @include('partials.paginations', ['data_links' => $to, 'colspan' => 4])

                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{request()->tab == 'weekly' ? 'active show' : ''}}"
                                id="pills-weekly" role="tabpanel" aria-labelledby="pills-clrcontact-tab1">
                                <div class="table-responsive">
                                    <table class="display" id="weekly_fees">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Driver Id</th>
                                                <th>Driver Username</th>
                                                <th>Trip Id</th>
                                                <th>Payment Type</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($weekly as $payment)
                                                                                        <tr>
                                                                                            <td>{{$payment->id}}</td>
                                                                                            <td>{{$payment->driver_id}}</td>
                                                                                            @php
                                                                                                $driver = Driver::where('driver_id', $payment->driver_id)->first();
                                                                                            @endphp
                                                                                            <td>{{ $driver->username}}</td>
                                                                                            <td>{{$payment->trip_id}}</td>
                                                                                            <td>
                                                                                                weekly
                                                                                            </td>
                                                                                            <td>{{$util->format_date($payment->created_at)}}</td>
                                                                                            <td>{{$util->time_format2($payment->created_at)}}</td>
                                                                                            <td>{{$payment->amount}}</td>

                                                                                        </tr>
                                            @endforeach
                                        </tbody>
                                        @include('partials.paginations', ['data_links' => $weekly, 'colspan' => 4])

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
    var weekly_fees = $('#weekly_fees').DataTable({
        pageLength: 50
    });

</script>
@endsection

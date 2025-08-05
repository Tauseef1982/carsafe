@extends('customer.layouts.yajra')
@php

$util = new \App\Utils\dateUtil();

@endphp
@section('css')
<style>
    .page-wrapper .page-body-wrapper .page-title {
    margin: 0 !important;
    background-color: none !important;
    border-bottom: 1px solid #D9D9E1 !important;
    box-shadow: none !important;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card mt-5 p-4 shadow rounded-4">
        <div class="card-body">
      <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3 class="f-28 fw-bold">Complaint</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Complaints</li>
                        <li class="breadcrumb-item text-primary">Detail</li>

                      </ol>
                    </div>
                  </div>
                </div>
 <table class="table table-bordered mt-4 table-striped">
                <tbody>
                    <tr>
                        <th width="20%">Trip ID</th>
                        <td>{{ $complaint->trip_id }}</td>
                    </tr>
                    <tr>
                        <th>Complaint</th>
                        <td>{{ $complaint->complaint }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ $util->format_date($complaint->created_at) }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ $complaint->status }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $complaint->note }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <!-- Add more fields if needed -->
    </div>
</div>
@endsection

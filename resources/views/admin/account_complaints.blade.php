@extends('admin.admin-layout')
@php

$util = new \App\Utils\dateUtil();

@endphp
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
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
               
                <div class="card">
                  <div class="card-header">
                    <h5 class="pull-left">Account Complaints</h5>
                  </div>
                  <div class="card-body">
                    <div class="tabbed-card">
                      <ul class="pull-right nav nav-pills nav-primary" id="pills-clrtabinfo" role="tablist">
                        <!-- <li class="nav-item"><a class="nav-link active" id="pills-clrhome-tabinfo" data-bs-toggle="pill" href="#pills-clrhomeinfo" role="tab" aria-controls="pills-clrhome" aria-selected="true">Postpaid Accounts</a></li> -->
                        <!-- <li class="nav-item"><a class="nav-link" id="pills-clrprofile-tabinfo" data-bs-toggle="pill" href="#pills-clrprofileinfo" role="tab" aria-controls="pills-clrprofile" aria-selected="false">Prepaid Accounts</a></li> -->
                      
                      </ul>
                      <div class="tab-content" id="pills-clrtabContentinfo">
                        <div class="tab-pane fade show active" id="pills-clrhomeinfo" role="tabpanel" aria-labelledby="pills-clrhome-tabinfo">
                        <div class="table-responsive">
                            <table class="table table-sm" id="advance-1">
                                <thead>
                                <tr>
                                    <th>Account ID</th>
                                    <th>Trip ID</th>
                                    <th>Complaint</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Username</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                     </tr>
                                </thead>
                                <tbody>
                                    @foreach ($complaints as $complaint)
                                    <tr>
                                    <th>{{$complaint->account_id}}</th>
                                    <th>{{$complaint->trip_id}}</th>
                                    <th>{{$complaint->complaint}}</th>
                                    <th>{{$util->format_date($complaint->created_at)}}</th>
                                    <th>{{$complaint->status}}</th>
                                    <th>{{$complaint->username}}</th>
                                    <th>{{$complaint->note}}</th>
                                    <th><a href="{{url('admin/update_complaint_status')}}/{{$complaint->id}}" class="btn btn-primary">Change Status</a></th>
                                     </tr> 
                                    @endforeach
                                
                             
                                
                             
                             
                            </tbody>
                            <tfoot>
                            <tr>
                                    <th>Account ID</th>
                                    <th>Trip ID</th>
                                    <th>Complaint</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Username</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                    
                                     </tr>
                            </tfoot>
                               

                            </table>
                        </div>
                        </div>
                        <div class="tab-pane fade" id="pills-clrprofileinfo" role="tabpanel" aria-labelledby="pills-clrprofile-tabinfo">
                        <div class="table-responsive">
                            <table class="table table-sm" id="advance-2">
                                <thead>
                                <tr>
                                    <th>Account id</th>
                                    <th>Account Name</th>
                                    
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                     </tr>
                                </thead>
                                <tbody>
                                 <tr>
                                 <td>1502</td>
                                    <td>Jonh Doe</td>
                                    
                                   
                                    <td>$10</td>
                                   
                                    <td>10-24-2024</td>
                                   
                                  <td>Card</td>
                                 </tr> 
                                
                                
                             
                            </tbody>
                            <tfoot>
                            <tr>
                            <tr>
                                    <th>Account id</th>
                                    <th>Account Name</th>
                                  
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                     </tr> 
                            </tfoot>
                               

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

@endsection


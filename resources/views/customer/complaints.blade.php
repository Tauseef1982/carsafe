@extends('customer.layouts.yajra')
@php

$util = new \App\Utils\dateUtil();

@endphp
@section('content')
    <div class="container-fluid">
                <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3>Complaints</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Complaints</li>

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
                          <table class="display w-100">
                            <thead class="bg-dark">
                              <tr class="text-primary">
                                
                                    <th class="p-3">Trip ID</th>
                                    <th>Complaint</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                   

                              </tr>
                            </thead>
                            <tbody>
                            @foreach ($complaints as $complaint)
                                    <tr>
                                    
                                    <th class="p-3">{{$complaint->trip_id}}</th>
                                    <th>{{$complaint->complaint}}</th>
                                    <th>{{$util->format_date($complaint->created_at)}}</th>
                                    <th>{{$complaint->status}}</th>
                                  
                                   
                                     </tr> 
                                    @endforeach

                            </tbody>

                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Zero Configuration  Ends-->
            </div>
           </div>
          </div>

@endsection
@section('js')

    <script>
        $(document).ready(function(){


          

        });





    </script>


@endsection

@php
    use Carbon\Carbon;

    // Define the time
    $time = $trip->time;
    $formattedDate = Carbon::parse($trip->date)->format('m/d/Y');
    $formattedTime = Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
@endphp
@extends('layout')

@section('content')
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="">Trip Details</a></li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid">
            <div class="row size-column">
              <div class="col-xl-3 risk-col xl-100 box-col-12">
                <div class="card total-users">
                  <div class="card-header card-no-border">
                    <h5>Trip Details</h5>
                   
                  </div>
                  <div class="card-body pt-0 ">
                
                    <div class="row">
                        <div class="col-6 b-primary">
                           <h5>Trip Id</h5>
                        </div>
                        <div class="col-6 pt-2 b-primary"> 
                          <h6>{{$trip->trip_id}}</h6>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                         <h5>Location From</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                        <h6>{{$trip->location_from}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Location To</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>{{$trip->location_to}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Date</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6 class="notranslate">{{$formattedDate}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Time</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6 class="notranslate">{{$formattedTime}}</h6>
                       </div>
                    </div>
                     <!-- <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Passenger Phone</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>{{$trip->passenger_phone}}</h6>
                       </div>
                    </div>  -->
                  
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Estimated Cost</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>${{$trip->estimated_cost}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Extra Charges</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>${{$trip->extra_charges}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Total Bill</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>${{$trip->trip_cost}}</h6>
                       </div>
                    </div>
                    
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>CarSafe Payment ID</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>{{$trip->gocab_payment_id}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Amount Paid to drive</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>${{$trip->driver_paid}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Amount paid to CarSafe</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>${{$trip->gocab_paid}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Payment method</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>{{$trip->payment_method}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                            <h5>Trip Account Number</h5>
                      </div>
                        <div class="col-6 pt-2 b-primary"> 
                            <h6>{{$trip->account_number}}</h6>
                       </div>
                    </div>
                    <div class="row ">
                        <div class="col-6 b-primary">
                       
                          
                           
                           
                           <h5>Strip Confirmation Number</h5>

                        </div>
                        <div class="col-6 pt-2 b-primary"> 
                        
                          
                           
                           
                           <h6>{{$trip->strip_id}}</h6>
                        </div>
                    </div>
                   

                 

                 
                    
                  </div>
                </div>
               
              </div>
              
            </div>
          </div>
          <!-- Container-fluid Ends-->
       @endsection
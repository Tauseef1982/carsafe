@extends('customer.layouts.yajra')

@section('css')
<style>
.page-wrapper .page-body-wrapper .page-title {
    margin: 0 !important;
    background-color: none !important;
    border-bottom: 1px solid #D9D9E1 !important;
    box-shadow: none !important;
}
.card .card-body {
     padding: 13px; 
  
}
.btn {
    padding: 0.375rem 0.79rem !important;
    width: 100%;
}
.img-2{
      width: 27px;
    height: 27px;
}
  #dashboard {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}
  #dashboard thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
#dashboard thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}
#dashboard_length{
  display: none;
}
#dashboard_filter{
  display: none;
}
   .even{
    background-color: #FEEEEA !important;
}
.even > .sorting_1{
    background-color: #FEEEEA!important;
} 
</style>
@endsection

@section('content')
<div class="card total-users">
   <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3 class="f-28 fw-bold">Dashboard</h3>
                    </div>
                 
                  </div>
                  
                </div>
        <div class="card-body pt-5 ">
          
          <div class="row">
          <!-- <div class="col-xl-4 col-lg-12 xl-50 morning-sec box-col-12">
                <div class="card profile-greeting">
                  <div class="card-body pb-0">
                    <div class="media">
                      <div class="media-body">
                        <div class="greeting-user">
                          <h4 class="f-w-600 font-primary" id="greeting">Good Morning </h4>
                          <span><p>{{ $account->f_name }}</p></span>
                          <p>Whats going on</p>

                        </div>
                      </div>
                      <div class="badge-groups">
                        <div class="badge f-10"><span ></span></div>
                      </div>
                    </div>
                    <div class="cartoon"><img class="img-fluid" src="../assets/images/dashboard/cartoon.png" alt=""></div>
                  </div>
                </div>
              </div> -->
            <!-- <div class="col-md-6 pt-5">
                <div class="card p-5">
              <div class="bg-primary card p-10">
                <h5 class=" text-center font-dark">Total Trips</h5>
                <h6 class=" text-center font-dark" id="total_trips">...loading</h6>

              </div> -->
              <!-- <div class="bg-dark card p-10">
                <h5 class="font-dark text-center txt-primary">Balance</h5>
                <h6 class="font-dark text-center txt-primary" id="account_balance">...loading</h6>
                <button class="btn text-primary" type="button" data-bs-toggle="modal" data-bs-target="#myModal">Add More Balance</button>

              </div> -->
                
              <div class="col-lg-3 col-md-4">
                <div class="card">
                  <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-16 f-w-700">Total Balance</h3>
                       <img src="{{ asset('assets/images/Frame 25.png') }}" alt="">
                   </div>
                   <div class="d-flex justify-content-between">
                       <h3 class="f-32 f-w-700 text-primary" id="account_balance">$0.00</h3>
                       <button class="btn btn-transparent btn-xs text-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                        <i class="fa fa-plus"></i>
                        Add More
                       </button>
                   </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-4">
                <div class="card">
                   <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-16 f-w-700">Total Trips</h3>
                       <img src="{{ asset('assets/images/Frame 27.png') }}" alt="">
                   </div>
                   <div class="d-flex justify-content-between">
                       <h3 class="f-32 f-w-700 text-primary" id="total_trips">0</h3>
                        <div id="area-spaline"></div>
                       
                   </div>
                  </div>
                  
                </div>
              </div>
              <div class="col-lg-3 col-md-4">
                <div class="card">
                   <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-16 f-w-700">Total Complaints</h3>
                       <img src="{{ asset('assets/images/Frame 29.png') }}" alt="">
                   </div>
                   <div class="d-flex justify-content-between">
                       <h3 class="f-32 f-w-700 text-primary" id="account_complaints">0</h3>
                        <div id="area-spaline1"></div>
                       
                   </div>
                  </div>
                  
                </div>
              </div>
              <div class="col-lg-3 col-md-4">
                <div class="card">
                   <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-16 f-w-700">Total Invoices</h3>
                       <img src="{{ asset('assets/images/Frame 31.png') }}" alt="">
                   </div>
                   <div class="d-flex justify-content-between">
                       <h3 class="f-32 f-w-700 text-primary" id="account_invoices">0</h3>
                        <div id="area-spaline2"></div>
                       
                   </div>
                  </div>
                  
                </div>
              </div>
              

             
              </div>
               
             <h3 class="f-24 fw-bold">Discover Our Features</h3>

              <div class="row mt-4">
                  <div class="col-lg-3 col-md-4">
                <div class="card bg-primary">
                  <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-18 f-w-700">Book a Ride</h3>
                       <img src="{{ asset('assets/images/Frame 25.png') }}" alt="">
                   </div>
                   <div class="">
                       <p class="f-14 f-w-500  ">Safe rides, anytime.</p>
                      <a href="{{ url('customer/book-ride') }}"> <button class="btn btn-sm  text-primary bg-light f-14 fw-bold">
                        Book a Ride 
                                <img src="{{ asset('assets/images/arrow-right-line.png') }}" alt="">

                       </button></a>
                   </div>
                  </div>
                </div>
              </div>

               <div class="col-lg-3 col-md-4">
                <div class="card bg-primary">
                  <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-18 f-w-700">Track Your Rides</h3>
                       <img class="img-fluid img-2" src="{{ asset('assets/images/Frame 40.png') }}" alt="">
                   </div>
                   <div class="">
                       <p class="f-14 f-w-500  ">Track live. Ride safe.</p>
                      <a href=""> <button class="btn btn-sm  text-primary bg-light f-14 fw-bold">
                       Track Your Rides
                                <img src="{{ asset('assets/images/arrow-right-line.png') }}" alt="">

                       </button></a>
                   </div>
                  </div>
                </div>
              </div>

               <div class="col-lg-3 col-md-4">
                <div class="card bg-primary">
                  <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-18 f-w-700">Check Prices</h3>
                       <img src="{{ asset('assets/images/Frame 25.png') }}" alt="">
                   </div>
                   <div class="">
                       <p class="f-14 f-w-500  ">Compare fares instantly.</p>
                      <a href=""> <button class="btn btn-sm  text-primary bg-light f-14 fw-bold">
                        Check Prices
                                <img src="{{ asset('assets/images/arrow-right-line.png') }}" alt="">

                       </button></a>
                   </div>
                  </div>
                </div>
              </div>
               <div class="col-lg-3 col-md-4 ">
                <div class="card bg-primary">
                  <div class="card-body">
                   <div class="d-flex justify-content-between">
                       <h3 class="f-18 f-w-700">Download Invoice</h3>
                       <img class="img-fluid img-2" src="{{ asset('assets/images/Frame 44.png') }}" alt="">
                   </div>
                   <div class="">
                       <p class="f-14 f-w-500  ">Instant invoice. Zero hassle.</p>
                      <a href="{{ url('customer/invoices') }}"> <button class="btn btn-sm  text-primary bg-light f-14 fw-bold">
                     Download Invoice 
                                <img src="{{ asset('assets/images/arrow-right-line.png') }}" alt="">

                       </button></a>
                   </div>
                  </div>
                </div>
              </div>

              </div>

                
                <div class="row mt-3">
                  <div class="col-md-9">
                     <span><h3 class="f-28 fw-bold">Recent Trips</h3></span> 
                  </div>
                  <div class="col-md-3">
                    <a href="{{ url('customer/trips') }}" class="btn btn-transparent text-primary btn-outline-primary btn-sm f-14 f-w-700">View All Trips <span><img src="{{ asset('assets/images/arrow-right-line.png') }}" alt=""></span></a>
                  </div>
                  </div>
                  <div class="row">
      <!-- Zero Configuration  Starts-->
      <div class="col-sm-12 m-0 pt-0">
        <div class="row mb-3 d-none">
          <div class="col-md-3">
      <input type="date" id="from_date" class="form-control">
      </div>
      <div class="col-md-3">
      <input type="date" id="to_date" class="form-control">
      </div>
        </div>
      <div class="">

        <div class="">
        <div class="table-responsive ">
          <table class="display" id="dashboard">
          <thead class="table-header-light" >
            <tr class="">
            <th>Trip ID</th>
            <th>Pin Status</th>
            <th>Driver ID</th>
            <th>From</th>
            <th>To</th>

            <th>Total Cost</th>

            <th>Date</th>
            <th>Time</th>

            <th>Action</th>
            </tr>
          </thead>
          <tbody>


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





          </div>


        </div>
      </div>
       <!-- Modal-->
       <div class="modal fade" id="myModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog" role="document">
          <form action="{{url('customer/pay-to-refill')}}" method="post">
            @csrf
          <div class="modal-content">
        <!-- Close Button -->
        <div class="modal-header bg-orange-g text-white">
          <h5 class="text-white">Add Balance</h5>
          <button class="btn-close  btn-primary text-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <div class="card">
          <div class="animate-widget">

              <div class="">
                <input hidden name="account_id" value="{{$account->account_id}}">
                <input hidden name="refill_method" value="card">
                <input class="card-number my-custom-class form-control" name="to_refill" placeholder="Amount" value="">


              </div>


            </div>

          </div>
          </div>

              <div class="modal-footer ">
                  <input type="submit" class="btn btn-dark mt-3 ms-auto bg-orange-g text-white" value="Add Balance">

              </div>
          </form>
      </div>
        </div>
      </div>
      </div>

@endsection
@php
 $account_id = $account->account_id;
@endphp
@section('js')

    <script>
        function loadTrips(from_date = '', to_date = '') {
        if ($.fn.DataTable.isDataTable('#dashboard')) {
      $('#dashboard').DataTable().destroy();
    }
       $('#dashboard').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{url('customer/trips')}}",
          type: 'GET',
          data: function (d) {
      d.account_id = "{{$account_id}}";
      d.from_date = from_date;
      d.to_date = to_date;
    }
        },
         initComplete: function () {

         $('#dashboard_length').appendTo('#datatable_length_holder');
         $('#dashboard_filter').appendTo('#datatable_search_holder');

         const $searchInput = $('#dashboard_filter input');


         const customSearch = `
  <div class="input-group mt-3 mb-3">
    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
    <input type="search" id="custom-search" class="form-control border-start-0" placeholder="Search Trips" />
  </div>
  `;


         $('#datatable_search_holder').html(customSearch);


         $('#custom-search').on('keyup', function () {
           $('#dashboard').DataTable().search(this.value).draw();
         });



         $('#dashboard_filter label').addClass('w-100');
         $('#dashboard_length label').addClass('float-end');
         },
       
       
        columns: [
          {data: 'trip_id', name: 'trip_id'},
          {data: 'cube_status', name: 'cube_status'},
          {data: 'driver_id', name: 'driver_id'},
          {data: 'location_from', name: 'location_from'},
          {data: 'location_to', name: 'location_to'},

          {data: 'trip_cost', name: 'trip_cost'},

          {data: 'date', name: 'date'},
          {data: 'time', name: 'time'},

          {data: 'action', name: 'action'},


        ], 
        order: [[1, 'desc']]
      }
        );
      };
       let today = new Date().toISOString().split('T')[0];
    let lastWeek = new Date();
    lastWeek.setDate(lastWeek.getDate() - 7);
    let lastWeekStr = lastWeek.toISOString().split('T')[0];

    $('#from_date').val(lastWeekStr);
    $('#to_date').val(today);
     loadTrips(lastWeekStr, today);

      $('#to_date').change(function () {
      let from = $('#from_date').val();
      let to = $('#to_date').val();
      loadTrips(from, to);
    });
     

        function get_account_summary() {
            // var fromDate = $('#from_date').val();
            // var toDate = $('#to_date').val();
            var account_id = "{{$account->account_id}}";

            $.ajax({
                url: "{{url('customer/index')}}",
                method: "GET",
                data: {
                    // from_date: fromDate,
                    // to_date: toDate,
                    account_id: account_id
                },
                success: function (response) {

                    $('#total_trips').text(response.total_trips);
                    $('#account_balance').text('$' + response.total_payments);
                    $('#account_complaints').text( response.total_complaints);
                     $('#account_invoices').text( response.total_invoices);

                    // if (response.gocab_paid >= 0) {
                    //     $('#balance_heading').html('Amount owed to driver');
                    // } else {
                    //     $('#balance_heading').html('Amount owed to Gocab');
                    // }


                },
                error: function (xhr) {

                }
            });
        }

        $(document).ready(function () {
            get_account_summary();
            startTime();

        });

    </script>
    <script>
      var options1 = {
    chart: {
        height: 150,
        type: 'area',
        toolbar:{
          show: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth'
    },
    series: [{
        name: 'series1',
        data: [31, 32, 33]
    }],

    xaxis: {
        type: 'datetime',
        categories: [  "2018-09-19T05:30:00", "2018-09-20T06:30:00","2018-09-21T06:30:00"],
        labels: {
            show: false // hide x-axis labels
        },
        axisBorder: {
            show: false // hide axis line
        },
        axisTicks: {
            show: false // hide ticks
        },
        tooltip: {
            enabled: false // hide tooltip on x-axis
        }
      },
      yaxis: {
        show: false // hide y-axis completely
    },
    grid: {
        show: false // hide background grid
    },
    legend: {
        show: false // hide series legend
    },
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        },
    },
    colors:[ CubaAdminConfig.primary ]
}

var chart1 = new ApexCharts(
    document.querySelector("#area-spaline"),
    options1
);

chart1.render();
// second chart
var options2 = {
    chart: {
        height: 150,
        type: 'area',
        toolbar:{
          show: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth'
    },
    series: [{
        name: 'series1',
        data: [31, 32, 33]
    }],

    xaxis: {
        type: 'datetime',
        categories: [  "2018-09-19T05:30:00", "2018-09-20T06:30:00","2018-09-21T06:30:00"],
        labels: {
            show: false // hide x-axis labels
        },
        axisBorder: {
            show: false // hide axis line
        },
        axisTicks: {
            show: false // hide ticks
        },
        tooltip: {
            enabled: false // hide tooltip on x-axis
        }
      },
      yaxis: {
        show: false // hide y-axis completely
    },
    grid: {
        show: false // hide background grid
    },
    legend: {
        show: false // hide series legend
    },
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        },
    },
    colors:[ CubaAdminConfig.primary ]
}

var chart2 = new ApexCharts(
    document.querySelector("#area-spaline1"),
    options2
);

chart2.render();
// third chart
var options3 = {
    chart: {
        height: 150,
        type: 'area',
        toolbar:{
          show: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth'
    },
    series: [{
        name: 'series1',
        data: [31, 32, 33]
    }],

    xaxis: {
        type: 'datetime',
        categories: [  "2018-09-19T05:30:00", "2018-09-20T06:30:00","2018-09-21T06:30:00"],
        labels: {
            show: false // hide x-axis labels
        },
        axisBorder: {
            show: false // hide axis line
        },
        axisTicks: {
            show: false // hide ticks
        },
        tooltip: {
            enabled: false // hide tooltip on x-axis
        }
      },
      yaxis: {
        show: false // hide y-axis completely
    },
    grid: {
        show: false // hide background grid
    },
    legend: {
        show: false // hide series legend
    },
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        },
    },
    colors:[ CubaAdminConfig.primary ]
}

var chart3 = new ApexCharts(
    document.querySelector("#area-spaline2"),
    options3
);

chart3.render();
    </script>
   

@endsection

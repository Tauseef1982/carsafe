@extends('customer.layouts.yajra')

@section('content')
<div class="card total-users">

        <div class="card-body pt-5 ">
          <div class="row">
          <div class="col-xl-4 col-lg-12 xl-50 morning-sec box-col-12">
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
                        <div class="badge f-10"><i class="me-1" data-feather="clock"></i><span id="txt"></span></div>
                      </div>
                    </div>
                    <div class="cartoon"><img class="img-fluid" src="../assets/images/dashboard/cartoon.png" alt=""></div>
                  </div>
                </div>
              </div>
            <div class="col-md-6 pt-5">
                <div class="card p-5">
              <div class="bg-primary card p-10">
                <h5 class=" text-center font-dark">Total Trips</h5>
                <h6 class=" text-center font-dark" id="total_trips">...loading</h6>

              </div>
              <div class="bg-dark card p-10">
                <h5 class="font-dark text-center txt-primary">Balance</h5>
                <h6 class="font-dark text-center txt-primary" id="account_balance">...loading</h6>
                <!-- <button class="btn text-primary" type="button" data-bs-toggle="modal" data-bs-target="#myModal">Add More Balance</button> -->

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
        <div class="modal-header bg-dark">
          <h5 class="text-primary">Add Balance</h5>
          <button class="btn-close  btn-primary" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
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
                  <input type="submit" class="btn btn-dark mt-3 ms-auto text-primary" value="Add Balance">

              </div>
          </form>
      </div>
        </div>
      </div>
      </div>

@endsection

@section('js')

    <script>

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
        // greeting
        var today = new Date()
        var curHr = today.getHours()

        if (curHr >= 0 && curHr < 4) {
            document.getElementById("greeting").innerHTML = 'Good Night';
        } else if (curHr >= 4 && curHr < 12) {
            document.getElementById("greeting").innerHTML = 'Good Morning';
        } else if (curHr >= 12 && curHr < 16) {
            document.getElementById("greeting").innerHTML = 'Good Afternoon';
        } else {
            document.getElementById("greeting").innerHTML = 'Good Evening';
        }

        // time
        function startTime() {
            var today = new Date();
            var h = today.getHours();
            var m = today.getMinutes();
            // var s = today.getSeconds();
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            m = checkTime(m);
            // s = checkTime(s);
            document.getElementById('txt').innerHTML =
                h + ":" + m + ' ' + ampm;
            var t = setTimeout(startTime, 500);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i
            }
            ;  // add zero in front of numbers < 10
            return i;
        }
    </script>

@endsection

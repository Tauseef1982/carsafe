@extends('admin.layout.yajra')

@section('css')
    <style>
        .pac-container {
            z-index: 10000 !important; /* Ensure it is above Bootstrap modal (1050) */
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
            <div class="xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">


                        <form method="post" onsubmit="return confirmSubmit()" action="{{url('admin/accounts/cron-postpaid')}}" >
                            @csrf

                            <div class="row">
                                <h5 class="pull-left">Generate Invoices</h5>

                                <div class="col-6">

                                    <label for="">From</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date">
                                </div>
                                <div class="col-6 ">
                                    <label for="">To</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date">

                                </div>
                                <div class="col-12 mt-3">
                                    <p>Dear Account Holder,</p>
                                  
                                    <label for="">Custome Message</label>
                                    <textarea id="summernote" name="custom_msg" class="form-control" rows="5"></textarea>
                                    <p>Thank You For Choosing CarSafe.</p> 
                                     <p>If you notice a 'flagdown' charge on your last invoice for a trip you didn’t take in this period , feel free to submit a complaint and we will review it and respond via email.</p>
                                    
                                </div>
                                <div class="col-12 mt-5">

                                <input type="button" id="submit" class="pull-right btn btn-primary" value="Submit Invoices">
                                </div>

                            </div>


                        </form>

                        <div class="row">
                            <h5 class="pull-left">Results</h5>
                            <ul id="results">

                            </ul>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@endsection

@section('js')
<script>
    $(document).ready(function() {
  $('#summernote').summernote();
});
</script>



    <script>


        var counttt = 0
        function syncing() {

            $('#results').append('<li>Processing....</li>');

            $.ajax({
                url: '/admin/accounts/cron-postpaid',
                type: 'POST',
                data: {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    custom_msg: $('#summernote').val(),
                    _token: "{{csrf_token()}}"
                },
                success: function(response) {

                    $('#results').append('<li>'+response+'</li>');
                    if(response){

                        if(counttt < 100) {

                            counttt++;
                            console.log(counttt);
                            syncing();
                        }
                    }
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }

        $('#submit').on('click', function() {

            syncing();


        });



    </script>
@endsection

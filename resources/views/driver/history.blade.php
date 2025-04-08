@php
use Illuminate\Support\Carbon;
@endphp
@extends('layout')

@section('content')
<div class="page-title">
  <div class="row">
    <div class="col-6">

    </div>
    <div class="col-6">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i data-feather="home"></i></a></li>

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
          <h5>Trips History</h5>

        </div>
        <div class="card-body">

            <ul class="display" id="basic-ul">


            </ul>
            <button class="btn btn-primary" onclick="loadmore()">Load More</button>

          </div>
        </div>
      </div>
      </div>
      </div>
      </div>
@endsection
@section('js')

    <script>

        let offset = 0;
        function loadmore(){
            $.ajax({
                url: "{{url('/trip_history')}}", // Your route to fetch more products
                method: 'GET',
                data: { offset: offset },
                success: function(data) {
                    $('#basic-ul').append(data);  // Append the new products to the container
                    offset += 10;  // Increase the offset for the next load
                }
            });
        }
        $(document).ready(function () {

            loadmore();

        });
    </script>
@endsection

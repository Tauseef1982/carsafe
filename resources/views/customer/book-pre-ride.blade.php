@extends('customer.layouts.yajra')
@section('css')
<style>
    .page-wrapper .page-body-wrapper .page-title {
    padding: 15px 9px !important;
    margin: 0 !important;

    border-bottom: none !important;
    box-shadow: none !important;
}
.green{
    margin-left: 5px;
    width: 17px;
    height: 17px;
    background-color: #00C42A;
    border-radius: 50%;
}
.red{
    margin-left: 5px;
    width: 17px;
    height: 17px;
    background-color: #FF4141;
    border-radius: 50%;
}
</style>
@endsection
@section('content')

    <div class="card total-users" style="padding-bottom:70px;">

           <div class="container-fluid">
              <div class="page-title">
                  <div class="row">
                    <div class="col-6">
                      <h3 class="f-28 fw-bold">Book a Pre Ride</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">Booking</li>

                      </ol>
                    </div>
                  </div>

                </div>

            <div class="row">
                 <!-- Zero Configuration  Starts-->
                 <div class="col-sm-12 mt-5">
                    <div class="">
                     <p class="d-flex">
                        <span class="pt-2"><img class="img-fluid" src="{{asset('assets/images/map-pin-3-line.png')}}" alt=""></span>
                        <span class="f-24 fw-bold ms-2 ">Select Pickup & Drop Location</span>
                    </p>

                    </div>
                    <form action="{{ url('customer/store_pre_ride') }}" id="book_ride_form" method="POST">
                        @csrf

                        <input type="hidden" name="account_id" value="{{$account->account_id}}">

                        <div class="d-flex">
                            <div class="green"></div>

                            <p class="f-14 f-w-700 ms-3" style="line-height:17px;">Date Time</p>
                        </div>
                        <img src="{{ asset('assets/images/Line 4.png') }}" style="    position: absolute; margin-left: 12px; margin-top: -14px; height:100px;" alt="">
                        <div class="form-group mb-5 ps-5">

                            <div class="input-group ">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                                <input class="form-control" type="date" id="date" name="date" placeholder="Date" required>
                                <input class="form-control" type="time" id="time" name="time" placeholder="Time" required>

                            </div>
                        </div>


                    <div class="d-flex">
                        <div class="green"></div>

                        <p class="f-14 f-w-700 ms-3" style="line-height:17px;">Pickup Location</p>
                    </div>
                    <img src="{{ asset('assets/images/Line 4.png') }}" style="    position: absolute; margin-left: 12px; margin-top: -14px; height:100px;" alt="">
                    <div class="form-group mb-5 ps-5">

                    <div class="input-group ">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                      <input class="form-control" type="text" id="pickup_location" name="pickup_location" placeholder="Please enter your full address. Address, City, State and Zip.">
                      <input type="hidden" name="pickup_lat" id="pickup_lat" >
                      <input type="hidden" name="pickup_lng" id="pickup_lng" >
                    </div>
                  </div>
                    <div class="d-flex mt-5">
                        <div class="red"></div>
                        <p class="f-14 f-w-700 ms-3" style="line-height:17px;">Drop Location</p>
                    </div>
                    <div class="form-group ps-5 mb-5">

                    <div class="input-group">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/map-pin-2-line.png') }}" alt="">
                          </span>
                      <input class="form-control" type="text" id="drop_location" name="drop_location" placeholder="Please enter your full address. Address, City, State and Zip.">
                      <input type="hidden" name="drop_lat" id="drop_lat" placeholder="Drop Latitude">
                     <input type="hidden" name="drop_lng" id="drop_lng" placeholder="Drop Longitude">
                    </div>
                      <div class="input-group mt-3">
                        <span class="input-group-text">
                            <i class="fa fa-phone" style="padding: 2px; font-size: 21px; color: #F05829;"></i>
                          </span>
                      <input class="form-control " type="phone" required  name="phone_number" placeholder="Please enter Passenger phone number">

                    </div>

                       <label for="" class="mt-3">Please Choose Driver</label>
                       <select name="driver_type" class="form-select" id="">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="both">Any</option>
                       </select>


                  </div>
                  <div class="d-flex text-end">
                    <button class="btn btn-light me-2 text-gray b-r-8 ms-auto" id="cancel_btn">Cancel</button>
                    <input class="btn bg-orange-g text-white b-r-8" type="submit" value="Countinue">

                  </div>
                  </form>

                  </div>



                  <!-- Zero Configuration  Ends-->
            </div>
           </div>
          </div>

@endsection
@section('js')
<script>
  function initAutocomplete() {
    const pickupInput = document.getElementById('pickup_location');
    const dropInput = document.getElementById('drop_location');

    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, {
      types: ['geocode']
    });

    const dropAutocomplete = new google.maps.places.Autocomplete(dropInput, {
      types: ['geocode']
    });

    pickupAutocomplete.addListener('place_changed', function () {
      const place = pickupAutocomplete.getPlace();
      if (place.geometry) {
        document.getElementById('pickup_lat').value = place.geometry.location.lat();
        document.getElementById('pickup_lng').value = place.geometry.location.lng();
      }
    });

    dropAutocomplete.addListener('place_changed', function () {
      const place = dropAutocomplete.getPlace();
      if (place.geometry) {
        document.getElementById('drop_lat').value = place.geometry.location.lat();
        document.getElementById('drop_lng').value = place.geometry.location.lng();
      }
    });
  }
</script>

<!-- This line must be placed AFTER the function, and without async/defer -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5iwvhZmOVgCzqDOrKp_Q7SNYucsFDEd4&libraries=places&callback=initAutocomplete" async defer></script>
<script>
  $(document).ready(function () {

    $('#cancel_btn').click(function(e) {
      e.preventDefault();
      $('#book_ride_form')[0].reset();
    });

  });
</script>




@endsection

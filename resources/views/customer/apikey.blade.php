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
                      <h3 class="f-28 fw-bold">Manage API key</h3>
                    </div>
                    <div class="col-6">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('customer/index') }}">                                       <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item text-primary">API Key</li>

                      </ol>
                    </div>
                  </div>

                </div>

            <div class="row">
                 <!-- Zero Configuration  Starts-->
                 <div class="col-sm-12 mt-5">


                    @if (!$apikey)
                     <form action="{{ url('customer/api-key') }}" id="api_key_form" method="POST">
                        @csrf

                        <input type="hidden" name="account_id" value="{{$account->account_id}}">
                     </div>
                  <div class="d-flex text-end">

                    <input class="btn bg-orange-g text-white b-r-8" type="submit" value="Create API Key">

                  </div>
                  </form>
                  @else
                      <div class="mb-3">
                        <small class="text-success d-none" id="copyMsg">
                            ✅ Copied to clipboard
                        </small>
                             <label for="">Your Key:</label>
                          <input type="text" readonly value="{{ $apikey->api_key }}" class="form-control mb-3"  id="apiKeyInput">
                           <button class="btn bg-orange-g text-white b-r-8" id="copyApiKey">
                            Copy
                         </button>
                        </div>

                @endif




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

    $('#copyApiKey').click(function () {

        let copyText = $('#apiKeyInput');

        // Select text
        copyText.prop('readonly', false);
        copyText.select();
        copyText[0].setSelectionRange(0, 99999);
        copyText.prop('readonly', true);

        // Copy
        document.execCommand("copy");

        // Message
        $('#copyMsg').removeClass('d-none');

        setTimeout(function () {
            $('#copyMsg').addClass('d-none');
        }, 1500);
    });

  });
</script>




@endsection

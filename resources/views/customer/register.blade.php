@extends('auth-layout')
 @section('css')
    <style>
        .icon {
            float: right;
            margin-top: -28px;
            margin-right: 20px;
        }
        .second_step{
          display: none;
        }
        .third_step{
          display: none;
        }
    </style>

@endsection
 @section('content')


  <div class="row m-0">
  <div class="col-12 p-0">
  <div class="login-card">
  <div>
  <div>
  <a class="logo" href="">
  <img class="img-fluid" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="200px" alt="looginpage">
  </a>
  </div>
  <div class="login-main">
  @if (session('success'))
  <div class="alert alert-success">
  {{ session('success') }}
  </div>
  @endif
  @if (session('error'))
  <div class="alert alert-danger">
  {{ session('error') }}
  </div>
  @endif
  <form class="theme-form" id="addAccountForm" action="{{ url('add_account') }}" method="post">
    @csrf
    <div class="first_step">
    <h4>Please Register Here</h4>
     <input type="hidden" name="account_type" value="prepaid">
     <div class="form-group">
      <label class="col-form-label">Account Number</label>
      <div class="input-group">
    <input type="number" class="form-control mb-3" required placeholder="Please enter an unique account number" name="account_id"
      id="account_id" />
    <input type="hidden" name="password" value="{{ rand(10000000, 99999999) }}">
    </div>
    <div id="errorDiv" style="color: red;"></div>
     </div>
    <div class="form-group">
    <label class="col-form-label">Name</label>
    <div class="input-group">
    <input class="form-control" type="text" name="f_name" placeholder="Enter Your Full Name">
    </div>
    </div>
    <div class="form-group">
    <label class="col-form-label">Company Name</label>
    <div class="input-group">
    <input class="form-control" type="text" name="company_name" placeholder="Enter Your Company Name">
    </div>
    </div>
    <div class="form-group">
    <label class="col-form-label">Email</label>
    <div class="input-group">
    <input class="form-control" type="email" name="email" placeholder="Enter Your Email">
    </div>
    </div>
    <div class="form-group">
    <label class="col-form-label">Billing Email</label>
    <div class="input-group">
    <input class="form-control" type="email" name="billing_email" placeholder="Enter Billing Email">
    </div>
    </div>
    <div class="form-group">
    <label class="col-form-label">Phone</label>
    <div class="input-group">
    <input class="form-control" type="tel" name="phone" placeholder="Enter Your Phone Number">
    </div>
    </div>
    <input type="hidden" name="cash_type" value="cash">
    <div class="form-group">
    <label class="col-form-label">Address</label>
    <div class="input-group">
    <input class="form-control" type="text" name="address" placeholder="Enter Your Address">
    </div>
    </div>
    <div class="form-group">
     <label for="">How Would You Like to Get Trip Notifications?</label> <br>
     <input type="radio" value="account_email" name="notification_setting" id="account_email_n">
     <label for="account_email_n">Account Email </label>
     <input type="radio" value="account_phone" name="notification_setting" id="account_phone_n">
     <label for="account_phone_n">Account Phone Number</label>
     <br>
     <input type="radio" value="passenger_phone" name="notification_setting" id="passenger_phone_n">
     <label for="passenger_phone_n">Passenger Phone Number</label>
     <input type="radio" value="both_phone" name="notification_setting" id="both_phone_n">
     <label for="both_phone_n">Both Phone Numbers</label>
     <br>
    </div>




    <div class="form-group mb-3">

    <div class="text-end mt-3">
    <button class="btn btn-primary btn-block w-100" id="next-2nd" type="">Next</button>
    </div>
    </div>
  </div>
  <div class="second_step" >
     <span style="cursor: pointer;" id="go_first_step">

    <i class="fa fa-long-arrow-left"></i> Go Back

    </span>
     <span  style="cursor: pointer;"id="addPinBtn" class="btn btn-primary float-end">Add More</span>
    <div class="form-group">
    <label class="col-form-label">Add Your Pin Numbers</label>
     <input type="hidden" class="form-control mb-3" id="pinsFinal" name="pins" />

     <div id="pinInputsContainer">

     </div>

    </div>
    <div class="form-group mb-3">

    <div class="text-end mt-3">
    <button class="btn btn-primary btn-block w-100" id="next-3rd" type="">Next</button>
    </div>
    </div>

  </div>
  <div class="third_step">
     <span style="cursor: pointer;" id="go_second_step">

    <i class="fa fa-long-arrow-left"></i> Go Back

    </span>
    <div class="form-group">
     <label for="">Would you like to pay per trip</label>
    <div class="input-group">

    <input type="radio" id="on-paypertrip" name="paypertrip" value="on"><label for="on-paypertrip" class="ms-2">Yes</label>
    <input type="radio" class="ms-3" id="off-paypertrip" name="paypertrip" value="off"><label for="off-paypertrip" class="ms-2">No</label>

    </div>
    </div>
    <div class="payment-section">
    <div class="form-group">
    <label for="">Please Enter your first recharge amount</label>
    <div class="input-group">
    <input type="number"  class="form-control mb-2" name="first_refill" placeholder="Please enter your recharge amount">
    <input type="hidden" id="on-autofill" name="autofill"  value="on">
    
    </div>
    </div>
     <div class="form-group">
    <label for="">Please Enter your recharge amount</label>
    <div class="input-group">
    <input type="number"  class="form-control mb-2" name="recharge" placeholder="Please enter your recharge amount">
   
    <small class="text-danger">(Your card will be charged automatically when your balance falls below $20.)</small>
    </div>
    </div>
    </div>
    <div class="card-js" id="cardnox_inputs">
    <div class="card-js">
    <input class="card-number my-custom-class form-control" name="card_number" placeholder="Card Number" value="">
    <input class="expiry-month" name="month" placeholder="MM">
    <input class="expiry-year " name="year" placeholder="YYYY" value="">
    <input class="cvc form-control mt-3" name="cvc" placeholder="CVC" value="">
    </div>
    </div>


<button class="btn btn-primary w-100 mt-3" type="submit">Submit</button>

  </div>
     

  </form>

  <a href="{{ url('customer/login') }}">Please Login if registered already!</a>
  </div>
  </div>
  </div>
  </div>
  </div>
  @endsection

      @section('js')
         <script>
         function addPinInput(value = '', pinId = null) {
           const inputGroup = $(`
        <div class="input-group mb-2">
          <input type="text" class="form-control" name="pinItem" value="${value}" placeholder="Enter pin">
          <button type="button" class="btn btn-danger removeBtn">Delete</button>
        </div>
      `);


      inputGroup.find('.removeBtn').click(function () {
        inputGroup.remove(); 


        submitAllPinsViaAjax();
      });

      $('#pinInputsContainer').append(inputGroup);
    }
    function collectPins() {
      const pinValues = [];
      $('input[name="pinItem"]').each(function () {
        const val = $(this).val().trim();
        if (val !== '') {
          pinValues.push(val);
        }
      });
      return pinValues.join(',');
    }
    $(document).ready(function () {
      $('#next-2nd').click(function(){
        $('.second_step').show();
        $('.first_step').hide();
        $('.third_step').hide();
      });
      
        $('#go_second_step').click(function(){
        $('.second_step').show();
        $('.first_step').hide();
        $('.third_step').hide();
      });
      $('#go_first_step').click(function(){
         $('.second_step').hide();
        $('.first_step').show();
        $('.third_step').hide();
      });
      $('#next-3rd').click(function(){
         $('.second_step').hide();
        $('.first_step').hide();
        $('.third_step').show();   
      });



    $(".expiry").addClass("form-control mt-3");
    
       $('#account_id').on('input', function () {

        var value = $(this).val();

        if (value.length >= 4) {
            $('#errorDiv').text('');
        }
        if (value.length < 4) {
            $('#errorDiv').text('Account number must be at least 4 digits long.');
        }
    });
       
    
    addPinInput();


      // Submit button triggers same AJAX function
      $('#submitBtn').click(function (e) {
        e.preventDefault(); // stop actual form submit
        submitAllPinsViaAjax();
      });

      // Add new pin input
      $('#addPinBtn').click(function () {
       
        addPinInput();
      });
     

      // add account ajax
$('#addAccountForm').on('submit', function (e) {
        e.preventDefault(); // prevent default form submission
         const pins = collectPins();
        let form = $(this);
        let formData = new FormData(this);
        let submitButton = form.find('button[type="submit"]');
         formData.append('pins', JSON.stringify(pins));
        // Disable the button and show loading state
        submitButton.prop('disabled', true).text('Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
               if (response.status) {
            toastr.success(response.message);
            form[0].reset();

          
        } else {
            toastr.error(response.message);
             submitButton.prop('disabled', false).text('Submit');
        }
            },
            error: function (xhr) {
                let errorDiv = $('#errorDiv');
                errorDiv.empty();

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        errorDiv.append('<div>' + value[0] + '</div>');
                    });
                     submitButton.prop('disabled', false).text('Submit');
                } else {
                    errorDiv.text('An error occurred. Please try again.');
                     submitButton.prop('disabled', false).text('Submit');
                }
            },
            complete: function () {
                // Re-enable the button
                submitButton.prop('disabled', false).text('Submit');
            }
        });
    });

// add account ajax

    });
         </script>
    @endsection

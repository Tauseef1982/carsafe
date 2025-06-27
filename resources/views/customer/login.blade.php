
 @extends('auth-layout')
 @section('css')
 <style>
  
 </style>
 
 @endsection
 @section('content')


      <div class="row m-0">
        <div class="col-12 p-0" style="background-image:linear-gradient(to bottom, #FEEEEA, #FFFFFF)">
          <div class="login-card">
            <div>
            
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
        <div class="row mt-5">
          <div class="col-md-6 bg-orange-g left-radius sm-radius text-center d-flex align-items-center" >
               <div class="mx-auto">  
            <img src="{{ asset('assets/images/logo/carsafe-logo.webp') }}" style="height:220px; width: 220px;" alt="">
            <h3 class="f-32 text-white font-inter">Welcome to SafeCar Portal</h3>
            <p class="f-16 text-white font-inter">
              Simple. Secure. Safe everything you need to <br> protect your drive.
            </p>
            </div>
          </div>
          <div class="col-md-6 px-5 py-5 right-radius">
                          <form class="theme-form" action="" method="post">
                  @csrf
                  <h4 class="f-32 fw-bold font-inter">Sign in to Customer Portal</h4>
                  <p class="font-inter f-16">Enter your information to log in to your account.</p>
                  <div class="form-group">
                    <label class="col-form-label f-14 font-inter">Account Number</label>
                    <div class="input-group">
                      <span class="input-group-text">
                            <img src="{{ asset('assets/images/mail-line.png') }}" alt="">
                          </span>
                      <input class="form-control" type="text" name="username" placeholder="Enter Your Account Number">
                    </div>
                  </div>

                    <div class="form-group">
                        <label class="col-form-label f-14 font-inter">Password</label>
                        <div class="input-group">
                          <span class="input-group-text">
                            <img src="{{ asset('assets/images/lock-line.png') }}" alt="">
                          </span>
                         <input class="form-control" placeholder="Enter Your password" id="password_field" name="password" type="password"  >
                        
                          <span class="input-group-text" id="password_toggle" style="border-left:none !important; border-right: 1px solid #efefef; !important; cursor:pointer">
                             <img src="{{ asset('assets/images/eye-off-line.png') }}" alt="">
                          </span>
                        </div>
                    </div>

                  <!-- <div class="form-group mb-0">

                    <div class="text-end mt-3">
                       <div class="checkbox p-0">
                      <input id="checkbox1" type="checkbox">
                      <label class="text-muted" for="checkbox1">Remember password</label>
                    </div><a class="link" href="forget-password.html">Forgot password?</a>
                      <button class="btn bg-orange-g btn-block w-100 text-white" type="submit">Login</button>
                    </div>
                  </div> -->
                  <div class="form-group mb-0">
                    <div class="checkbox p-0">
                      <input id="checkbox1" type="checkbox">
                      <label class="text-muted" for="checkbox1">Remember me</label>
                    </div><a class="link text-dark" href="{{ url('customer/reset_password') }}">Forgot password?</a>
                    <div class="text-end mt-3">
                      <button class="btn bg-orange-g b-r-8 btn-block w-100 text-white " type="submit">Login in</button>
                    </div>
                  </div>



                </form>
                 <p class="mt-5 text-center text-muted">Don’t have an account? 
                  <span> <a href="{{ url('register') }}" class="">Sign Up</a></span>
                </p>
               
                
          </div>

        </div>
  
              </div>
            </div>
          </div>
        </div>
      </div>
      @endsection
      @section('js')
      <script>
        $(document).ready(function () {
          $('#password_toggle').on('click', function () {
    const passwordField = $('#password_field');
    const toggleIcon = $('#toggle_icon');

    const isPassword = passwordField.attr('type') === 'password';

    passwordField.attr('type', isPassword ? 'text' : 'password');
    toggleIcon.attr('src', isPassword 
        ? "{{ asset('assets/images/eye-line.png') }}"     // visible
        : "{{ asset('assets/images/eye-off-line.png') }}"  // hidden
    );
});

          
        })
      </script>
      
      @endsection

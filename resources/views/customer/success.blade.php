
 @extends('auth-layout')
 @section('content')


      <div class="row m-0">
        <div class="col-12 p-0">
          <div class="login-card">
            <div>
            <div>
                <a class="logo" href="{{ url('customer/login') }}">
                <img class="img-fluid" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="200px" alt="looginpage">
              </a>
            </div>
              <div class="login-main text-center">
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
                 <img src="{{asset('assets/images/svg-icon/check-circle.svg')}}" class="img-fluid" width="200px" alt="" srcset="">
                <h3>
                    Your account is created successfully, please check your email for your login details.
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endsection

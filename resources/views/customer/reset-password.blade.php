
@extends('auth-layout')
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
                <form class="theme-form" action="" method="post">
                  @csrf
                  <h4>Reset Your Password</h4>
                  <p>Enter your Email</p>
                  <div class="form-group">
                    <label class="col-form-label">Email</label>
                    <div class="input-group">
                      <input class="form-control" name="email" placeholder="Enter Your Email">
                    </div>
                  </div>

                    

                  <div class="form-group mb-0">

                    <div class="text-end mt-3">
                      <button class="btn btn-primary btn-block w-100" type="submit">Reset Password</button>
                    </div>
                  </div>



                </form>
                
                
              </div>
            </div>
          </div>
        </div>
      </div>
      @endsection

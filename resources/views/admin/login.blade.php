
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
              @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
                <form class="theme-form" action="{{route("admin.attempt.login")}}" method="post">
                  @csrf
                  <h4>Sign in to Admin account</h4>
                  <p>Enter your Username/password</p>
                  <div class="form-group">
                    <label class="col-form-label">Username</label>
                    <div class="input-group">
                      <input class="form-control" name="username" placeholder="Enter Your Username">
                    </div>
                  </div>

                    <div class="form-group">
                        <label class="col-form-label">Password</label>
                        <div class="input-group">
                            <input class="form-control" name="password" type="password"  placeholder="Enter Your password">
                        </div>
                    </div>

                  <div class="form-group mb-0">

                    <div class="text-end mt-3">
                      <button class="btn btn-primary btn-block w-100" type="submit">Login</button>
                    </div>
                  </div>



                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endsection

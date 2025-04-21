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
                <form class="theme-form" action="{{url('verify-otp')}}" method="post">
                  @csrf
                  <h4>CarSafe Login Code</h4>
                   @php
                 
$masked_phone = substr($user_phone, -4); 
$masked_phone = str_repeat('*', strlen($user_phone) - 4) . $masked_phone;
                   @endphp
                  <div class="form-group">
                    <label class="col-form-label">An OTP Sent To Phone Number {{$user_phone}} </label>

                      <input class="form-control" name="otp" type="text" placeholder="Please Enter OTP here">
                    </div>

                  <div class="form-group mb-0">

                    <div class="text-end mt-3">
                      <button class="btn btn-primary btn-block w-100" type="submit">Send</button>
                    </div>
                  </div>

                <input hidden name="phone" value="{{$user_phone}}">
                <input hidden name="username" value="{{$username}}">
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
     @endsection

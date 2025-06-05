<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="icon" href="{{asset('assets/images/logo/carsafe-icon.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/carsafe-icon.png')}}" type="image/x-icon">
    <title>CarSafe</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/font-awesome.css')}}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/icofont.css')}}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/themify.css')}}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/flag-icon.css')}}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/feather-icon.css')}}">
    <!-- Plugins css start-->
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/bootstrap.css')}}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}">
    <link id="color" rel="stylesheet" href="{{asset('assets/css/color-1.css')}}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/responsive.css')}}">
     <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/toastr.min.css')}}">
    @yield('css')
  </head>
  <body>
  
    <div class="container-fluid p-0">

  @yield('content')

  
   <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
      
      <script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
     
      <script src="{{asset('assets/js/icons/feather-icon/feather.min.js')}}"></script>
      <script src="{{asset('assets/js/icons/feather-icon/feather-icon.js')}}"></script>
     
      <script src="{{asset('assets/js/config.js')}}"></script>
     <script src="{{asset('assets/js/card-js.min.js')}}"></script>
     <script src="{{asset('assets/js/toastr.min.js')}}"></script>
      <script src="{{asset('assets/js/script.js')}}"></script>
       <script>
         @if (session('success'))
        toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
        toastr.warning("{{ session('error') }}");
        @endif
      </script>
       @yield('js')
    
    </div>
  </body>
</html>
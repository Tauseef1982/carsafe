@php
use Carbon\Carbon;
$currentYear = Carbon::now()->year;
use App\Models\Account;
$user = Auth::guard('customer')->user()->account_id;
$account = Account::where('account_id' , $user)->first();

@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords"
          content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{asset('assets/images/logo/carsafe-icon.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/carsafe-icon.png')}}" type="image/x-icon">
    <title>CarSafe - Portal </title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
          rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/font-awesome.css')}}">
    <!-- ico-font-->
{{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/icofont.css')}}">--}}
<!-- Themify icon-->
{{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/themify.css')}}">--}}
<!-- Flag icon-->
{{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/flag-icon.css')}}">--}}
<!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/feather-icon.css')}}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/scrollbar.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/animate.css')}}">
    {{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/chartist.css')}}">--}}
    {{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/owlcarousel.css')}}">--}}
    {{--    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/prism.css')}}">--}}
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/datatables.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/summernote.css') }}" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/bootstrap.css')}}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}">
    <link id="color" rel="stylesheet" href="{{asset('assets/css/color-1.css')}}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/responsive.css')}}">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/toastr.min.css')}}">

    @yield('css')
    <style>
        .select2-container {
            z-index: 9999 !important;
        }

        /* loader.css */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8); /* semi-transparent overlay */
            z-index: 9999; /* make sure it appears on top */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .blurred {
            filter: blur(5px); /* Apply blur effect */
            pointer-events: none; /* Disable interaction */
            user-select: none; /* Prevent text selection */
            opacity: 0.7; /* Optional: Reduce opacity */
        }

        body.dark-only .page-wrapper .page-body-wrapper a div {
            color: rgba(255, 255, 255, 0.6);
        }

        .time-display {
      display: flex;
      align-items: center;
      color: #F3744D;
      font-weight: 600px;
      font-size: 14px;
    }
       .search-bar {
      display: flex;
      align-items: center;
      background-color: #f5f6f7;
      border-radius: 10px;
      padding: 4px 13px;
    }
    .search-bar i {
      color: #888;
      margin-right: 8px;
    }
    .search-bar input {
      border: none;
      outline: none;
      background: transparent;
      font-size: 14px;
      flex: 1;
    }
    .search-shortcut {
      background: white;
      padding: 4px 7px;
      border-radius: 6px;
      font-size: 12px;
      color: #555;
      border: 1px solid #ddd;
    }
    .page-wrapper .page-header .header-wrapper {
    width: 100%;
    display: flex
;
    align-items: center;
    padding: 11px 16px;
    position: relative;
}
.menu-item{
    background: transparent;
    padding: 8px 12px !important;
    border-radius: 6px !important;
    
    display: flex;
    align-items: center;
    gap: 6px;
}
.menu-item:hover{
    background: #F3744D;
    color: #fff;
}
.menu-item-active{
    background: #F3744D;
    color: #fff;
}
.menu-item:hover i,
.menu-item:hover svg {
    stroke: #fff; 
}
.menu-item.menu-item-active i,
.menu-item.menu-item-active svg {
    stroke: #fff; 
}


    </style>
</head>
<body>

<!-- tap on top starts-->
<div class="tap-top"><i data-feather="chevrons-up"></i></div>
<!-- tap on tap ends-->
<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <div class="page-header">
        <div class="header-wrapper row m-0">
            <div id="loader">
                <div class="spinner"></div>
            </div>
            <div class="header-logo-wrapper col-auto p-0">
                <div class="logo-wrapper">
                    <a href="{{url('customer/index')}}">
                        <img class="img-fluid" src="{{asset('assets/images/logo/logo-safe.png')}}" alt="">
                    </a>
                </div>
                <div class="toggle-sidebar">
                    <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
                </div> 

            </div>
            <div class="left-header col horizontal-wrapper ps-0">
                <ul class="horizontal-menu">
              <li > 
                <div class="greeting">
                    <span id="greeting"></span>, <strong>{{ $account->f_name }}!</strong>
                </div>
                
              </li>
           
            </ul>
            </div>
            <div class="nav-right col-md-8 pull-right right-header p-0">
                <ul class="nav-menus">
              <li>
                <div class="time-display" >
                  <i class="me-1" data-feather="clock"></i>
                  <span id="txt"></span>
                  
                </div>
              </li>
              <li>                           
                <div class="search-bar">
                  <i class="fa fa-search"></i>
                  <input type="text" placeholder="Search here...">
                  <div class="search-shortcut">⌘ K</div>
                </div>
              <li class="onhover-dropdown">
                <div class="notification-box"><i data-feather="bell"> 

                </i><span class="badge rounded-pill badge-danger">4 </span></div>
                <div class="onhover-show-div notification-dropdown">
                  <h6 class="f-18 mb-0 dropdown-title">Notitications                               </h6>
                  <ul>
                    <li class="b-l-primary border-4">
                      <p>Delivery processing <span class="font-danger">10 min.</span></p>
                    </li>
                    <li class="b-l-success border-4">
                      <p>Order Complete<span class="font-success">1 hr</span></p>
                    </li>
                    <li class="b-l-info border-4">
                      <p>Tickets Generated<span class="font-info">3 hr</span></p>
                    </li>
                    <li class="b-l-warning border-4">
                      <p>Delivery Complete<span class="font-warning">6 hr</span></p>
                    </li>
                    <li><a class="f-w-700" href="#">Check all</a></li>
                  </ul>
                </div>
              </li>
             
              <!-- <li>
                <div class="mode"><i class="fa fa-moon-o"></i></div>
              </li> -->
            <li>
                <div style="font-size: 18px;
    font-weight: 600;
    -webkit-transition: all 0.3s ease;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 20px;
    text-align: center;"><i class="fa fa-moon-o"></i></div>
              </li>
           
              
              
              <li class="profile-nav onhover-dropdown p-0 me-0">
                <div class="media profile-media">
                    <img style="width: 40px; height: 40px; border-radius: 50%;" src="../assets/images/dashboard/profile.jpg" alt="">
                  <div class="media-body ">
                    <span></span>
                    <p class="mb-0 font-roboto">{{ $account->f_name }} <i class="middle fa fa-angle-down"></i></p>
                  </div>
                </div>
                <!-- <ul class="profile-dropdown onhover-show-div">
                  <li><a href="#"><i data-feather="user"></i><span>Account </span></a></li>
                  <li><a href="#"><i data-feather="mail"></i><span>Inbox</span></a></li>
                  <li><a href="#"><i data-feather="file-text"></i><span>Taskboard</span></a></li>
                  <li><a href="#"><i data-feather="settings"></i><span>Settings</span></a></li>
                  <li><a href="#"><i data-feather="log-in"> </i><span>Log in</span></a></li>
                </ul> -->
              </li>
            </ul>
            </div>


        </div>
    </div>
    <!-- Page Header Ends                              -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper pt-3">
        <!-- Page Sidebar Start-->
        <div class="sidebar-wrapper">
            <div>
                <div class="logo-wrapper"><a href="{{url('customer/index')}}">
                    <img class="img-fluid for-light" src="{{asset('assets/images/logo/logo-safe.png')}}" width="124px" alt=""></a>
                    <div class="back-btn"><i class="fa fa-angle-left"></i></div>
                    <!-- <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i>
                    </div> -->
                </div>
                <div class="logo-icon-wrapper"><a href="{{url('customer/index')}}"><img class="img-fluid"
                                                                                   src="{{asset('assets/images/logo/carsafe-logo.webp')}}"
                                                                                   width="50px" alt=""></a></div>
                <nav class="sidebar-main">
                    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
                    <div id="sidebar-menu">
                        <ul class="sidebar-links" id="simple-bar">
                            <li class="back-btn"><a href="{{url('dashboard')}}"><img class="img-fluid"
                                                                                     src="{{asset('assets/images/logo/carsafe-logo.webp')}}"
                                                                                     width="50px" alt=""></a>
                                <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2"
                                                                                      aria-hidden="true"></i></div>
                            </li>
                            <li class="sidebar-main-title">

                                <a href="{{url('customer/index')}}">
                                    <div class="menu-item {{ request()->is('customer/index') ? 'menu-item-active' : '' }}">
                                        <i class="" data-feather="grid"></i>
                                        Dashboard
                                    </div>
                                </a>

                            </li>

                            <li class=" sidebar-main-title ">

                                <a href="{{url('customer/trips')}}">
                                    <div class="menu-item {{ request()->is('customer/trips') ? 'menu-item-active' : '' }}">
                                        <i class="fa fa-history" aria-hidden="true"></i>
                                        Trip History
                                    </div>
                                </a>

                            </li>


                            <li class="sidebar-main-title">

                                <a href="{{url('customer/cards')}}">
                                    <div class="menu-item {{ request()->is('customer/cards') ? 'menu-item-active' : '' }}">
                                        <i data-feather="credit-card"></i>
                                        Payment Methods
                                    </div>
                                </a>

                            </li>
                            <li class="sidebar-main-title">

                                <a href="{{url('customer/payments')}}">
                                    <div class="menu-item {{ request()->is('customer/payments') ? 'menu-item-active' : '' }}">
                                        
                                    <i class="fa fa-usd" ></i>
                                    Payments
                                    </div>
                                </a>

                            </li>

                            <li class="sidebar-main-title">
                            
                                <a href="{{url('customer/pins')}}">
                                    <div class="menu-item {{ request()->is('customer/pins') ? 'menu-item-active' : '' }}"> 
                                        <i data-feather="map-pin"></i>
                                        Account Pins
                                    </div>
                                </a>
                            
                            </li>
                            <li class="sidebar-main-title">
                            
                            <a href="{{url('customer/complaints')}}">
                                <div class="menu-item {{ request()->is('customer/complaints') ? 'menu-item-active' : '' }}">
                                    <i data-feather="alert-circle"></i>
                                    Complaints
                                </div>
                            </a>
                        
                        </li>

                            <li class="sidebar-main-title">

                                <a href="{{url('customer/invoices')}}">
                                    <div class="menu-item {{ request()->is('customer/invoices') ? 'menu-item-active' : '' }}">
                                        <i data-feather="file-text"></i>
                                        Invoices
                                    </div>
                                </a>

                            </li>
                            <li class="sidebar-main-title">

                                <a href="{{url('customer/settings')}}">
                                    <div class="menu-item {{ request()->is('customer/settings') ? 'menu-item-active' : '' }}">
                                        <i data-feather="settings"></i>
                                        Account Settings
                                    </div>
                                </a>

                            </li>


                            <li class="sidebar-main-title">

                                <a href="{{url('customer/logout')}}">
                                    <div style="color:#FF4141 !important;">
                                        <i data-feather="log-out" style="color:#FF4141;"></i>
                                        Logout
                                    </div>
                                </a>

                            </li>
                        </ul>
                    </div>
                    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
                </nav>
            </div>
        </div>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <div class="container-fluid">

            @yield('content')

            <!-- Container-fluid Ends-->
            </div>


        </div>
        <!-- footer start-->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 footer-copyright text-center">
                        <p class="mb-0">Copyright {{ $currentYear }} © CarSafe</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- latest jquery-->
    <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
    <!-- Bootstrap js-->
    <script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <!-- feather icon js-->
    <script src="{{asset('assets/js/icons/feather-icon/feather.min.js')}}"></script>
    <script src="{{asset('assets/js/icons/feather-icon/feather-icon.js')}}"></script>
    <!-- scrollbar js-->
    <script src="{{asset('assets/js/scrollbar/simplebar.js')}}"></script>
    <script src="{{asset('assets/js/scrollbar/custom.js')}}"></script>
    <!-- Sidebar jquery-->
    <script src="{{asset('assets/js/config.js')}}"></script>
    <!-- Plugins JS start-->
    <script src="{{asset('assets/js/sidebar-menu.js')}}"></script>
    {{--    <script src="{{asset('assets/js/chart/chartist/chartist.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/chart/chartist/chartist-plugin-tooltip.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/chart/apex-chart/apex-chart.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/chart/apex-chart/stock-prices.js')}}"></script>--}}
    <script src="{{asset('assets/js/prism/prism.min.js')}}"></script>
    {{--    <script src="{{asset('assets/js/clipboard/clipboard.min.js')}}"></script>--}}
    <script src="{{asset('assets/js/counter/jquery.waypoints.min.js')}}"></script>
    {{--    <script src="{{asset('assets/js/counter/jquery.counterup.min.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/counter/counter-custom.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/custom-card/custom-card.js')}}"></script>--}}
    {{--    <script src="{{asset('assets/js/owlcarousel/owl.carousel.js')}}"></script>--}}
    <script src="{{asset('assets/js/editor/summernote/summernote.js')}}"></script>
    <script src="{{ asset('assets/js/modal-animated.js') }}"></script>
    <script src="{{asset('assets/js/tooltip-init.js')}}"></script>
    <script src="{{asset('assets/js/datatable/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/datatable/datatables/datatable.custom.js')}}"></script>

    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.4/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js"></script>
    {{--    <script src="{{asset('assets/js/script.js')}}"></script>--}}
    <script src="{{asset('assets/js/toastr.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{asset('assets/js/card-js.min.js')}}"></script>

    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script>
        $(".mode").on("click", function () {
            $('.mode i').toggleClass("fa-moon-o").toggleClass("fa-lightbulb-o");
            $('body').toggleClass("dark-only");
        });
        @if (session('success'))
        toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
        toastr.warning("{{ session('error') }}");
        @endif
    </script>
    <script>

        document.addEventListener("DOMContentLoaded", function () {
            const loader = document.getElementById("loader");
            loader.style.display = "none";
        });

    </script>

@yield('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    feather.replace();
});

</script>
 <script>
        // greeting
        var todate = new Date()
        var curHr = todate.getHours()

        if (curHr >= 0 && curHr < 4) {
            document.getElementById("greeting").innerHTML = 'Good Night';
        } else if (curHr >= 4 && curHr < 12) {
            document.getElementById("greeting").innerHTML = 'Good Morning';
        } else if (curHr >= 12 && curHr < 16) {
            document.getElementById("greeting").innerHTML = 'Good Afternoon';
        } else {
            document.getElementById("greeting").innerHTML = 'Good Evening';
        }

        // time
        function startTime() {
            var todate = new Date();
            var h = todate.getHours();
            var m = todate.getMinutes();
            // var s = todate.getSeconds();
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            m = checkTime(m);
            // s = checkTime(s);
            document.getElementById('txt').innerHTML =
                h + ":" + m + ' ' + ampm;
            var t = setTimeout(startTime, 500);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i
            }
            ;  // add zero in front of numbers < 10
            return i;
        }
        startTime();

    </script>
</body>
</html>

@php
use Carbon\Carbon;
$currentYear = Carbon::now()->year;
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
                        <img class="img-fluid" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" alt="">
                    </a>
                </div>
                <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle"
                                               data-feather="align-center"></i></div>

            </div>
            <div class="left-header col horizontal-wrapper ps-0">

            </div>
            <div class="nav-right col-8 pull-right right-header p-0">
                <ul class="nav-menus">

                    <li>
                        <div class="mode"><i class="fa fa-moon-o"></i></div>
                    </li>


                    <li class="profile-nav onhover-dropdown p-0 me-0">
                        <div class="media profile-media">
                            <div class="media-body"><span>{{ Auth::guard('customer')->user()->f_name }}</span>

                            </div>
                        </div>
                        <ul class="profile-dropdown onhover-show-div">

                            <li><a href="{{url('customer/logout')}}"><i data-feather="log-in"> </i><span>LogOut</span></a></li>
                        </ul>
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
                <div class="logo-wrapper"><a href="{{url('customer/index')}}"><img class="img-fluid for-light"
                                                                              src="{{asset('assets/images/logo/carsafe-logo.webp')}}"
                                                                              width="50px" alt=""></a>
                    <div class="back-btn"><i class="fa fa-angle-left"></i></div>
                    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i>
                    </div>
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
                                    <div>
                                        Dashboard
                                    </div>
                                </a>

                            </li>

                            <li class=" sidebar-main-title ">

                                <a href="{{url('customer/trips')}}">
                                    <div>
                                        Trip History
                                    </div>
                                </a>

                            </li>


                            <li class="sidebar-main-title">

                                <a href="{{url('customer/cards')}}">
                                    <div>
                                        Payment Methods
                                    </div>
                                </a>

                            </li>
                            <li class="sidebar-main-title">

                                <a href="{{url('customer/payments')}}">
                                    <div>
                                        Payments
                                    </div>
                                </a>

                            </li>

                            <li class="sidebar-main-title">
                            
                                <a href="{{url('customer/pins')}}">
                                    <div>
                                        Account Pins
                                    </div>
                                </a>
                            
                            </li>


                            <li class="sidebar-main-title">

                                <a href="{{url('customer/invoices')}}">
                                    <div>
                                        Invoices
                                    </div>
                                </a>

                            </li>
                            <li class="sidebar-main-title">

                                <a href="{{url('customer/settings')}}">
                                    <div>
                                        Account Settings
                                    </div>
                                </a>

                            </li>


                            <li class="sidebar-main-title">

                                <a href="{{url('customer/logout')}}">
                                    <div>
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
</body>
</html>

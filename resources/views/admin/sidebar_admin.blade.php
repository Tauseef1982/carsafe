<div>
    <div class="logo-wrapper"><a href="{{url('dashboard')}}"><img class="img-fluid for-light" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="50px"  alt=""></a>
        <div class="back-btn"><i class="fa fa-angle-left"></i></div>
        <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="{{url('dashboard')}}"><img class="img-fluid" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="50px" alt=""></a></div>
    <nav class="sidebar-main">
        <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
        <div id="sidebar-menu">
            <ul class="sidebar-links" id="simple-bar">
                <li class="back-btn"><a href="{{url('dashboard')}}"><img class="img-fluid" src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="50px" alt=""></a>
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                </li>
                <li class="sidebar-main-title">

                    <a href="{{url('admin/dashboard')}}">
                        <div>
                            Dashboard
                        </div>
                    </a>

                </li>

                <li class="sidebar-list" style="  padding:15px 20px; ">
                    <a class="sidebar-link sidebar-title " style="background-color:rgba(115, 102, 255, 0.06);padding-top:15px; padding-bottom:13px;padding-left:20px;" href="#"><span class="lan-">Drivers</span></a>
                    <ul class="sidebar-submenu">
                      <li><a class="lan-" href="{{url('admin/drivers')}}">All Active Drivers</a></li>
                    <li><a class="lan-" href="{{url('admin/inactive-drivers')}}">Inactive Drivers</a></li>
                    <li><a class="lan-" href="{{url('admin/drivers?show_negative=true')}}">Negative Balance</a></li>
                    <li><a class="lan-" href="{{url('admin/driver_complaints')}}">Complaints</a></li>   
                </ul>
                  </li>
                <li class="sidebar-main-title">

                    <a href="{{url('admin/payments')}}">
                        <div>
                            Payments
                        </div>
                    </a>

                </li>

                <li class="sidebar-list" style="  padding:15px 20px; ">
                    <a class="sidebar-link sidebar-title " style="background-color:rgba(115, 102, 255, 0.06);padding-top:15px; padding-bottom:13px;padding-left:20px;" href="#"><span class="lan-">Trips</span></a>
                    <ul class="sidebar-submenu">
                      <li><a class="lan-" href="{{url('admin/trips2?tab=all')}}">All Trips</a></li>
{{--                      <li><a class="lan-" href="{{url('admin/trips2?tab=paid')}}">Paid Trips</a></li>--}}
                      <li><a class="lan-" href="{{url('admin/tripswithoutestimatedcost')}}">Trips Cost Added by Driver</a></li>
                      <li><a class="lan-" href="{{url('admin/trips_complaint')}}">Trips With Complaint</a></li>
                      <li><a class="lan-" href="{{url('admin/trips_extra')}}">Trips With Extra</a></li>
                    </ul>
                  </li>
                <li class="sidebar-main-title">

                    <a href="{{url('admin/adjustments')}}">
                        <div>
                            Adjustments
                        </div>
                    </a>

                </li>
                <li class="sidebar-main-title">

                <a href="{{url('admin/dispatchers')}}">
                   <div>
                      Dispatchers
                      </div>
                </a>

           </li>


                <li class="sidebar-list" style="  padding:15px 20px; ">
                    <a class="sidebar-link sidebar-title " style="background-color:rgba(115, 102, 255, 0.06);padding-top:15px; padding-bottom:13px;padding-left:20px;" href="#"><span class="lan-">Accounts</span></a>
                    <ul class="sidebar-submenu">
                        <li><a class="lan-" href="{{url('admin/accounts')}}">All Accounts</a></li>
                        <li><a class="lan-" href="{{url('admin/accounts/cards')}}">Accounts Cards</a></li>
                        <li><a class="lan-" href="{{url('admin/accounts/invoices')}}">Invoices</a></li>
                        <li><a class="lan-" href="{{url('admin/accounts/complaints')}}">Complaints</a></li>
                        <li><a class="lan-" href="{{url('admin/accounts/cron-postpaid')}}">Generate Invoices</a></li>
                    </ul>
                </li>
                <li class="sidebar-main-title">
                  <a href="{{url('admin/discounts')}}">
                    <div>
                       Discounts Settings
                    </div>
                 </a>
                </li>
                <li class="sidebar-main-title">
               <a href="{{url('admin/accounts/payments')}}">
                   <div>
                       Accounts Payments
                   </div>
                </a>
               </li>
                <li class="sidebar-main-title">

                    <a href="{{route('admin.logout')}}">
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

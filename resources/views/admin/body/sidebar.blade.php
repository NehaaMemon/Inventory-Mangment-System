   <div class="app-sidebar-menu">
                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <div class="logo-box">
                            <a href="index.html" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('backend/assets/images/logo-light.png') }}" alt="" height="24">
                                </span>
                            </a>
                            <a href="index.html" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="" height="24">
                                </span>
                            </a>
                        </div>

                        <ul id="side-menu">

                            <li class="menu-title">Menu</li>
                            <li>
                                <a href="{{ route('dashboard') }}" >
                                    <i data-feather="home"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>

                            <!-- <li>
                                <a href="landing.html" target="_blank">
                                    <i data-feather="globe"></i>
                                    <span> Landing </span>
                                </a>
                            </li> -->

                            <li class="menu-title">Pages</li>

                            <li>
                                <a href="#sidebarAuth" data-bs-toggle="collapse">
                                    {{-- <i data-feather="users"></i> --}}
                                    <i class="fa-regular fa-copyright"></i>
                                    <span> Brands </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAuth">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('all.brand') }}" class="tp-link">All Brand</a>
                                        </li>
                                         <li>
                                            <a href="{{ route('add.brand') }}" class="tp-link">Add Brand</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                                <li>
                                <a href="#sidebarBaseui" data-bs-toggle="collapse">
                                    {{-- <i data-feather="package"></i> --}}
                                 <i class="fa-regular fa-building fa-sm"></i>
                                    <span> Warehouse </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBaseui">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('all.warehouse') }}"class="tp-link">All Warehouses</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('add.warehouse') }}" class="tp-link">Add Warehouse</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                             <li>
                                <a href="#supplier" data-bs-toggle="collapse">
                                    {{-- <i data-feather="package"></i> --}}
                                    <i class="fa-solid fa-truck fa-sm"></i>

                                    <span> Supplier </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="supplier">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('all.supplier') }}"class="tp-link">All Supplier</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('add.supplier') }}" class="tp-link">Add Supplier</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                                <li>
                                <a href="#customer" data-bs-toggle="collapse">
                                    <i data-feather="user"></i>


                                    <span>Customer </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="customer">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('customers.index') }}"class="tp-link">All Customer</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('customers.create') }}" class="tp-link">Add Customer</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                             <li>
                                <a href="#product" data-bs-toggle="collapse">
                                    <i class="fa-solid fa-table-cells-large"></i>


                                    <span>Product Manage </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="product">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('category.index') }}"class="tp-link">All Category</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('product.index') }}" class="tp-link">All product</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                              <li>
                                <a href="#purchase" data-bs-toggle="collapse">
                                    <i class="fa-solid fa-bag-shopping"></i>


                                    <span>Purchase Manage </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="purchase">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('purchase.index') }}"class="tp-link">All Purchase</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('purchase.create') }}" class="tp-link">Add Purchase</a>
                                        </li>
                                           <li>
                                            <a href="{{ route('return-purchase.index') }}" class="tp-link">All Return Purchase</a>
                                                   </li>
                                    </ul>
                                </div>
                            </li>

                                     {{-- Sale --}}
                               <li>
                                <a href="#sale" data-bs-toggle="collapse">
                                    <i class="fa-solid fa-bag-shopping"></i>


                                    <span>Sale Manage </span>
                                    <span class="menu-arrow"></span>

                                </a>
                                <div class="collapse" id="sale">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="{{ route('sale.index') }}"class="tp-link">All Sale</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>


                            <li>
                                <a href="#sidebarError" data-bs-toggle="collapse">
                                    <i data-feather="alert-octagon"></i>
                                    <span> Error Pages </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarError">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="error-404.html" class="tp-link">Error 404</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>



                            <li class="menu-title mt-2">General</li>



                            <li>
                                <a href="widgets.html" class="tp-link">
                                    <i data-feather="aperture"></i>
                                    <span> Widgets </span>
                                </a>
                            </li>

                            <li>
                                <a href="#sidebarAdvancedUI" data-bs-toggle="collapse">
                                    <i data-feather="cpu"></i>
                                    <span> Extended UI </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAdvancedUI">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="extended-carousel.html" class="tp-link">Carousel</a>
                                        </li>
                                        <li>
                                            <a href="extended-notifications.html" class="tp-link">Notifications</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarIcons" data-bs-toggle="collapse">
                                    <i data-feather="award"></i>
                                    <span> Icons </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarIcons">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="icons-feather.html" class="tp-link">Feather Icons</a>
                                        </li>
                                        <li>
                                            <a href="icons-mdi.html" class="tp-link">Material Design Icons</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
            </div>
<script>
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

    </script>

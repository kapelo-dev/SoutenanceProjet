<!-- Header -->
<header class="kt-header fixed end-0 start-0 top-0 z-10 flex shrink-0 items-stretch bg-background shadow-md" data-kt-sticky="true"
    data-kt-sticky-class="border-b border-border shadow-lg" data-kt-sticky-name="header" id="header">
    <!-- Container -->
    <div class="kt-container-fixed flex items-stretch justify-between lg:gap-4" id="headerContainer">
        <!-- Mobile Logo -->
        <div class="-ms-1 flex items-center gap-2.5 lg:hidden">
            <a class="shrink-0 dark:hidden" href="{{ url('/') }}" title="PDV Connect">
                <img class="h-9 max-h-[40px] w-auto" src="{{ asset('assets/media/app/mini-logo-v2.svg') }}" alt="PDV Connect" />
            </a>
            <a class="shrink-0 hidden dark:block" href="{{ url('/') }}" title="PDV Connect">
                <img class="h-6 max-h-[40px] w-auto" src="{{ asset('assets/media/app/mini-logo-v2-dark.svg') }}" alt="PDV Connect" />
            </a>
            <div class="flex items-center">
                <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#sidebar">
                    <i class="ki-filled ki-menu">
                    </i>
                </button>
                <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#mega_menu_wrapper">
                    <i class="ki-filled ki-burger-menu-2">
                    </i>
                </button>
            </div>
        </div>
        <!-- End of Mobile Logo -->
        @include('partials.mega-menu')
        <!-- Topbar -->
        <div class="flex items-center gap-2.5">
          
            @include('partials.topbar-user-dropdown')
        </div>
        <!-- End of Topbar -->
    </div>
    <!-- End of Container -->
</header>
<!-- End of Header -->

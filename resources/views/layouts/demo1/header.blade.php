<!-- Header -->
<header class="kt-header fixed end-0 start-0 top-0 z-10 flex shrink-0 items-stretch bg-background shadow-md" 
data-kt-sticky="true"
data-kt-sticky-class="border-b border-border shadow-lg" 
data-kt-sticky-name="header" 
id="header"
style="background-image: url('{{ asset('assets/media/images/bg-cover2.png') }}'); background-size: 40%; background-position: center 25%; background-repeat: repeat;"><div class="kt-container-fixed flex items-stretch justify-between lg:gap-4" id="headerContainer">
<!-- Mobile Logo -->
<div class="-ms-1 flex items-center gap-2.5 lg:hidden">
<a class="shrink-0 flex items-center gap-2.5" href="{{ url('/') }}" title="PDV Connect">
<span class="flex items-center justify-center size-8 rounded-full bg-[#314e6c]" style="font-family: 'Geist Sans', system-ui, sans-serif;">
<span class="text-xs font-bold leading-none"><span class="text-white">P</span><span class="text-[#fbbf24]">C</span></span>
</span>
<span class="text-base font-extrabold tracking-tight leading-none" style="font-family: 'Geist Sans', system-ui, sans-serif;">
<span class="text-[#314e6c]">PDV</span><span class="text-[#fbbf24]"> Connect</span>
</span>
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
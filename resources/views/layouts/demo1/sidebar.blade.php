<!-- Sidebar -->
<style>
#sidebar .kt-menu-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
#sidebar .kt-menu-item-active .kt-menu-link {
    background-color: rgba(255, 255, 255, 0.15);
}
/* Forcer tous les textes en blanc dans le sidebar */
#sidebar .kt-menu-title,
#sidebar .kt-menu-heading {
    color: white !important;
}
/* Forcer toutes les icônes en jaune clair */
#sidebar .kt-menu-icon {
    color:rgb(241, 161, 12) !important;
}
#sidebar .kt-menu-arrow {
    color:rgb(223, 190, 47) !important;
}
/* Style pour le bouton de toggle du sidebar */
#sidebar_toggle {
    background-color: #6b7280 !important; /* Gris */
    border-color: #6b7280 !important;
}
#sidebar_toggle:hover {
    background-color: #4b5563 !important; /* Gris plus foncé au hover */
}
#sidebar_toggle i {
    color: #fefce8 !important; /* Jaune clair pour la flèche */
}
/* Gestion des logos selon l'état du sidebar */
.default-logo {
    display: block;
}
.small-logo {
    display: none;
}
/* Quand le sidebar est réduit - cacher seulement le texte */
body.kt-sidebar-collapse .default-logo {
    display: none !important;
}
body.kt-sidebar-collapse .small-logo {
    display: block !important;
}
/* Quand le sidebar est en hover (s'ouvre), réafficher le texte */
body.kt-sidebar-collapse #sidebar:hover .default-logo {
    display: block !important;
}
body.kt-sidebar-collapse #sidebar:hover .small-logo {
    display: none !important;
}
</style>
<div class="kt-sidebar fixed bottom-0 top-0 z-20 hidden shrink-0 flex-col items-stretch border-e border-e-slate-700 [--kt-drawer-enable:true] lg:flex lg:[--kt-drawer-enable:false]"
    style="background-color: #1e293b;" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header relative hidden shrink-0 items-center justify-between px-3 lg:flex lg:px-6"
        id="sidebar_header">
        <a class="flex items-center gap-2.5 justify-center" href="#">
            <img class="default-logo h-8 w-auto" src="{{ asset('assets/media/brand-logos/uex.svg') }}" alt="PDV Connecté" />
            <img class="small-logo h-8 w-auto" src="{{ asset('assets/media/brand-logos/uex.svg') }}" alt="PDV" />
            <span class="default-logo text-white font-semibold text-base">PDV_CONNECT</span>
        </a>
        <button
            class="kt-btn kt-btn-outline kt-btn-icon absolute start-full top-2/4 size-[30px] -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
            data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <i
                class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 rtl:translate rtl:kt-toggle-active:rotate-0 transition-all duration-300 rtl:rotate-180 text-yellow-50">
            </i>
        </button>
    </div>
    <div class="kt-sidebar-content flex shrink-0 grow py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover flex shrink-0 grow pe-1 ps-2 lg:pe-3 lg:ps-5" data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <!-- Sidebar Menu -->
            <div class="kt-menu flex grow flex-col gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false"
                id="sidebar_menu">
                <div class="kt-menu-item">
                    <a href="{{ url('/dashboard') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">

                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-element-11 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary text-sm font-medium text-foreground">
                            Dashboard
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-white/70">
                        User
                    </span>
                </div>
                <div class="kt-menu-item">
                    <a href="{{ url('/transactions') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-profile-circle text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Transactions
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-setting-2 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Agents
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-yellow-50">
                            <span class="kt-menu-item-show:hidden inline-flex">
                                <i class="ki-filled ki-plus text-[11px]">
                                </i>
                            </span>
                            <span class="kt-menu-item-show:inline-flex hidden">
                                <i class="ki-filled ki-minus text-[11px]">
                                </i>
                            </span>
                        </span>
                    </div>
                    <div
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-slate-600">
                        <div class="kt-menu-item">
                            <a href="{{ url('/agents/liste-agents') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                    Liste des Agents
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a href="{{ url('/agents/soldes') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                   Soldes
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-shop text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Kiosques
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-yellow-50">
                            <span class="kt-menu-item-show:hidden inline-flex">
                                <i class="ki-filled ki-plus text-[11px]">
                                </i>
                            </span>
                            <span class="kt-menu-item-show:inline-flex hidden">
                                <i class="ki-filled ki-minus text-[11px]">
                                </i>
                            </span>
                        </span>
                    </div>
                    <div
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-slate-600">
                        <div class="kt-menu-item">
                            <a href="{{ url('/kiosques') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                    Liste des Kiosques
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a href="{{ url('/kiosques-carte') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                    Carte des Kiosques
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <a href="{{ url('/utilisateurs') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"   
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-users text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Utilisateurs
                        </span>
                        
                    </a>
                   
                </div>
               
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-white/70">
                        Finance
                    </span>
                </div>
                <div class="kt-menu-item">
                    <a href="{{ url('/rapports') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-setting text-lg">
                            </i>
                        </span>
                        <span class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                           Rapports
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item">
                    <a href="{{ url('/operations-agence') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-bank text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Opérations en Agence
                        </span>
                    </a>
                </div>

                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-white/70">
                        Configuration
                    </span>
                </div>
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-setting-2 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Role et permissions
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-yellow-50">
                            <span class="kt-menu-item-show:hidden inline-flex">
                                <i class="ki-filled ki-plus text-[11px]">
                                </i>
                            </span>
                            <span class="kt-menu-item-show:inline-flex hidden">
                                <i class="ki-filled ki-minus text-[11px]">
                                </i>
                            </span>
                        </span>
                    </div>
                    <div
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-slate-600">
                        <div class="kt-menu-item">
                            <a href="{{ url('/roles-et-permissions/gestion-roles') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium kt-menu-link-hover:!text-primary me-1 font-normal text-foreground">
                                    Gestion des roles
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a href="{{ url('/roles-et-permissions/gestion-permissions') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                    Gestion des permissions
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a href="{{ url('/roles-et-permissions/gestion-routes') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px]"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-yellow-50 kt-menu-item-hover:before:bg-yellow-50 relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-yellow-50 kt-menu-item-active:font-medium kt-menu-link-hover:!text-yellow-50 me-1 font-normal text-white">
                                   Gestion des routes
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item">
                    <a href="{{ url('/operateurs') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-yellow-50">
                            <i class="ki-filled ki-phone text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-yellow-50 kt-menu-link-hover:!text-yellow-50 text-sm font-medium text-white">
                            Opérateurs Mobile Money
                        </span>
                    </a>
                </div>
              
            </div>
            <!-- End of Sidebar Menu -->
        </div>
    </div>
</div>
<!-- End of Sidebar -->

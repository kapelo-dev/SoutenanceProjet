<!-- Sidebar -->
<div class="kt-sidebar fixed bottom-0 top-0 z-20 hidden shrink-0 flex-col items-stretch border-e border-e-border bg-background [--kt-drawer-enable:true] lg:flex lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header relative hidden shrink-0 items-center justify-between px-3 lg:flex lg:px-6"
        id="sidebar_header">
        <a href="{{ url('/') }}" title="PDV Connect" class="sidebar-brand-link flex w-full items-center gap-3">
            <img src="{{ asset('assets/media/app/pdv-connect-logo.svg') }}" alt="PDV Connect" class="default-logo h-10 w-auto max-w-[200px] object-contain object-left" />
            <img src="{{ asset('assets/media/app/pdv-connect-logo-mini.svg') }}" alt="PDV Connect" class="small-logo h-9 w-9 shrink-0 object-contain mx-auto" />
        </a>
        <button
            class="kt-btn kt-btn-outline kt-btn-icon absolute start-full top-2/4 size-[30px] -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
            data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <i
                class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 rtl:translate rtl:kt-toggle-active:rotate-0 transition-all duration-300 rtl:rotate-180">
            </i>
        </button>
    </div>
    <div class="kt-sidebar-content flex shrink-0 grow py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover flex shrink-0 grow pe-1 ps-2 lg:pe-3 lg:ps-5" data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <!-- Sidebar Menu -->
            <div class="kt-menu flex grow flex-col gap-1 kt-permissions-loading" data-kt-menu="true" data-kt-menu-accordion-expand-all="false"
                id="sidebar_menu">
                <div class="kt-sidebar-menu-skeleton" aria-hidden="true">
                    <span style="width:88%"></span>
                    <span style="width:72%"></span>
                    <span style="width:80%"></span>
                    <span style="width:65%"></span>
                    <span style="width:76%"></span>
                    <span style="width:70%"></span>
                </div>
                @auth
                <script type="application/json" id="menu-permissions-data">@json($userMenuPermissions ?? ['success' => false, 'routes' => []])</script>
                @endauth
                <div class="kt-menu-item {{ request()->is('dashboard') && !request()->is('dashboard/technique*') && !request()->is('dashboard/securite*') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/dashboard') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">

                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-element-11 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Dashboard
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item {{ request()->is('dashboard/technique*') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ route('dashboard.technique') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-chart-line-up-2 text-lg"></i>
                        </span>
                        <span class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Dashboard Technique
                        </span>
                    </a>
                </div>
                <div class="kt-menu-item {{ request()->is('dashboard/securite*') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ route('dashboard.securite') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-shield-search text-lg"></i>
                        </span>
                        <span class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Dashboard Sécurité
                        </span>
                    </a>
                </div>
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-muted-foreground">
                        User
                    </span>
                </div>
                <div class="kt-menu-item {{ request()->is('transactions') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/transactions') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-profile-circle text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Transactions
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item {{ request()->is('agent/dashboard') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/agent/dashboard') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-chart-line-up text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Mon Dashboard
                        </span>
                        
                    </a>
                </div>
                <div class="kt-menu-item {{ request()->is('agents/*') ? 'kt-menu-item-active kt-menu-item-show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-setting-2 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Agents
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-muted-foreground">
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
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-border">
                        <div class="kt-menu-item {{ request()->is('agents/liste-agents') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/agents/liste-agents') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Liste des Agents
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('agents/soldes') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/agents/soldes') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                   Soldes
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item {{ request()->is('kiosques*') ? 'kt-menu-item-active kt-menu-item-show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-shop text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Kiosques
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-muted-foreground">
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
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-border">
                        <div class="kt-menu-item {{ request()->is('kiosques') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/kiosques') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Liste des Kiosques
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('kiosques-carte') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/kiosques-carte') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Carte des Kiosques
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item {{ request()->is('utilisateurs') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/utilisateurs') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"   
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-users text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Utilisateurs
                        </span>
                        
                    </a>
                   
                </div>
               
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-muted-foreground">
                        Finance
                    </span>
                </div>
                <a href="{{ url('/rapports') }}" class="kt-menu-item {{ request()->is('rapports') ? 'kt-menu-item-active' : '' }}">
                    <div class="kt-menu-label gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        href="" tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-setting text-lg">
                            </i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground">
                           Rapports
                        </span>
                        
                    </div>
                </a>
                <div class="kt-menu-item {{ request()->is('operations-agence') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/operations-agence') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-bank text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Opérations en Agence
                        </span>
                    </a>
                </div>

                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-muted-foreground">
                        Entreprise
                    </span>
                </div>
                <div class="kt-menu-item {{ request()->is('gestion-entreprise') ? 'kt-menu-item-active kt-menu-item-show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-office-bag text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Gestion d'entreprise
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-muted-foreground">
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
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-border">
                        <div class="kt-menu-item {{ request()->fullUrlIs('*gestion-entreprise?onglet=salaires*') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/gestion-entreprise?onglet=salaires') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Salaires
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->fullUrlIs('*gestion-entreprise?onglet=parametres*') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/gestion-entreprise?onglet=parametres') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Paramètres Salaire
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->fullUrlIs('*gestion-entreprise?onglet=tresorerie*') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/gestion-entreprise?onglet=tresorerie') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Trésorerie
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="kt-menu-item pt-2.25 pb-px">
                    <span
                        class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-muted-foreground">
                        Configuration
                    </span>
                </div>
                <div class="kt-menu-item {{ request()->is('roles-et-permissions/*') ? 'kt-menu-item-active kt-menu-item-show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-setting-2 text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
                            Role et permissions
                        </span>
                        <span
                            class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-muted-foreground">
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
                        class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-border">
                        <div class="kt-menu-item {{ request()->is('roles-et-permissions/gestion-roles') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/roles-et-permissions/gestion-roles') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Gestion des roles
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('roles-et-permissions/gestion-permissions') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/roles-et-permissions/gestion-permissions') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Gestion des permissions
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('roles-et-permissions/gestion-routes') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/roles-et-permissions/gestion-routes') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                   Gestion des routes
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('parametres-app-mobile') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/parametres-app-mobile') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Configuration App Mobile
                                </span>
                            </a>
                        </div>
                        <div class="kt-menu-item {{ request()->is('system-logs*') ? 'kt-menu-item-active' : '' }}">
                            <a href="{{ url('/system-logs') }}" class="kt-menu-link grow cursor-pointer gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] rounded-md"
                                tabindex="0">
                                <span
                                    class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                </span>
                                <span
                                    class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-medium me-1 font-normal text-foreground">
                                    Logs Système
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="kt-menu-item {{ request()->is('operateurs') ? 'kt-menu-item-active' : '' }}">
                    <a href="{{ url('/operateurs') }}" class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] rounded-md"
                        tabindex="0">
                        <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                            <i class="ki-filled ki-phone text-lg">
                            </i>
                        </span>
                        <span
                            class="kt-menu-title kt-menu-item-active:text-primary text-sm font-medium text-foreground">
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

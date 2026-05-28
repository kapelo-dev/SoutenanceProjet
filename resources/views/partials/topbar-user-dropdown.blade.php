<!-- User -->
@auth
<div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-offset-rtl="-20px, 10px"
    data-kt-dropdown-placement="bottom-end" data-kt-dropdown-placement-rtl="bottom-start" data-kt-dropdown-trigger="click">
    <div class="shrink-0 cursor-pointer" data-kt-dropdown-toggle="true">
        @if(auth()->user()->photo_profil)
            <img alt="{{ auth()->user()->nom_complet }}" 
                class="size-9 shrink-0 rounded-full border-2 border-green-500 object-cover"
                src="{{ asset('storage/' . auth()->user()->photo_profil) }}" />
        @else
            <div class="size-9 shrink-0 rounded-full border-2 border-green-500 bg-green-100 dark:bg-green-900 flex items-center justify-center">
                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                    {{ strtoupper(substr(auth()->user()->prenom ?? auth()->user()->nom, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '', 0, 1)) }}
                </span>
            </div>
        @endif
    </div>
    <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
        <div class="flex items-center justify-between gap-1.5 px-2.5 py-1.5">
            <div class="flex items-center gap-2">
                @if(auth()->user()->photo_profil)
                    <img alt="{{ auth()->user()->nom_complet }}" 
                        class="size-9 shrink-0 rounded-full border-2 border-green-500 object-cover"
                        src="{{ asset('storage/' . auth()->user()->photo_profil) }}" />
                @else
                    <div class="size-9 shrink-0 rounded-full border-2 border-green-500 bg-green-100 dark:bg-green-900 flex items-center justify-center">
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ strtoupper(substr(auth()->user()->prenom ?? auth()->user()->nom, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '', 0, 1)) }}
                        </span>
                    </div>
                @endif
                <div class="flex flex-col gap-1.5">
                    <span class="text-sm font-semibold leading-none text-foreground">
                        {{ auth()->user()->nom_complet }}
                    </span>
                    <a class="hover:text-primary text-xs font-medium leading-none text-secondary-foreground"
                        href="#">
                        {{ auth()->user()->email }}
                    </a>
                </div>
            </div>
            @if(auth()->user()->statut === 'actif')
                <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">
                    Actif
                </span>
            @endif
        </div>
        <ul class="kt-dropdown-menu-sub">
            <li>
                <div class="kt-dropdown-menu-separator">
                </div>
            </li>
            <li>
                <a class="kt-dropdown-menu-link" href="javascript:void(0)" onclick="loadUserProfile({{ auth()->user()->id }})">
                    <i class="ki-filled ki-profile-circle">
                    </i>
                    Mon Profil
                </a>
            </li>
            <li>
                <div class="kt-dropdown-menu-separator">
                </div>
            </li>
        </ul>
        <div class="mb-2.5 flex flex-col gap-3.5 px-2.5 pt-1.5">
            <div class="hidden flex items-center justify-between gap-2">
                <span class="flex items-center gap-2">
                    <i class="ki-filled ki-moon text-base text-muted-foreground">
                    </i>
                    <span class="text-2sm font-medium">
                        Mode Sombre
                    </span>
                </span>
                <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true"
                    name="check" type="checkbox" value="1" />
            </div>
            <form action="{{ route('logout') }}" method="POST" data-ajax="false">
                @csrf
                <button type="submit" class="kt-btn kt-btn-outline w-full justify-center">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</div>
@else
<div class="shrink-0">
    <a href="{{ route('login') }}" class="kt-btn kt-btn-primary">
        Connexion
    </a>
</div>
@endauth
<!-- End of User -->

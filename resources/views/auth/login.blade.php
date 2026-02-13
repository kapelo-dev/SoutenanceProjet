<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="fr">
<head>
    <base href="{{ url('/') }}">
    <title>Connexion - PDV Connect</title>
    <meta charset="utf-8"/>
    <meta content="follow, index" name="robots"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta content="Page de connexion" name="description"/>
    <link href="{{ asset('assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
<link href="{{ asset('assets/media/app/favicon.ico') }}" rel="shortcut icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light';
        let themeMode;

        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.classList.add(themeMode);
        }
    </script>
    <!-- End of Theme Mode -->
    
    <!-- Page -->
    <style>
        .branded-bg {
            background-image: url('{{ asset('assets/media/images/2600x1600/1.png') }}');
        }
        .dark .branded-bg {
            background-image: url('{{ asset('assets/media/images/2600x1600/1-dark.png') }}');
        }
    </style>
    
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="kt-card max-w-[370px] w-full">
                <form action="{{ route('login') }}" class="kt-card-content flex flex-col gap-5 p-10" id="sign_in_form" method="POST">
                    @csrf
                    
                    <div class="text-center mb-2.5">
                        <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                            Connexion
                        </h3>
                        <div class="flex items-center justify-center font-medium">
                            <span class="text-sm text-secondary-foreground me-1.5">
                                Besoin d'un compte?
                            </span>
                            <a class="text-sm link" href="#">
                                Contactez l'administrateur
                            </a>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="kt-alert kt-alert-danger">
                            <div class="kt-alert-content">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="kt-alert kt-alert-success">
                            <div class="kt-alert-content">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">
                            Email
                        </label>
                        <input 
                            class="kt-input @error('email') kt-input-error @enderror" 
                            name="email" 
                            type="email" 
                            placeholder="email@email.com" 
                            value="{{ old('email') }}" 
                            required
                            autofocus
                        />
                        @error('email')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-1">
                            <label class="kt-form-label font-normal text-mono">
                                Mot de passe
                            </label>
                            <a class="text-sm kt-link shrink-0" href="#">
                                Mot de passe oublié?
                            </a>
                        </div>
                        <div class="kt-input @error('password') kt-input-error @enderror" data-kt-toggle-password="true">
                            <input 
                                name="password" 
                                placeholder="Entrez votre mot de passe" 
                                type="password" 
                                required
                            />
                            <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                                <span class="kt-toggle-password-active:hidden">
                                    <i class="ki-filled ki-eye text-muted-foreground"></i>
                                </span>
                                <span class="hidden kt-toggle-password-active:block">
                                    <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                                </span>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>

                    <label class="kt-label">
                        <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
                        <span class="kt-checkbox-label">
                            Se souvenir de moi
                        </span>
                    </label>

                    <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit">
                        Se connecter
                    </button>
                </form>
            </div>
        </div>
        
        <div class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg">
            <div class="flex flex-col p-8 lg:p-16 gap-4">
                <a href="{{ url('/') }}">
                    <img class="h-[28px] max-w-none" src="{{ asset('assets/media/app/mini-logo-v2.svg') }}" alt="PDV Connect"/>
                </a>
                <div class="flex flex-col gap-3">
                    <h3 class="text-2xl font-semibold text-mono">
                        Portail d'Accès Sécurisé
                    </h3>
                    <div class="text-base font-medium text-secondary-foreground">
                        Une passerelle d'authentification robuste garantissant
                        <br/>
                        un accès
                        <span class="text-mono font-semibold">
                            sécurisé et efficace
                        </span>
                        à l'interface
                        <br/>
                        du tableau de bord PDV Connect.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Page -->
    
    <!-- Scripts -->
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    @vite(['resources/js/app.js'])
    <!-- End of Scripts -->
</body>
</html>

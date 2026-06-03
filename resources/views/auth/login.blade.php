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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
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
        .login-gradient-bg {
            background: linear-gradient(160deg, #0a0a0a 0%, #1a1a2e 40%, #16213e 70%, #0f3460 100%);
            position: relative;
            overflow: hidden;
        }
        .login-gradient-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(251,191,36,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .login-gradient-bg::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(49,78,108,0.15) 0%, transparent 60%);
            pointer-events: none;
        }
    </style>
    
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="max-w-[420px] w-full">
                <form action="{{ route('login') }}" method="POST" id="sign_in_form" class="flex flex-col gap-6">
                    @csrf

                    <!-- Header -->
                    <div class="flex flex-col gap-2 mb-2">
                        <h3 class="text-2xl font-bold text-foreground leading-none" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                            Connexion
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Entrez vos identifiants pour accéder à votre espace.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-information-2 text-red-500 text-lg shrink-0 mt-0.5"></i>
                                <div class="flex flex-col gap-1 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <span>{{ $error }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-check-circle text-green-600 text-lg shrink-0"></i>
                                <span class="text-sm text-green-700">{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Email -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Adresse email
                        </label>
                        <div class="kt-input @error('email') kt-input-error @enderror">
                            <i class="ki-filled ki-sms text-muted-foreground text-sm"></i>
                            <input
                                name="email"
                                type="email"
                                placeholder="email@exemple.com"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            />
                        </div>
                        @error('email')
                            <span class="text-xs text-destructive flex items-center gap-1">
                                <i class="ki-filled ki-information-2 text-xs"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-foreground">
                                Mot de passe
                            </label>
                            <a class="text-xs font-medium text-[#314e6c] hover:text-[#263d56] transition-colors" href="#">
                                Mot de passe oublié?
                            </a>
                        </div>
                        <div class="kt-input @error('password') kt-input-error @enderror" data-kt-toggle-password="true">
                            <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
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
                            <span class="text-xs text-destructive flex items-center gap-1">
                                <i class="ki-filled ki-information-2 text-xs"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Remember + Submit -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
                            <span class="text-sm text-muted-foreground">
                                Se souvenir de moi
                            </span>
                        </label>
                    </div>

                    <button class="kt-btn kt-btn-primary flex justify-center grow h-11 text-sm font-semibold" type="submit">
                        Se connecter
                    </button>

                    <!-- Divider -->
                    <div class="flex items-center gap-3">
                        <div class="h-px grow bg-border"></div>
                        <span class="text-xs text-muted-foreground shrink-0">ou</span>
                        <div class="h-px grow bg-border"></div>
                    </div>

                    <!-- Bottom link -->
                    <div class="text-center">
                        <span class="text-sm text-muted-foreground">Pas encore de compte?</span>
                        <a class="text-sm font-semibold text-[#314e6c] hover:text-[#263d56] transition-colors ms-1" href="#">
                            Contactez l'administrateur
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="order-1 lg:order-2 login-gradient-bg flex flex-col justify-between p-8 lg:p-12">
            <div class="relative z-10 flex flex-col gap-4">
                <!-- Logo -->
                <a href="{{ url('/') }}" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                    <span class="text-3xl font-extrabold tracking-tight">
                        <span class="text-[#314e6c]">PDV</span><span class="text-[#fbbf24]"> Connect</span>
                    </span>
                </a>
                <!-- Heading -->
                <div class="flex flex-col gap-3 mt-4">
                    <h3 class="text-3xl lg:text-4xl font-bold text-white leading-snug" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                        Gérez vos kiosques<br/>en toute <span class="text-[#fbbf24]">simplicité</span>
                    </h3>
                    <p class="text-base text-white/50 leading-relaxed max-w-xs">
                        Plateforme centralisée de gestion des points de vente, agents et transactions financières.
                    </p>
                </div>
            </div>

            <!-- Bottom cards -->
            <div class="relative z-10 flex flex-col gap-4 mt-8">
                <!-- Card 1 -->
                <div class="rounded-xl bg-white/[0.07] backdrop-blur-sm border border-white/10 p-5 flex items-start gap-4">
                    <div class="flex items-center justify-center shrink-0 size-10 rounded-lg bg-[#fbbf24]/15">
                        <i class="ki-filled ki-shield-tick text-[#fbbf24] text-lg"></i>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-base font-semibold text-white" style="font-family: 'Geist Sans', system-ui, sans-serif;">Sécurité renforcée</span>
                        <span class="text-sm text-white/50 leading-relaxed">Authentification multi-facteurs et chiffrement de bout en bout pour protéger vos données sensibles.</span>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="rounded-xl bg-white/[0.07] backdrop-blur-sm border border-white/10 p-5 flex items-start gap-4">
                    <div class="flex items-center justify-center shrink-0 size-10 rounded-lg bg-[#314e6c]/30">
                        <i class="ki-filled ki-chart-line-up text-[#5b8fb9] text-lg"></i>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-base font-semibold text-white" style="font-family: 'Geist Sans', system-ui, sans-serif;">Suivi en temps réel</span>
                        <span class="text-sm text-white/50 leading-relaxed">Tableau de bord interactif avec indicateurs clés, alertes instantanées et rapports détaillés.</span>
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

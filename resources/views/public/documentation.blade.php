@extends('layouts.demo9.base')

@section('content')
<div class="kt-container-fixed py-10">
    <!-- Hero Section -->
    <div class="kt-card mb-7.5">
        <div class="kt-card-content p-10 lg:p-15 text-center">
            <i class="ki-filled ki-book text-5xl text-primary mb-5"></i>
            <h1 class="text-3xl lg:text-4xl font-bold text-mono mb-5">Documentation</h1>
            <p class="text-lg text-muted-foreground max-w-3xl mx-auto">
                Bienvenue dans notre centre de documentation. Trouvez tous les guides, tutoriels et ressources 
                dont vous avez besoin pour utiliser efficacement notre plateforme.
            </p>
        </div>
    </div>

    <!-- Quick Start Section -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5">
            <i class="ki-filled ki-rocket text-primary me-2"></i>
            Démarrage Rapide
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10 mb-5">
                        <i class="ki-filled ki-user-tick text-2xl text-success"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">1. Créer un Compte</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Inscrivez-vous en quelques clics et commencez à utiliser la plateforme immédiatement.
                    </p>
                    <a href="#inscription" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-success">
                        En savoir plus
                        <i class="ki-filled ki-right text-xs"></i>
                    </a>
                </div>
            </div>

            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10 mb-5">
                        <i class="ki-filled ki-setting-2 text-2xl text-info"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">2. Configuration</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Personnalisez votre espace de travail selon vos besoins et préférences.
                    </p>
                    <a href="#configuration" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-info">
                        En savoir plus
                        <i class="ki-filled ki-right text-xs"></i>
                    </a>
                </div>
            </div>

            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 mb-5">
                        <i class="ki-filled ki-graph-up text-2xl text-warning"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">3. Premiers Pas</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Découvrez les fonctionnalités essentielles pour démarrer efficacement.
                    </p>
                    <a href="#premiers-pas" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-warning">
                        En savoir plus
                        <i class="ki-filled ki-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Guides Section -->
    <div class="mb-10" id="guides">
        <h2 class="text-2xl font-bold text-mono mb-5">
            <i class="ki-filled ki-notepad text-primary me-2"></i>
            Guides Détaillés
        </h2>
        <div class="kt-card">
            <div class="kt-card-content p-0">
                <div class="kt-accordion" data-kt-accordion="true">
                    <!-- Guide 1 -->
                    <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700">
                        <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-user text-lg text-primary"></i>
                                <h3 class="text-base font-semibold">Gestion des Utilisateurs</h3>
                            </div>
                            <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                        </div>
                        <div class="kt-accordion-content hidden">
                            <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                <p class="mb-3">Apprenez à gérer efficacement les utilisateurs de votre plateforme :</p>
                                <ul class="list-disc list-inside space-y-2 ms-3">
                                    <li>Création et modification de comptes utilisateurs</li>
                                    <li>Attribution des rôles et permissions</li>
                                    <li>Gestion des profils et paramètres</li>
                                    <li>Suspension et réactivation de comptes</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Guide 2 -->
                    <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700">
                        <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-shop text-lg text-success"></i>
                                <h3 class="text-base font-semibold">Gestion des Kiosques</h3>
                            </div>
                            <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                        </div>
                        <div class="kt-accordion-content hidden">
                            <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                <p class="mb-3">Tout ce que vous devez savoir sur la gestion des kiosques :</p>
                                <ul class="list-disc list-inside space-y-2 ms-3">
                                    <li>Enregistrement de nouveaux kiosques</li>
                                    <li>Configuration des paramètres de localisation</li>
                                    <li>Suivi du statut et des performances</li>
                                    <li>Gestion des agents affectés</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Guide 3 -->
                    <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700">
                        <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-wallet text-lg text-info"></i>
                                <h3 class="text-base font-semibold">Opérations et Transactions</h3>
                            </div>
                            <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                        </div>
                        <div class="kt-accordion-content hidden">
                            <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                <p class="mb-3">Guide complet pour gérer vos opérations financières :</p>
                                <ul class="list-disc list-inside space-y-2 ms-3">
                                    <li>Enregistrement des dépôts et retraits</li>
                                    <li>Suivi des soldes en temps réel</li>
                                    <li>Génération de rapports de transactions</li>
                                    <li>Réconciliation des comptes</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Guide 4 -->
                    <div class="kt-accordion-item">
                        <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-chart-line text-lg text-warning"></i>
                                <h3 class="text-base font-semibold">Rapports et Analyses</h3>
                            </div>
                            <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                        </div>
                        <div class="kt-accordion-content hidden">
                            <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                <p class="mb-3">Exploitez pleinement vos données avec nos outils d'analyse :</p>
                                <ul class="list-disc list-inside space-y-2 ms-3">
                                    <li>Tableaux de bord personnalisables</li>
                                    <li>Exportation des données en divers formats</li>
                                    <li>Analyse des tendances et performances</li>
                                    <li>Alertes et notifications automatiques</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Section -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5">
            <i class="ki-filled ki-code text-primary me-2"></i>
            Documentation API
        </h2>
        <div class="kt-card">
            <div class="kt-card-content p-7.5">
                <div class="flex flex-col lg:flex-row gap-7.5">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-mono mb-3">Intégration API REST</h3>
                        <p class="text-sm text-muted-foreground mb-5">
                            Notre API RESTful vous permet d'intégrer facilement nos services dans vos applications. 
                            Accédez aux ressources via des endpoints sécurisés avec authentification OAuth 2.0.
                        </p>
                        <div class="flex flex-wrap gap-2 mb-5">
                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">GET</span>
                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-info">POST</span>
                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">PUT</span>
                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-danger">DELETE</span>
                        </div>
                        <a href="#" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-code me-1"></i>
                            Voir la documentation API
                        </a>
                    </div>
                    <div class="flex-1 bg-gray-950 dark:bg-gray-900 rounded-lg p-5">
                        <pre class="text-xs text-green-400 font-mono overflow-x-auto"><code>// Exemple d'appel API
fetch('https://api.example.com/v1/transactions', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Section -->
    <div>
        <h2 class="text-2xl font-bold text-mono mb-5">
            <i class="ki-filled ki-folder text-primary me-2"></i>
            Ressources Complémentaires
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <a href="#" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10">
                        <i class="ki-filled ki-file-down text-2xl text-primary"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">Téléchargements</h3>
                        <p class="text-sm text-muted-foreground">Manuels PDF, templates et ressources</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>

            <a href="#" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-success/10">
                        <i class="ki-filled ki-youtube text-2xl text-success"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">Tutoriels Vidéo</h3>
                        <p class="text-sm text-muted-foreground">Guides pas à pas en vidéo</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>

            <a href="#" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-info/10">
                        <i class="ki-filled ki-message-text text-2xl text-info"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">Forum Communauté</h3>
                        <p class="text-sm text-muted-foreground">Échangez avec d'autres utilisateurs</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>

            <a href="{{ route('public.faq') }}" class="kt-card hover:shadow-lg transition-shadow" data-ajax="false">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-warning/10">
                        <i class="ki-filled ki-question-2 text-2xl text-warning"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">FAQ</h3>
                        <p class="text-sm text-muted-foreground">Questions fréquemment posées</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

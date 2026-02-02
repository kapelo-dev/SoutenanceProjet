@extends('layouts.demo9.base')

@section('content')
<div class="kt-container-fixed py-10">
    <!-- Hero Section -->
    <div class="kt-card mb-7.5">
        <div class="kt-card-content p-10 lg:p-15 text-center">
            <i class="ki-filled ki-question-2 text-5xl text-warning mb-5"></i>
            <h1 class="text-3xl lg:text-4xl font-bold text-mono mb-5">Questions Fréquemment Posées</h1>
            <p class="text-lg text-muted-foreground max-w-3xl mx-auto">
                Trouvez rapidement des réponses aux questions les plus courantes. Si vous ne trouvez pas ce que vous cherchez, 
                n'hésitez pas à <a href="{{ route('public.support') }}" class="text-primary hover:underline" data-ajax="false">contacter notre support</a>.
            </p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-10">
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <label class="kt-input kt-input-lg">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" placeholder="Rechercher une question..." id="faq-search" />
                </label>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="mb-7.5">
        <div class="flex flex-wrap gap-2">
            <button class="kt-btn kt-btn-sm kt-btn-primary" data-category="all">
                <i class="ki-filled ki-category me-1"></i>
                Tout
            </button>
            <button class="kt-btn kt-btn-sm kt-btn-outline" data-category="compte">
                <i class="ki-filled ki-user me-1"></i>
                Compte
            </button>
            <button class="kt-btn kt-btn-sm kt-btn-outline" data-category="operations">
                <i class="ki-filled ki-wallet me-1"></i>
                Opérations
            </button>
            <button class="kt-btn kt-btn-sm kt-btn-outline" data-category="securite">
                <i class="ki-filled ki-shield-tick me-1"></i>
                Sécurité
            </button>
            <button class="kt-btn kt-btn-sm kt-btn-outline" data-category="technique">
                <i class="ki-filled ki-setting-2 me-1"></i>
                Technique
            </button>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="space-y-5">
        <!-- Section: Compte et Authentification -->
        <div>
            <h2 class="text-xl font-bold text-mono mb-5 flex items-center gap-2">
                <i class="ki-filled ki-user text-primary"></i>
                Compte et Authentification
            </h2>
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    <div class="kt-accordion" data-kt-accordion="true">
                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="compte">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Comment créer un compte ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Pour créer un compte sur notre plateforme :</p>
                                    <ol class="list-decimal list-inside space-y-2 ms-3">
                                        <li>Cliquez sur le bouton "S'inscrire" en haut à droite</li>
                                        <li>Remplissez le formulaire avec vos informations personnelles</li>
                                        <li>Vérifiez votre adresse email via le lien envoyé</li>
                                        <li>Complétez votre profil et commencez à utiliser la plateforme</li>
                                    </ol>
                                    <p class="mt-3 text-xs text-info">
                                        <i class="ki-filled ki-information-2 me-1"></i>
                                        L'inscription est gratuite et ne prend que quelques minutes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="compte">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">J'ai oublié mon mot de passe, que faire ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Pour réinitialiser votre mot de passe :</p>
                                    <ol class="list-decimal list-inside space-y-2 ms-3">
                                        <li>Cliquez sur "Mot de passe oublié ?" sur la page de connexion</li>
                                        <li>Entrez votre adresse email</li>
                                        <li>Consultez votre boîte email et cliquez sur le lien de réinitialisation</li>
                                        <li>Créez un nouveau mot de passe sécurisé</li>
                                    </ol>
                                    <p class="mt-3 text-xs text-warning">
                                        <i class="ki-filled ki-shield-tick me-1"></i>
                                        Le lien de réinitialisation expire après 60 minutes pour des raisons de sécurité.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item" data-category="compte">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Comment modifier mes informations personnelles ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Pour modifier vos informations :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Connectez-vous à votre compte</li>
                                        <li>Cliquez sur votre avatar en haut à droite</li>
                                        <li>Sélectionnez "Mon Profil"</li>
                                        <li>Modifiez les informations souhaitées et enregistrez</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Opérations et Transactions -->
        <div>
            <h2 class="text-xl font-bold text-mono mb-5 flex items-center gap-2">
                <i class="ki-filled ki-wallet text-success"></i>
                Opérations et Transactions
            </h2>
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    <div class="kt-accordion" data-kt-accordion="true">
                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="operations">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Comment effectuer un dépôt ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Pour effectuer un dépôt :</p>
                                    <ol class="list-decimal list-inside space-y-2 ms-3">
                                        <li>Accédez à la section "Opérations en Agence"</li>
                                        <li>Cliquez sur "Nouvelle opération"</li>
                                        <li>Sélectionnez le type d'opération "Dépôt"</li>
                                        <li>Remplissez les informations requises (montant, agent, opérateur)</li>
                                        <li>Validez l'opération</li>
                                    </ol>
                                    <p class="mt-3 text-xs text-success">
                                        <i class="ki-filled ki-check-circle me-1"></i>
                                        Le solde est mis à jour automatiquement après validation.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="operations">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Combien de temps prend une transaction ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Les délais de traitement varient selon le type de transaction :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li><strong>Dépôts :</strong> Instantané (quelques secondes)</li>
                                        <li><strong>Retraits :</strong> Instantané (quelques secondes)</li>
                                        <li><strong>Paiements :</strong> 1 à 5 minutes selon l'opérateur</li>
                                        <li><strong>Transferts internes :</strong> Instantané</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item" data-category="operations">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Comment consulter l'historique de mes transactions ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Pour consulter votre historique :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Rendez-vous dans la section "Transactions"</li>
                                        <li>Utilisez les filtres pour affiner votre recherche</li>
                                        <li>Exportez les données si nécessaire</li>
                                        <li>Cliquez sur une transaction pour voir les détails</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Sécurité -->
        <div>
            <h2 class="text-xl font-bold text-mono mb-5 flex items-center gap-2">
                <i class="ki-filled ki-shield-tick text-info"></i>
                Sécurité
            </h2>
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    <div class="kt-accordion" data-kt-accordion="true">
                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="securite">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Mes données sont-elles sécurisées ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Nous prenons la sécurité très au sérieux :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Chiffrement SSL/TLS pour toutes les communications</li>
                                        <li>Authentification à deux facteurs (2FA) disponible</li>
                                        <li>Sauvegarde quotidienne des données</li>
                                        <li>Surveillance 24/7 contre les menaces</li>
                                        <li>Conformité aux normes RGPD</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="securite">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Comment créer un mot de passe sécurisé ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Un mot de passe fort doit contenir :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Au moins 8 caractères (12+ recommandé)</li>
                                        <li>Des lettres majuscules et minuscules</li>
                                        <li>Des chiffres</li>
                                        <li>Des caractères spéciaux (@, #, $, etc.)</li>
                                        <li>Évitez les mots du dictionnaire ou informations personnelles</li>
                                    </ul>
                                    <p class="mt-3 text-xs text-danger">
                                        <i class="ki-filled ki-shield-cross me-1"></i>
                                        Ne partagez jamais votre mot de passe avec quiconque.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item" data-category="securite">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Que faire si je détecte une activité suspecte ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">En cas d'activité suspecte :</p>
                                    <ol class="list-decimal list-inside space-y-2 ms-3">
                                        <li>Changez immédiatement votre mot de passe</li>
                                        <li>Vérifiez vos transactions récentes</li>
                                        <li>Contactez notre support en urgence</li>
                                        <li>Activez l'authentification à deux facteurs si ce n'est pas déjà fait</li>
                                    </ol>
                                    <p class="mt-3">
                                        <a href="{{ route('public.support') }}" class="kt-btn kt-btn-xs kt-btn-danger">
                                            <i class="ki-filled ki-message-text me-1"></i>
                                            Contacter le support d'urgence
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Technique -->
        <div>
            <h2 class="text-xl font-bold text-mono mb-5 flex items-center gap-2">
                <i class="ki-filled ki-setting-2 text-warning"></i>
                Questions Techniques
            </h2>
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    <div class="kt-accordion" data-kt-accordion="true">
                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="technique">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Quels navigateurs sont supportés ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Notre plateforme est compatible avec :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Google Chrome (version 90+)</li>
                                        <li>Mozilla Firefox (version 88+)</li>
                                        <li>Safari (version 14+)</li>
                                        <li>Microsoft Edge (version 90+)</li>
                                    </ul>
                                    <p class="mt-3 text-xs text-info">
                                        <i class="ki-filled ki-information-2 me-1"></i>
                                        Pour une expérience optimale, nous recommandons de maintenir votre navigateur à jour.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item border-b border-gray-200 dark:border-gray-700" data-category="technique">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">La plateforme est-elle accessible sur mobile ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Oui ! Notre plateforme est entièrement responsive et s'adapte à tous les appareils :</p>
                                    <ul class="list-disc list-inside space-y-2 ms-3">
                                        <li>Smartphones (iOS et Android)</li>
                                        <li>Tablettes</li>
                                        <li>Ordinateurs portables et de bureau</li>
                                    </ul>
                                    <p class="mt-3 text-xs text-success">
                                        <i class="ki-filled ki-tablet me-1"></i>
                                        Une application mobile native est en cours de développement.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="kt-accordion-item" data-category="technique">
                            <div class="kt-accordion-toggle py-5 px-7.5" data-kt-accordion-toggle="collapse">
                                <h3 class="text-base font-semibold">Que faire en cas d'erreur technique ?</h3>
                                <i class="ki-filled ki-down text-sm text-muted-foreground"></i>
                            </div>
                            <div class="kt-accordion-content hidden">
                                <div class="px-7.5 pb-5 text-sm text-muted-foreground">
                                    <p class="mb-3">Si vous rencontrez une erreur :</p>
                                    <ol class="list-decimal list-inside space-y-2 ms-3">
                                        <li>Actualisez la page (F5 ou Ctrl+R)</li>
                                        <li>Videz le cache de votre navigateur</li>
                                        <li>Déconnectez-vous et reconnectez-vous</li>
                                        <li>Essayez avec un autre navigateur</li>
                                        <li>Si le problème persiste, contactez le support technique</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="mt-10">
        <div class="kt-card bg-primary/5 dark:bg-primary/10">
            <div class="kt-card-content p-7.5 text-center">
                <i class="ki-filled ki-message-question text-3xl text-primary mb-3"></i>
                <h3 class="text-lg font-semibold text-mono mb-3">Vous n'avez pas trouvé votre réponse ?</h3>
                <p class="text-sm text-muted-foreground mb-5">
                    Notre équipe de support est là pour vous aider. Contactez-nous et nous vous répondrons dans les plus brefs délais.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('public.support') }}" class="kt-btn kt-btn-primary" data-ajax="false">
                        <i class="ki-filled ki-message-text me-1"></i>
                        Contacter le Support
                    </a>
                    <a href="{{ route('public.documentation') }}" class="kt-btn kt-btn-outline" data-ajax="false">
                        <i class="ki-filled ki-book me-1"></i>
                        Voir la Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

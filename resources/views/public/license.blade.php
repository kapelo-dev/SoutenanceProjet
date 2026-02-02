@extends('layouts.demo9.base')

@section('content')
<div class="kt-container-fixed py-10">
    <!-- Hero Section -->
    <div class="kt-card mb-7.5">
        <div class="kt-card-content p-10 lg:p-15 text-center">
            <i class="ki-filled ki-badge text-5xl text-success mb-5"></i>
            <h1 class="text-3xl lg:text-4xl font-bold text-mono mb-5">Licence et Conditions d'Utilisation</h1>
            <p class="text-lg text-muted-foreground max-w-3xl mx-auto">
                Veuillez lire attentivement les termes et conditions de licence avant d'utiliser notre plateforme.
            </p>
            <div class="flex items-center justify-center gap-2 mt-5 text-sm text-muted-foreground">
                <i class="ki-filled ki-calendar"></i>
                <span>Dernière mise à jour: {{ date('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- License Types -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5">Types de Licence</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Free License -->
            <div class="kt-card">
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-14 rounded-full bg-info/10 mx-auto mb-5">
                        <i class="ki-filled ki-gift text-3xl text-info"></i>
                    </div>
                    <h3 class="text-xl font-bold text-mono text-center mb-3">Gratuite</h3>
                    <div class="text-center mb-5">
                        <span class="text-3xl font-bold text-mono">0 FCFA</span>
                        <span class="text-sm text-muted-foreground">/mois</span>
                    </div>
                    <ul class="space-y-3 mb-7.5">
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Accès aux fonctionnalités de base</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>1 utilisateur</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Support par email</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-cross text-muted-foreground mt-0.5"></i>
                            <span class="text-muted-foreground">Rapports avancés</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-cross text-muted-foreground mt-0.5"></i>
                            <span class="text-muted-foreground">API access</span>
                        </li>
                    </ul>
                    <button class="kt-btn kt-btn-outline kt-btn-info w-full">
                        Commencer gratuitement
                    </button>
                </div>
            </div>

            <!-- Professional License -->
            <div class="kt-card ring-2 ring-primary relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="kt-badge kt-badge-primary">Le plus populaire</span>
                </div>
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-14 rounded-full bg-primary/10 mx-auto mb-5">
                        <i class="ki-filled ki-rocket text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-mono text-center mb-3">Professionnelle</h3>
                    <div class="text-center mb-5">
                        <span class="text-3xl font-bold text-mono">25,000 FCFA</span>
                        <span class="text-sm text-muted-foreground">/mois</span>
                    </div>
                    <ul class="space-y-3 mb-7.5">
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Toutes les fonctionnalités de base</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Jusqu'à 10 utilisateurs</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Support prioritaire</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Rapports avancés</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Accès API</span>
                        </li>
                    </ul>
                    <button class="kt-btn kt-btn-primary w-full">
                        Démarrer l'essai gratuit
                    </button>
                </div>
            </div>

            <!-- Enterprise License -->
            <div class="kt-card">
                <div class="kt-card-content p-7.5">
                    <div class="flex items-center justify-center size-14 rounded-full bg-warning/10 mx-auto mb-5">
                        <i class="ki-filled ki-office-bag text-3xl text-warning"></i>
                    </div>
                    <h3 class="text-xl font-bold text-mono text-center mb-3">Enterprise</h3>
                    <div class="text-center mb-5">
                        <span class="text-xl font-bold text-mono">Sur devis</span>
                    </div>
                    <ul class="space-y-3 mb-7.5">
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Fonctionnalités illimitées</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Utilisateurs illimités</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Support 24/7 dédié</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>SLA garanti</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i class="ki-filled ki-check text-success mt-0.5"></i>
                            <span>Personnalisation complète</span>
                        </li>
                    </ul>
                    <a href="{{ route('public.support') }}" class="kt-btn kt-btn-outline kt-btn-warning w-full" data-ajax="false">
                        Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5">Termes et Conditions</h2>
        <div class="kt-card">
            <div class="kt-card-content p-7.5">
                <div class="prose prose-sm max-w-none dark:prose-invert">
                    <h3 class="text-lg font-semibold text-mono mb-3">1. Acceptation des Termes</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        En accédant et en utilisant cette plateforme, vous acceptez d'être lié par les présents termes et conditions. 
                        Si vous n'acceptez pas ces termes, veuillez ne pas utiliser notre service.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">2. Octroi de Licence</h3>
                    <p class="text-sm text-muted-foreground mb-3">
                        Sous réserve du respect des présentes conditions, nous vous accordons une licence limitée, non exclusive, 
                        non transférable et révocable pour utiliser notre plateforme conformément au type de licence que vous avez souscrit.
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm text-muted-foreground mb-5 ms-3">
                        <li>Vous ne pouvez pas copier, modifier ou distribuer le logiciel</li>
                        <li>Vous ne pouvez pas effectuer de rétro-ingénierie du code source</li>
                        <li>Vous devez respecter les limites d'utilisation de votre plan</li>
                        <li>La licence est personnelle et ne peut être transférée</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-mono mb-3">3. Propriété Intellectuelle</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Tous les droits, titres et intérêts relatifs à la plateforme, y compris tous les droits de propriété intellectuelle, 
                        restent notre propriété exclusive. Vous reconnaissez que vous n'acquérez aucun droit de propriété sur la plateforme 
                        en vertu de cette licence.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">4. Utilisation Acceptable</h3>
                    <p class="text-sm text-muted-foreground mb-3">Vous vous engagez à ne pas :</p>
                    <ul class="list-disc list-inside space-y-2 text-sm text-muted-foreground mb-5 ms-3">
                        <li>Utiliser la plateforme à des fins illégales ou non autorisées</li>
                        <li>Tenter d'accéder à des zones non autorisées du système</li>
                        <li>Interférer avec le fonctionnement normal de la plateforme</li>
                        <li>Transmettre des virus, malwares ou codes malveillants</li>
                        <li>Violer les droits d'autres utilisateurs</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-mono mb-3">5. Résiliation</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Nous nous réservons le droit de suspendre ou de résilier votre accès à la plateforme à tout moment, 
                        avec ou sans préavis, si nous estimons que vous avez violé les présentes conditions. En cas de résiliation, 
                        tous les droits qui vous sont accordés en vertu de cette licence cesseront immédiatement.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">6. Limitation de Responsabilité</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        La plateforme est fournie "telle quelle" sans garantie d'aucune sorte. Dans toute la mesure permise par la loi, 
                        nous déclinons toute responsabilité pour les dommages directs, indirects, accessoires ou consécutifs découlant 
                        de l'utilisation ou de l'impossibilité d'utiliser la plateforme.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">7. Confidentialité des Données</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Nous nous engageons à protéger la confidentialité de vos données. Les informations que vous nous fournissez 
                        sont traitées conformément à notre Politique de Confidentialité et aux réglementations en vigueur (RGPD).
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">8. Modifications des Termes</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Nous nous réservons le droit de modifier ces termes à tout moment. Les modifications entreront en vigueur 
                        dès leur publication sur la plateforme. Votre utilisation continue de la plateforme après la publication 
                        des modifications constitue votre acceptation des nouveaux termes.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">9. Loi Applicable</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Ces termes sont régis par les lois du Sénégal. Tout litige découlant de ces termes sera soumis 
                        à la juridiction exclusive des tribunaux de Dakar.
                    </p>

                    <h3 class="text-lg font-semibold text-mono mb-3">10. Contact</h3>
                    <p class="text-sm text-muted-foreground mb-3">
                        Pour toute question concernant cette licence ou ces termes, veuillez nous contacter :
                    </p>
                    <div class="kt-card bg-muted/20 p-5">
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <i class="ki-filled ki-sms text-primary"></i>
                                <span><strong>Email:</strong> legal@example.com</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="ki-filled ki-phone text-success"></i>
                                <span><strong>Téléphone:</strong> +221 12 345 67 89</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="ki-filled ki-geolocation text-info"></i>
                                <span><strong>Adresse:</strong> Dakar, Sénégal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-10">
        <!-- Open Source -->
        <div class="kt-card">
            <div class="kt-card-content p-7.5">
                <div class="flex items-center gap-3 mb-5">
                    <i class="ki-filled ki-code text-2xl text-success"></i>
                    <h3 class="text-lg font-semibold text-mono">Composants Open Source</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-5">
                    Notre plateforme utilise divers composants open source. Nous respectons et honorons les licences 
                    de ces projets. Vous pouvez consulter la liste complète des dépendances et leurs licences dans 
                    notre documentation.
                </p>
                <a href="{{ route('public.documentation') }}" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-success">
                    <i class="ki-filled ki-book me-1"></i>
                    Voir les attributions
                </a>
            </div>
        </div>

        <!-- Updates and Maintenance -->
        <div class="kt-card">
            <div class="kt-card-content p-7.5">
                <div class="flex items-center gap-3 mb-5">
                    <i class="ki-filled ki-update-file text-2xl text-info"></i>
                    <h3 class="text-lg font-semibold text-mono">Mises à Jour</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-5">
                    Les abonnés actifs bénéficient automatiquement de toutes les mises à jour de la plateforme, 
                    incluant les nouvelles fonctionnalités, les améliorations de sécurité et les corrections de bugs, 
                    sans frais supplémentaires.
                </p>
                <div class="flex items-center gap-2 text-xs text-success">
                    <i class="ki-filled ki-check-circle"></i>
                    <span>Mises à jour automatiques incluses</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="kt-card bg-gradient-to-r from-primary/10 to-success/10">
        <div class="kt-card-content p-10 text-center">
            <i class="ki-filled ki-shield-tick text-4xl text-primary mb-5"></i>
            <h3 class="text-2xl font-bold text-mono mb-3">Prêt à Commencer ?</h3>
            <p class="text-sm text-muted-foreground mb-7.5 max-w-2xl mx-auto">
                Choisissez le plan qui correspond à vos besoins et commencez à utiliser notre plateforme dès aujourd'hui. 
                Essai gratuit de 14 jours, aucune carte de crédit requise.
            </p>
            <div class="flex flex-wrap gap-3 justify-center">
                <button class="kt-btn kt-btn-primary kt-btn-lg">
                    <i class="ki-filled ki-rocket me-2"></i>
                    Commencer l'essai gratuit
                </button>
                <a href="{{ route('public.support') }}" class="kt-btn kt-btn-outline kt-btn-lg" data-ajax="false">
                    <i class="ki-filled ki-message-text me-2"></i>
                    Parler à un expert
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

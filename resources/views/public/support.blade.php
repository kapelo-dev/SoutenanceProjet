@extends('layouts.demo9.base')

@section('content')
<div class="kt-container-fixed py-10">
    <!-- Hero Section -->
    <div class="kt-card mb-7.5">
        <div class="kt-card-content p-10 lg:p-15 text-center">
            <i class="ki-filled ki-messages text-5xl text-info mb-5"></i>
            <h1 class="text-3xl lg:text-4xl font-bold text-mono mb-5">Centre de Support</h1>
            <p class="text-lg text-muted-foreground max-w-3xl mx-auto">
                Notre équipe d'assistance est à votre disposition pour répondre à toutes vos questions 
                et résoudre vos problèmes rapidement.
            </p>
        </div>
    </div>

    <!-- Contact Methods -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5 text-center">Comment nous contacter ?</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Email Support -->
            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5 text-center">
                    <div class="flex items-center justify-center size-16 rounded-full bg-primary/10 mx-auto mb-5">
                        <i class="ki-filled ki-sms text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">Email</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Envoyez-nous un email, nous vous répondrons sous 24h.
                    </p>
                    <a href="mailto:support@example.com" class="kt-btn kt-btn-sm kt-btn-primary">
                        support@example.com
                    </a>
                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                            <i class="ki-filled ki-time text-success"></i>
                            <span>Réponse en 24h</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phone Support -->
            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5 text-center">
                    <div class="flex items-center justify-center size-16 rounded-full bg-success/10 mx-auto mb-5">
                        <i class="ki-filled ki-phone text-3xl text-success"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">Téléphone</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Appelez-nous directement pour une assistance immédiate.
                    </p>
                    <a href="tel:+221123456789" class="kt-btn kt-btn-sm kt-btn-success">
                        +221 12 345 67 89
                    </a>
                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                            <i class="ki-filled ki-time text-success"></i>
                            <span>Lun-Ven: 8h-18h</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat -->
            <div class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-7.5 text-center">
                    <div class="flex items-center justify-center size-16 rounded-full bg-info/10 mx-auto mb-5">
                        <i class="ki-filled ki-message-text text-3xl text-info"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-mono mb-3">Chat en direct</h3>
                    <p class="text-sm text-muted-foreground mb-5">
                        Discutez avec notre équipe en temps réel.
                    </p>
                    <button class="kt-btn kt-btn-sm kt-btn-info" onclick="alert('Le chat sera disponible prochainement')">
                        Démarrer le chat
                    </button>
                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-2 text-xs">
                            <span class="size-2 bg-success rounded-full animate-pulse"></span>
                            <span class="text-success font-medium">En ligne maintenant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5 text-center">Envoyez-nous un message</h2>
        <div class="kt-card max-w-4xl mx-auto">
            <div class="kt-card-content p-7.5 lg:p-10">
                <form action="#" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-foreground">
                                Nom complet
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom" class="kt-input" placeholder="Votre nom complet" required />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-foreground">
                                Email
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="kt-input" placeholder="votre@email.com" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-foreground">
                                Téléphone
                            </label>
                            <input type="tel" name="telephone" class="kt-input" placeholder="+221 XX XXX XX XX" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-foreground">
                                Catégorie
                                <span class="text-danger">*</span>
                            </label>
                            <select name="categorie" class="kt-select" data-kt-select="true" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <option value="technique">Problème technique</option>
                                <option value="compte">Question sur mon compte</option>
                                <option value="transaction">Transaction / Paiement</option>
                                <option value="securite">Sécurité</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Niveau de priorité
                            <span class="text-danger">*</span>
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="priorite" value="basse" checked class="form-radio text-success focus:ring-success" />
                                <span class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-success hover:bg-success/5 transition-colors">
                                    <i class="ki-filled ki-arrow-down text-success"></i>
                                    <span class="text-sm font-medium">Basse</span>
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="priorite" value="moyenne" class="form-radio text-warning focus:ring-warning" />
                                <span class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-warning hover:bg-warning/5 transition-colors">
                                    <i class="ki-filled ki-minus text-warning"></i>
                                    <span class="text-sm font-medium">Moyenne</span>
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="priorite" value="haute" class="form-radio text-danger focus:ring-danger" />
                                <span class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-danger hover:bg-danger/5 transition-colors">
                                    <i class="ki-filled ki-arrow-up text-danger"></i>
                                    <span class="text-sm font-medium">Haute</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Objet
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="objet" class="kt-input" placeholder="Résumé de votre demande" required />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Message
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="message" class="kt-textarea" rows="6" placeholder="Décrivez votre problème ou question en détail..." required></textarea>
                        <span class="text-xs text-muted-foreground">
                            Soyez le plus précis possible pour nous aider à vous répondre rapidement.
                        </span>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Pièces jointes (optionnel)
                        </label>
                        <input type="file" name="fichiers[]" class="kt-input" multiple accept="image/*,.pdf,.doc,.docx" />
                        <span class="text-xs text-muted-foreground">
                            <i class="ki-filled ki-information-2 me-1"></i>
                            Formats acceptés: images, PDF, Word. Taille max: 5 Mo par fichier.
                        </span>
                    </div>

                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="copie_email" id="copie_email" class="kt-checkbox" checked />
                        <label for="copie_email" class="text-sm text-muted-foreground cursor-pointer">
                            M'envoyer une copie de ce message par email
                        </label>
                    </div>

                    <div class="pt-5 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-send me-2"></i>
                                Envoyer le message
                            </button>
                            <button type="reset" class="kt-btn kt-btn-outline">
                                <i class="ki-filled ki-arrows-circle me-2"></i>
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-mono mb-5 text-center">Ressources utiles</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <a href="{{ route('public.faq') }}" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-warning/10">
                        <i class="ki-filled ki-question-2 text-2xl text-warning"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">FAQ</h3>
                        <p class="text-sm text-muted-foreground">Consultez les questions fréquemment posées</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>

            <a href="{{ route('public.documentation') }}" class="kt-card hover:shadow-lg transition-shadow" data-ajax="false">
                <div class="kt-card-content p-5 flex items-center gap-5">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10">
                        <i class="ki-filled ki-book text-2xl text-primary"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-mono mb-1">Documentation</h3>
                        <p class="text-sm text-muted-foreground">Guides et tutoriels détaillés</p>
                    </div>
                    <i class="ki-filled ki-right text-muted-foreground"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Support Hours -->
    <div class="kt-card bg-gradient-to-r from-primary/10 to-info/10">
        <div class="kt-card-content p-7.5 lg:p-10">
            <div class="flex flex-col lg:flex-row items-center gap-7.5">
                <div class="flex items-center justify-center size-20 rounded-full bg-white dark:bg-gray-800 shadow-lg">
                    <i class="ki-filled ki-time text-4xl text-primary"></i>
                </div>
                <div class="flex-1 text-center lg:text-left">
                    <h3 class="text-xl font-bold text-mono mb-3">Heures d'ouverture du support</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            <i class="ki-filled ki-calendar text-success"></i>
                            <span><strong>Lundi - Vendredi:</strong> 8h00 - 18h00</span>
                        </div>
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            <i class="ki-filled ki-calendar text-warning"></i>
                            <span><strong>Samedi:</strong> 9h00 - 14h00</span>
                        </div>
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            <i class="ki-filled ki-calendar text-danger"></i>
                            <span><strong>Dimanche:</strong> Fermé</span>
                        </div>
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            <i class="ki-filled ki-geolocation text-info"></i>
                            <span><strong>Fuseau:</strong> GMT+0 (Dakar)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

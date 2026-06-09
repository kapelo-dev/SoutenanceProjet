/**
 * Système d'initialisation des pages après navigation AJAX
 * Chaque page peut définir ses propres gestionnaires globaux
 */

// Gestionnaire global pour les modals d'opérateurs
window.initOperateursPage = function() {
    console.log('Init: Opérateurs');
    const filterActifs = document.getElementById('filter_actifs');
    if (filterActifs && !filterActifs._initialized) {
        filterActifs._initialized = true;
        filterActifs.addEventListener('change', function() {
            const table = document.getElementById('operateurs_table');
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr[data-statut]');
            rows.forEach(row => {
                if (this.checked) {
                    row.style.display = row.getAttribute('data-statut') === 'actif' ? '' : 'none';
                } else {
                    row.style.display = '';
                }
            });
        });
    }
};

// Gestionnaire global pour charger le profil utilisateur dans un modal
window.loadUserProfile = function(userId) {
    console.log('Chargement profil utilisateur:', userId);
    
    const modal = document.getElementById('modal_profile');
    if (!modal) {
        console.error('Modal de profil utilisateur introuvable');
        return;
    }
    
    // Récupérer les éléments du modal
    const avatar = document.getElementById('modal_profile_avatar');
    const name = document.getElementById('modal_profile_name');
    const email = document.getElementById('modal_profile_email');
    const profils = document.getElementById('modal_profile_profils');
    const profilsContainer = document.getElementById('modal_profile_profils_container');
    const about = document.getElementById('modal_profile_about');
    
    // Réinitialiser le contenu
    if (avatar) avatar.src = '';
    if (name) name.textContent = 'Chargement...';
    if (email) email.textContent = '';
    if (profils) profils.textContent = '';
    if (profilsContainer) profilsContainer.style.display = 'none';
    if (about) about.innerHTML = '<div class="col-span-2 text-center py-8"><i class="ki-filled ki-loading animate-spin text-primary text-2xl"></i></div>';
    
    // Charger les données
    fetch(`/api/utilisateurs/${userId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur lors du chargement');
        return response.json();
    })
    .then(data => {
        if (!data.success || !data.utilisateur) throw new Error('Données invalides');
        
        const user = data.utilisateur;
        
        // Avatar
        if (avatar) {
            const avatarContainer = avatar.parentNode;
            const oldInitialDiv = avatarContainer.querySelector('.avatar-initial');
            if (oldInitialDiv) oldInitialDiv.remove();
            
            if (user.photo_profil_url) {
                avatar.src = user.photo_profil_url;
                avatar.style.display = 'block';
                avatar.alt = `${user.prenom} ${user.nom}`;
            } else {
                const initial = (user.prenom ? user.prenom.charAt(0) : user.nom.charAt(0)).toUpperCase();
                avatar.style.display = 'none';
                const initialDiv = document.createElement('div');
                initialDiv.className = 'flex items-center justify-center relative text-2xl text-green-500 w-[80px] h-[80px] ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full border-4 border-background shadow-lg avatar-initial';
                initialDiv.textContent = initial;
                avatarContainer.appendChild(initialDiv);
            }
        }
        
        // Nom et email
        if (name) name.textContent = `${user.prenom} ${user.nom}`;
        if (email) {
            email.textContent = user.email;
            email.href = `mailto:${user.email}`;
        }
        
        // Profils (dans l'en-tête)
        if (user.profils && user.profils.length > 0) {
            if (profils) profils.textContent = user.profils.map(p => p.libelle).join(', ');
            if (profilsContainer) profilsContainer.style.display = 'flex';
        } else {
            if (profilsContainer) profilsContainer.style.display = 'none';
        }
        
        // Statut badge in header
        const statutBadge = document.getElementById('modal_profile_statut_badge');
        if (statutBadge && user.statut) {
            const statutConfig = {
                'actif': { label: 'Actif', class: 'bg-green-500/10 text-green-600 dark:text-green-400', icon: 'ki-check-circle' },
                'inactif': { label: 'Inactif', class: 'bg-gray-500/10 text-gray-600 dark:text-gray-400', icon: 'ki-minus-circle' },
                'suspendu': { label: 'Suspendu', class: 'bg-red-500/10 text-red-600 dark:text-red-400', icon: 'ki-cross-circle' }
            };
            const cfg = statutConfig[user.statut] || { label: user.statut, class: 'bg-gray-500/10 text-gray-600', icon: 'ki-information-circle' };
            statutBadge.innerHTML = `<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ${cfg.class}"><i class="ki-filled ${cfg.icon} text-xs"></i>${cfg.label}</span>`;
        }

        // Section Informations - grid cards
        if (about) {
            let html = '';
            
            // Statut card
            if (user.statut) {
                const statutConfig = {
                    'actif': { label: 'Actif', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-950/30', icon: 'ki-check-circle' },
                    'inactif': { label: 'Inactif', color: 'text-gray-500', bg: 'bg-gray-50 dark:bg-gray-950/30', icon: 'ki-minus-circle' },
                    'suspendu': { label: 'Suspendu', color: 'text-red-500', bg: 'bg-red-50 dark:bg-red-950/30', icon: 'ki-cross-circle' }
                };
                const cfg = statutConfig[user.statut] || { label: user.statut, color: 'text-gray-500', bg: 'bg-gray-50', icon: 'ki-information-circle' };
                html += `<div class="flex items-center gap-3 rounded-lg border border-border p-3 ${cfg.bg}">
                    <div class="flex items-center justify-center size-10 rounded-full ${cfg.bg}"><i class="ki-filled ${cfg.icon} ${cfg.color} text-lg"></i></div>
                    <div><div class="text-xs text-muted-foreground">Statut</div><div class="text-sm font-semibold ${cfg.color}">${cfg.label}</div></div>
                </div>`;
            }

            // Téléphone card
            if (user.telephone) {
                html += `<div class="flex items-center gap-3 rounded-lg border border-border p-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/5"><i class="ki-filled ki-phone text-primary text-lg"></i></div>
                    <div><div class="text-xs text-muted-foreground">Téléphone</div><div class="text-sm font-semibold text-foreground">${user.telephone}</div></div>
                </div>`;
            }

            // Dernière connexion card
            if (user.dernier_connexion) {
                html += `<div class="flex items-center gap-3 rounded-lg border border-border p-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/5"><i class="ki-filled ki-time text-warning text-lg"></i></div>
                    <div><div class="text-xs text-muted-foreground">Dernière connexion</div><div class="text-sm font-semibold text-foreground">${user.dernier_connexion}</div></div>
                </div>`;
            }

            // Membre depuis card
            if (user.created_at) {
                html += `<div class="flex items-center gap-3 rounded-lg border border-border p-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-success/5"><i class="ki-filled ki-calendar text-success text-lg"></i></div>
                    <div><div class="text-xs text-muted-foreground">Membre depuis</div><div class="text-sm font-semibold text-foreground">${user.created_at}</div></div>
                </div>`;
            }

            // Profils card (full width)
            if (user.profils && user.profils.length > 0) {
                const badges = user.profils.map(p => 
                    `<span class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-1 text-xs font-medium"><i class="ki-filled ki-abstract-41"></i>${p.libelle}</span>`
                ).join(' ');
                html += `<div class="col-span-2 flex items-center gap-3 rounded-lg border border-border p-3">
                    <div class="flex items-center justify-center size-10 shrink-0 rounded-full bg-primary/5"><i class="ki-filled ki-profile-user text-primary text-lg"></i></div>
                    <div><div class="text-xs text-muted-foreground mb-1">Profils</div><div class="flex flex-wrap gap-1.5">${badges}</div></div>
                </div>`;
            }
            
            about.innerHTML = html || '<div class="col-span-2 text-center py-8"><i class="ki-filled ki-information-circle text-3xl text-muted-foreground mb-2"></i><div class="text-sm text-muted-foreground">Aucune information disponible</div></div>';
        }
        
        // Ouvrir le modal
        setTimeout(() => {
            try {
                // Essayer d'utiliser KTModal si disponible
                let modalInstance = typeof KTModal !== 'undefined' ? KTModal.getInstance(modal) : null;
                
                if (!modalInstance && typeof KTModal !== 'undefined') {
                    modalInstance = new KTModal(modal);
                }
                
                if (modalInstance && typeof modalInstance.show === 'function') {
                    modalInstance.show();
                } else {
                    // Fallback manuel
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                }
            } catch (error) {
                console.error('Erreur ouverture modal:', error);
                // Fallback simple
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.style.display = 'flex';
                modal.classList.add('show');
            }
        }, 100);
    })
    .catch(error => {
        console.error('Erreur chargement profil:', error);
        window.AppToast?.error('Une erreur est survenue lors du chargement des données');
        if (about) about.innerHTML = '<div class="col-span-2 text-center py-8 text-destructive"><i class="ki-filled ki-information-circle text-2xl mb-2"></i><div>Erreur de chargement</div></div>';
    });
};

// Gestionnaire global pour la page agents
window.initAgentsPage = function() {
    console.log('Init: Agents');
    // Les gestionnaires d'événements pour les agents seront ici
};

// Fonction globale pour ouvrir le modal de détails d'un agent (fallback)
// La vraie fonction est dans la vue /agents/liste_agents/index.blade.php
// Ce fallback garantit que les appels depuis d'autres pages ne causent pas d'erreur
if (typeof window.loadAgentDetails === 'undefined') {
    window.loadAgentDetails = function(agentId) {
        console.log('loadAgentDetails fallback - Agent ID:', agentId);
        // Si on n'est pas sur la page agents, rediriger
        if (!window.location.pathname.includes('/agents')) {
            window.location.href = `/agents/liste-agents`;
        } else {
            console.error('Modal d\'agent non disponible');
        }
    };
}

// Note: initSoldesPage est définie dans app.js et exposée globalement

// Gestionnaire global pour la page kiosques
window.initKiosquesPage = function() {
    console.log('Init: Kiosques');
    // Les gestionnaires d'événements pour les kiosques seront ici
};

// Gestionnaire global pour la page transactions — implémenté dans pages/transactions/index.blade.php

// Appeler toutes les fonctions init au chargement de la page ET après navigation AJAX
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, initialisation des pages');
    // Le système ajax-navigation.js appellera automatiquement toutes les fonctions window.init*
    // Mais on peut aussi les appeler explicitement ici pour le chargement initial
    if (typeof window.initOperateursPage === 'function') window.initOperateursPage();
    if (typeof window.initAgentsPage === 'function') window.initAgentsPage();
    if (typeof window.initKiosquesPage === 'function') window.initKiosquesPage();
    if (typeof window.initTransactionsPage === 'function') window.initTransactionsPage();
    if (typeof window.initOperationAgenceModal === 'function') window.initOperationAgenceModal();
    // initSoldesPage est appelée par initMetronicCore dans app.js au chargement initial
});

// Écouter l'événement ajax-content-loaded pour réinitialiser après navigation AJAX
document.addEventListener('ajax-content-loaded', function() {
    console.log('Contenu AJAX chargé, réinitialisation des pages');
    // ajax-navigation.js appellera déjà toutes les fonctions init*, mais on peut aussi les appeler ici en backup
});

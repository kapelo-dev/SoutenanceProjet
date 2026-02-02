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
    if (about) about.innerHTML = '<tr><td colspan="2" class="text-center py-4"><i class="ki-filled ki-loading animate-spin text-primary text-2xl"></i></td></tr>';
    
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
                initialDiv.className = 'flex items-center justify-center relative text-2xl text-green-500 w-[100px] h-[100px] ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full avatar-initial';
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
        
        // Section Informations avec badges et icônes
        if (about) {
            let html = '';
            
            if (user.telephone) {
                html += `<tr>
                    <td class="text-sm font-medium text-secondary-foreground pb-4 pe-3 align-top">
                        <div class="flex items-center gap-2">
                            <i class="ki-filled ki-phone text-primary text-base"></i>
                            <span>Téléphone:</span>
                        </div>
                    </td>
                    <td class="text-sm text-mono pb-4 font-semibold text-foreground">${user.telephone}</td>
                </tr>`;
            }
            
            if (user.statut) {
                const statutConfig = {
                    'actif': { label: 'Actif', class: 'kt-badge-success', icon: 'ki-check-circle' },
                    'inactif': { label: 'Inactif', class: 'kt-badge-secondary', icon: 'ki-minus-circle' },
                    'suspendu': { label: 'Suspendu', class: 'kt-badge-destructive', icon: 'ki-cross-circle' }
                };
                const config = statutConfig[user.statut] || { label: user.statut, class: 'kt-badge-secondary', icon: 'ki-information-circle' };
                html += `<tr>
                    <td class="text-sm font-medium text-secondary-foreground pb-4 pe-3 align-top">
                        <div class="flex items-center gap-2">
                            <i class="ki-filled ki-graph-up text-primary text-base"></i>
                            <span>Statut:</span>
                        </div>
                    </td>
                    <td class="text-sm pb-4">
                        <span class="kt-badge kt-badge-sm ${config.class} kt-badge-outline">
                            <i class="ki-filled ${config.icon} me-1"></i>
                            ${config.label}
                        </span>
                    </td>
                </tr>`;
            }
            
            if (user.dernier_connexion) {
                html += `<tr>
                    <td class="text-sm font-medium text-secondary-foreground pb-4 pe-3 align-top">
                        <div class="flex items-center gap-2">
                            <i class="ki-filled ki-time text-primary text-base"></i>
                            <span>Dernière connexion:</span>
                        </div>
                    </td>
                    <td class="text-sm text-mono pb-4">
                        <span class="text-warning font-medium">${user.dernier_connexion}</span>
                    </td>
                </tr>`;
            }
            
            if (user.created_at) {
                html += `<tr>
                    <td class="text-sm font-medium text-secondary-foreground pb-4 pe-3 align-top">
                        <div class="flex items-center gap-2">
                            <i class="ki-filled ki-calendar text-primary text-base"></i>
                            <span>Membre depuis:</span>
                        </div>
                    </td>
                    <td class="text-sm text-mono pb-4">
                        <span class="text-success font-medium">${user.created_at}</span>
                    </td>
                </tr>`;
            }
            
            if (user.profils && user.profils.length > 0) {
                const badges = user.profils.map(p => 
                    `<span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">
                        <i class="ki-filled ki-abstract-41 me-1"></i>
                        ${p.libelle}
                    </span>`
                ).join(' ');
                html += `<tr>
                    <td class="text-sm font-medium text-secondary-foreground pb-4 pe-3 align-top">
                        <div class="flex items-center gap-2">
                            <i class="ki-filled ki-profile-user text-primary text-base"></i>
                            <span>Profils:</span>
                        </div>
                    </td>
                    <td class="text-sm pb-4">
                        <div class="flex flex-wrap gap-2">${badges}</div>
                    </td>
                </tr>`;
            }
            
            about.innerHTML = html || '<tr><td colspan="2" class="text-sm text-secondary-foreground text-center py-8"><i class="ki-filled ki-information-circle text-3xl text-muted-foreground mb-2"></i><div>Aucune information disponible</div></td></tr>';
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
        alert('Une erreur est survenue lors du chargement des données');
        if (about) about.innerHTML = '<tr><td colspan="2" class="text-center py-4 text-destructive"><i class="ki-filled ki-information-circle text-2xl mb-2"></i><div>Erreur de chargement</div></td></tr>';
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

// Gestionnaire global pour la page transactions
window.initTransactionsPage = function() {
    console.log('Init: Transactions');
    // Les gestionnaires d'événements pour les transactions seront ici
};

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

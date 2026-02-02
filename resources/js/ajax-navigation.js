/**
 * Système de navigation AJAX global
 * Permet de charger les pages et données via AJAX pour améliorer les performances
 */

// Configuration
const AjaxNavigation = {
    // Conteneur principal où le contenu sera chargé
    contentContainer: null,
    
    // Indicateur de chargement
    loadingIndicator: null,
    
    // Initialisation
    init() {
        this.contentContainer = document.querySelector('main[role="content"]') || document.querySelector('#content');
        
        // Ne pas initialiser si on ne trouve pas le conteneur
        if (!this.contentContainer) {
            console.warn('Conteneur de contenu principal non trouvé. Navigation AJAX désactivée.');
            return;
        }
        
        this.setupNavigation();
        this.setupForms();
        this.setupPagination();
    },
    
    // Intercepter les clics sur les liens
    setupNavigation() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            
            // Ignorer les liens externes, les ancres, les liens avec data-ajax="false"
            if (!link || 
                link.hostname !== window.location.hostname ||
                link.hasAttribute('download') ||
                link.getAttribute('href').startsWith('#') ||
                link.getAttribute('data-ajax') === 'false' ||
                link.target === '_blank') {
                return;
            }
            
            // Vérifier si c'est un lien de navigation interne
            const href = link.getAttribute('href');
            if (href && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                e.preventDefault();
                this.loadPage(href);
            }
        });
    },
    
    // Charger une page via AJAX
    async loadPage(url) {
        try {
            this.showLoading();
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                this.handleJsonResponse(data, url);
            } else {
                const html = await response.text();
                this.handleHtmlResponse(html, url);
            }
        } catch (error) {
            console.error('Erreur lors du chargement de la page:', error);
            this.showError('Erreur lors du chargement de la page. Veuillez réessayer.');
        } finally {
            this.hideLoading();
        }
    },
    
    /**
     * Ferme tout modal ouvert et retire l'état "modal ouvert" du body.
     * À appeler après injection AJAX pour éviter que l'écran reste flou (backdrop/overflow).
     */
    closeModalsAndBackdrop() {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        // Thème Metronic: .kt-modal avec .open ET .kt-modal-backdrop (élément séparé avec blur)
        document.querySelectorAll('.kt-modal.open, .kt-modal.show, .kt-modal[style*="flex"]').forEach(modal => {
            modal.classList.remove('open', 'show');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        });
        // Supprimer le backdrop du thème (classe kt-modal-backdrop = flou/overlay)
        document.querySelectorAll('.kt-modal-backdrop').forEach(el => {
            if (el.parentNode) el.parentNode.removeChild(el);
        });
        document.querySelectorAll('body > .modal-backdrop, body > [id*="backdrop"]').forEach(el => {
            if (el.parentNode) el.parentNode.removeChild(el);
        });
    },

    // Gérer la réponse HTML
    handleHtmlResponse(html, url) {
        // Si la réponse est déjà du HTML pur (sans layout), il faut quand même exécuter les <script>
        // (sinon les fonctions onclick comme saveAllPermissions ne sont pas définies après navigation AJAX)
        if (html.trim().startsWith('<') && !html.includes('<!DOCTYPE')) {
            if (this.contentContainer) {
                // Utiliser un conteneur temporaire pour extraire les scripts
                const temp = document.createElement('div');
                temp.innerHTML = html;

                const scripts = Array.from(temp.querySelectorAll('script'));
                const scriptsToExecute = [];

                scripts.forEach(script => {
                    if (script.src) {
                        scriptsToExecute.push({
                            type: 'external',
                            src: script.src,
                            async: script.async,
                            defer: script.defer
                        });
                    } else {
                        scriptsToExecute.push({
                            type: 'inline',
                            content: script.textContent
                        });
                    }
                    script.remove();
                });

                // Injecter le HTML sans les scripts
                this.contentContainer.innerHTML = temp.innerHTML;
                window.history.pushState({}, '', url);
                this.closeModalsAndBackdrop();

                // Exécuter les scripts APRÈS l'injection du HTML pour que les onclick fonctionnent
                // Utiliser un petit délai pour s'assurer que le DOM est complètement mis à jour
                setTimeout(() => {
                    this.executeScripts(scriptsToExecute).then(() => {
                        this.reinitialize();
                    });
                }, 10);
                return;
            }
        }
        
        // Parser le HTML complet
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extraire SEULEMENT le contenu principal (pas le layout)
        const newContent = doc.querySelector('main[role="content"]') || 
                          doc.querySelector('#content') || 
                          doc.querySelector('main');
        
        if (newContent && this.contentContainer) {
            // Extraire les scripts avant de remplacer le contenu
            const scripts = Array.from(newContent.querySelectorAll('script'));
            const scriptsToExecute = [];
            
            scripts.forEach(script => {
                if (script.src) {
                    // Script externe - le charger
                    scriptsToExecute.push({
                        type: 'external',
                        src: script.src,
                        async: script.async,
                        defer: script.defer
                    });
                } else {
                    // Script inline - l'exécuter
                    scriptsToExecute.push({
                        type: 'inline',
                        content: script.textContent
                    });
                }
                // Retirer le script du contenu pour éviter la double exécution
                script.remove();
            });
            
            // Remplacer SEULEMENT le contenu du main, pas tout le body
            this.contentContainer.innerHTML = newContent.innerHTML;
            
            // Mettre à jour l'URL sans recharger la page
            window.history.pushState({}, '', url);
            this.closeModalsAndBackdrop();
            
            // Exécuter les scripts APRÈS l'injection du HTML pour que les onclick fonctionnent
            // Utiliser un petit délai pour s'assurer que le DOM est complètement mis à jour
            setTimeout(() => {
                this.executeScripts(scriptsToExecute).then(() => {
                    // Réinitialiser les scripts et composants après l'exécution des scripts
                    this.reinitialize();
                });
            }, 10);
        } else {
            // Si on ne trouve pas le conteneur, recharger la page normalement
            window.location.href = url;
        }
    },
    
    // Exécuter les scripts extraits
    async executeScripts(scripts) {
        for (const script of scripts) {
            if (script.type === 'external') {
                await this.loadScript(script.src, script.async, script.defer);
                } else {
                // Exécuter le script inline
                try {
                    // Créer un élément script temporaire et l'injecter dans le DOM
                    // IMPORTANT: Ne PAS wrapper dans une fonction anonyme pour que les fonctions
                    // déclarées avec 'function nom() {}' soient disponibles dans le scope global (window)
                    const scriptElement = document.createElement('script');
                    scriptElement.textContent = script.content;
                    document.head.appendChild(scriptElement);
                    
                    // Retirer après exécution pour éviter l'encombrement
                    setTimeout(() => {
                        if (scriptElement.parentNode) {
                            document.head.removeChild(scriptElement);
                        }
                    }, 0);
                } catch (e) {
                    console.error('Erreur lors de l\'exécution du script inline:', e);
                    // En cas d'échec, essayer avec eval dans un try-catch (fallback)
                    try {
                        // Exécuter directement dans le scope global (pas dans une fonction anonyme)
                        // eslint-disable-next-line no-eval
                        eval(script.content);
                    } catch (evalError) {
                        console.error('Erreur même avec eval:', evalError);
                    }
                }
            }
        }
    },
    
    // Charger un script externe
    loadScript(src, async = false, defer = false) {
        return new Promise((resolve, reject) => {
            // Vérifier si le script est déjà chargé
            const existingScript = document.querySelector(`script[src="${src}"]`);
            if (existingScript) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = src;
            script.async = async;
            script.defer = defer;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Erreur lors du chargement du script: ${src}`));
            document.head.appendChild(script);
        });
    },
    
    // Gérer la réponse JSON
    handleJsonResponse(data, url) {
        if (data.html) {
            // Si le JSON contient du HTML
            if (this.contentContainer) {
                this.contentContainer.innerHTML = data.html;
            }
            window.history.pushState({}, '', url);
            this.reinitialize();
        } else if (data.redirect) {
            // Redirection
            this.loadPage(data.redirect);
        } else if (data.success === false) {
            // Erreur
            this.showError(data.message || 'Une erreur est survenue');
        }
    },
    
    // Réinitialiser les scripts et composants après chargement AJAX
    reinitialize() {
        // Réinitialiser Alpine.js si nécessaire
        if (window.Alpine) {
            // Alpine scanne automatiquement le nouveau contenu
        }
        
        // Réinitialiser les composants Metronic (y compris les tabs)
        if (window.MetronicCore && typeof window.MetronicCore.initMetronicCore === 'function') {
            window.MetronicCore.initMetronicCore();
        } else if (window.MetronicCore) {
            // Rétrocompatibilité si initMetronicCore n'existe pas
            window.MetronicCore.initDrawers && window.MetronicCore.initDrawers();
            window.MetronicCore.initMenus && window.MetronicCore.initMenus();
            window.MetronicCore.initModals && window.MetronicCore.initModals();
            window.MetronicCore.initTabs && window.MetronicCore.initTabs();
        }
        
        // Réinitialiser les événements onclick pour les boutons
        this.reinitializeButtonEvents();
        
        // Réinitialiser les cartes Leaflet
        this.reinitializeMaps();
        
        // Appeler toutes les fonctions d'initialisation de pages (pattern window.init*)
        this.reinitializePageFunctions();
        
        // Déclencher un événement personnalisé
        document.dispatchEvent(new CustomEvent('ajax-content-loaded'));
    },
    
    // Réinitialiser les événements des boutons après chargement AJAX
    reinitializeButtonEvents() {
        // Les événements onclick inline devraient fonctionner automatiquement
        // Mais on s'assure que les fonctions sont disponibles dans le scope global
        
        // Réattacher les événements sur les formulaires avec onsubmit
        const forms = this.contentContainer.querySelectorAll('form[onsubmit]');
        forms.forEach(form => {
            // Les onsubmit inline devraient fonctionner, mais on vérifie
            if (!form._ajaxFormHandler) {
                form._ajaxFormHandler = true;
                // Le gestionnaire setupForms() devrait déjà gérer cela via délégation d'événements
            }
        });
        
        // S'assurer que les fonctions globales communes sont disponibles
        // Par exemple, toggleDropdown qui est déjà exposée globalement dans app.js
        // Les autres fonctions doivent être définies dans les scripts inline de chaque page
    },
    
    // Réinitialiser toutes les fonctions d'initialisation de pages
    reinitializePageFunctions() {
        // Chercher toutes les fonctions window.init* et les appeler
        const initFunctions = [];
        for (let key in window) {
            if (key.startsWith('init') && typeof window[key] === 'function') {
                initFunctions.push(key);
            }
        }
        
        console.log('Réinitialisation des fonctions de page:', initFunctions);
        
        initFunctions.forEach(funcName => {
            try {
                window[funcName]();
            } catch (e) {
                console.warn(`Erreur lors de l'appel de ${funcName}:`, e);
            }
        });
    },
    
    // Réinitialiser les cartes Leaflet
    reinitializeMaps() {
        // Attendre un peu pour que le DOM soit complètement mis à jour et que les scripts soient chargés
        setTimeout(() => {
            // Vérifier si Leaflet est chargé
            if (typeof L === 'undefined') {
                // Essayer de charger Leaflet si ce n'est pas déjà fait
                this.loadLeaflet().then(() => {
                    setTimeout(() => this.initAllMaps(), 100);
                }).catch(() => {
                    console.warn('Impossible de charger Leaflet');
                });
            } else {
                this.initAllMaps();
            }
        }, 200);
    },
    
    // Charger Leaflet si nécessaire
    async loadLeaflet() {
        return new Promise((resolve, reject) => {
            // Vérifier si Leaflet est déjà chargé
            if (typeof L !== 'undefined') {
                resolve();
                return;
            }
            
            // Vérifier si les scripts sont déjà en cours de chargement
            if (document.querySelector('script[src*="leaflet"]')) {
                // Attendre que le script se charge
                const checkInterval = setInterval(() => {
                    if (typeof L !== 'undefined') {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
                
                setTimeout(() => {
                    clearInterval(checkInterval);
                    if (typeof L === 'undefined') {
                        reject(new Error('Timeout lors du chargement de Leaflet'));
                    }
                }, 5000);
                return;
            }
            
            // Charger Leaflet CSS
            if (!document.querySelector('link[href*="leaflet.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }
            
            // Charger Leaflet JS
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Erreur lors du chargement de Leaflet'));
            document.head.appendChild(script);
        });
    },
    
    // Initialiser toutes les cartes trouvées dans le contenu
    initAllMaps() {
        // Carte des kiosques - utiliser la nouvelle instance de classe
        const kiosquesMapElement = document.getElementById('kiosques_map');
        if (kiosquesMapElement && window.kiosquesMapInstance) {
            // Détruire l'ancienne carte si elle existe
            window.kiosquesMapInstance.destroy();
            // Réinitialiser après un court délai
            setTimeout(() => {
                window.kiosquesMapInstance.init();
            }, 200);
        }
        
        // Carte du dashboard (performance du mois) - utiliser la nouvelle instance de classe
        const dashboardMapElement = document.getElementById('dashboard_month_map');
        if (dashboardMapElement && window.dashboardMonthMapInstance) {
            // Détruire l'ancienne carte si elle existe
            window.dashboardMonthMapInstance.destroy();
            // Réinitialiser après un court délai
            setTimeout(() => {
                window.dashboardMonthMapInstance.init();
            }, 200);
        }
        
        // Autres cartes peuvent être ajoutées ici
        if (document.getElementById('map') && typeof initMap === 'function') {
            initMap();
        }
    },
    
    /**
     * Récupère le token CSRF (meta ou champ _token du formulaire)
     * @param {HTMLFormElement} [form] - Formulaire optionnel pour lire input _token
     * @returns {string|null}
     */
    getCsrfToken(form) {
        if (form) {
            const input = form.querySelector('input[name="_token"]');
            if (input && input.value) return input.value;
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    },

    // Gérer les formulaires avec AJAX
    setupForms() {
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            
            // Ignorer les formulaires avec data-ajax="false"
            if (form.getAttribute('data-ajax') === 'false') {
                return;
            }
            
            // Vérifier si c'est un formulaire GET (recherche, filtres)
            if (form.method.toLowerCase() === 'get') {
                e.preventDefault();
                const formData = new FormData(form);
                const url = new URL(form.action || window.location.href);
                
                // Ajouter les paramètres du formulaire à l'URL
                for (const [key, value] of formData.entries()) {
                    url.searchParams.set(key, value);
                }
                
                this.loadPage(url.toString());
            } else if (form.method.toLowerCase() === 'post' && form.getAttribute('data-ajax') !== 'false') {
                // Gérer les formulaires POST avec AJAX
                e.preventDefault();
                await this.submitForm(form);
            }
        });
    },
    
    // Soumettre un formulaire via AJAX
    async submitForm(form) {
        try {
            this.closeModalsAndBackdrop();
            this.showLoading();
            
            const formData = new FormData(form);
            const url = form.action || window.location.href;
            const method = form.method || 'POST';
            
            // Ajouter le token CSRF si disponible (formulaire ou meta)
            const csrfToken = this.getCsrfToken(form);
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, application/json'
            };
            
            // Ajouter le token CSRF dans les headers aussi
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: headers
            });
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                this.handleJsonResponse(data, url);
            } else {
                const html = await response.text();
                this.handleHtmlResponse(html, url);
            }
        } catch (error) {
            console.error('Erreur lors de la soumission du formulaire:', error);
            this.showError('Erreur lors de la soumission du formulaire. Veuillez réessayer.');
        } finally {
            this.hideLoading();
        }
    },
    
    // Gérer la pagination AJAX
    setupPagination() {
        document.addEventListener('click', async (e) => {
            const paginationLink = e.target.closest('.pagination a, [data-pagination]');
            
            if (paginationLink) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                if (url) {
                    this.loadPage(url);
                }
            }
        });
    },
    
    // Afficher l'indicateur de chargement
    showLoading() {
        if (!this.loadingIndicator) {
            this.loadingIndicator = document.createElement('div');
            this.loadingIndicator.className = 'ajax-loading-overlay fixed top-4 right-4 z-50';
            this.loadingIndicator.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-3 flex items-center gap-2.5 border border-gray-200 dark:border-gray-700">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-primary"></div>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Chargement...</span>
                </div>
            `;
            document.body.appendChild(this.loadingIndicator);
        }
        this.loadingIndicator.classList.remove('hidden');
    },
    
    // Masquer l'indicateur de chargement
    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.classList.add('hidden');
        }
    },
    
    // Afficher une erreur
    showError(message) {
        // Créer une notification d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
};

// Initialiser au chargement du DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AjaxNavigation.init());
} else {
    AjaxNavigation.init();
}

// Gérer le bouton retour du navigateur
window.addEventListener('popstate', (e) => {
    AjaxNavigation.loadPage(window.location.href);
});

// Exporter pour utilisation globale
window.AjaxNavigation = AjaxNavigation;

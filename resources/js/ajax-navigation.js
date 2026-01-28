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
    
    // Gérer la réponse HTML
    handleHtmlResponse(html, url) {
        // Si la réponse est déjà du HTML pur (sans layout), l'utiliser directement
        if (html.trim().startsWith('<') && !html.includes('<!DOCTYPE')) {
            // C'est probablement juste le contenu du main
            if (this.contentContainer) {
                this.contentContainer.innerHTML = html;
                window.history.pushState({}, '', url);
                this.reinitialize();
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
            
            // Exécuter les scripts
            this.executeScripts(scriptsToExecute).then(() => {
                // Réinitialiser les scripts et composants après l'exécution des scripts
                this.reinitialize();
            });
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
                    // Utiliser Function pour exécuter dans le contexte global
                    new Function(script.content)();
                } catch (e) {
                    console.error('Erreur lors de l\'exécution du script inline:', e);
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
        
        // Réinitialiser les cartes Leaflet
        this.reinitializeMaps();
        
        // Déclencher un événement personnalisé
        document.dispatchEvent(new CustomEvent('ajax-content-loaded'));
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
            this.showLoading();
            
            const formData = new FormData(form);
            const url = form.action || window.location.href;
            const method = form.method || 'POST';
            
            // Ajouter le token CSRF si disponible
            const csrfToken = this.getCsrfToken();
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

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
        document.querySelectorAll('.kt-modal.open, .kt-modal.show, .kt-modal[style*="flex"]').forEach(modal => {
            modal.classList.remove('open', 'show');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            if (typeof KTModal !== 'undefined') {
                const inst = KTModal.getInstance(modal);
                if (inst) try { inst.hide(); } catch (e) {}
            }
        });
        document.querySelectorAll('.kt-modal-backdrop, [data-kt-modal-backdrop="true"]').forEach(el => {
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
                // Extraire les scripts depuis le HTML brut pour éviter la troncature au premier
                // </script> (le parseur ferme la balise même dans une chaîne JS)
                const { scriptsToExecute, htmlWithoutScripts } = this.extractScriptsFromRawHtml(html);

                // Injecter le HTML sans les scripts (ou avec scripts vides pour garder l'ordre)
                this.contentContainer.innerHTML = htmlWithoutScripts;
                window.history.pushState({}, '', url);
                this.closeModalsAndBackdrop();

                setTimeout(() => {
                    this.executeScripts(scriptsToExecute).then(() => {
                        this.reinitialize();
                    });
                }, 10);
                return;
            }
        }
        
        // Extraire le main depuis le HTML brut (évite troncature des scripts au premier </script>)
        const mainMatch = html.match(/<main\b[^>]*>([\s\S]*?)<\/main>/i);
        const mainContent = mainMatch ? mainMatch[1] : null;

        if (mainContent && this.contentContainer) {
            const { scriptsToExecute, htmlWithoutScripts } = this.extractScriptsFromRawHtml(mainContent);

            this.contentContainer.innerHTML = htmlWithoutScripts;
            window.history.pushState({}, '', url);
            this.closeModalsAndBackdrop();

            setTimeout(() => {
                this.executeScripts(scriptsToExecute).then(() => {
                    this.reinitialize();
                });
            }, 10);
        } else if (this.contentContainer) {
            // Fallback : parser avec DOMParser
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('main[role="content"]') || doc.querySelector('#content') || doc.querySelector('main');
            if (newContent) {
                const scripts = Array.from(newContent.querySelectorAll('script'));
                const scriptsToExecute = [];
                scripts.forEach(script => {
                    if (script.src) {
                        scriptsToExecute.push({ type: 'external', src: script.src, async: script.async, defer: script.defer });
                    } else {
                        scriptsToExecute.push({ type: 'inline', content: script.textContent });
                    }
                    script.remove();
                });
                this.contentContainer.innerHTML = newContent.innerHTML;
                window.history.pushState({}, '', url);
                this.closeModalsAndBackdrop();
                setTimeout(() => {
                    this.executeScripts(scriptsToExecute).then(() => this.reinitialize());
                }, 10);
            } else {
                window.location.href = url;
            }
        } else {
            window.location.href = url;
        }
    },
    
    /**
     * Extraire les scripts depuis le HTML brut sans tronquer au premier </script>
     * (le parseur HTML ferme la balise même dans une chaîne JS).
     * @param {string} html
     * @returns {{ scriptsToExecute: Array<{type: string, content?: string, src?: string}>, htmlWithoutScripts: string }}
     */
    extractScriptsFromRawHtml(html) {
        const scriptsToExecute = [];
        let htmlWithoutScripts = '';
        let i = 0;
        const len = html.length;

        while (i < len) {
            const scriptStart = html.indexOf('<script', i);
            if (scriptStart === -1) {
                htmlWithoutScripts += html.slice(i);
                break;
            }
            htmlWithoutScripts += html.slice(i, scriptStart);
            const tagEnd = html.indexOf('>', scriptStart);
            if (tagEnd === -1) {
                htmlWithoutScripts += html.slice(scriptStart);
                break;
            }
            const openTag = html.slice(scriptStart, tagEnd + 1);
            const hasSrc = /src\s*=/i.test(openTag);
            i = tagEnd + 1;

            if (hasSrc) {
                const srcMatch = openTag.match(/src\s*=\s*["']([^"']+)["']/i);
                if (srcMatch && srcMatch[1]) {
                    scriptsToExecute.push({
                        type: 'external',
                        src: srcMatch[1],
                        async: /async\b/i.test(openTag),
                        defer: /defer\b/i.test(openTag)
                    });
                }
            }

            const contentStart = i;
            let inString = null;
            let escape = false;
            let blockComment = false;
            let lineComment = false;
            let endScript = -1;

            while (i < len) {
                const c = html[i];
                if (lineComment) {
                    if (c === '\n' || c === '\r') lineComment = false;
                    i++;
                    continue;
                }
                if (blockComment) {
                    if (c === '*' && html[i + 1] === '/') {
                        blockComment = false;
                        i += 2;
                    } else i++;
                    continue;
                }
                if (escape) {
                    escape = false;
                    i++;
                    continue;
                }
                if (inString) {
                    if (c === '\\' && (inString === '"' || inString === "'" || inString === '`')) {
                        escape = true;
                        i++;
                        continue;
                    }
                    if (c === inString) {
                        inString = null;
                        i++;
                        continue;
                    }
                    i++;
                    continue;
                }
                if (c === '/' && html[i + 1] === '/') {
                    lineComment = true;
                    i += 2;
                    continue;
                }
                if (c === '/' && html[i + 1] === '*') {
                    blockComment = true;
                    i += 2;
                    continue;
                }
                if (c === '"' || c === "'" || c === '`') {
                    inString = c;
                    i++;
                    continue;
                }
                if (html.slice(i, i + 9).toLowerCase() === '</script>') {
                    endScript = i;
                    i += 9;
                    break;
                }
                i++;
            }

            if (endScript === -1) {
                htmlWithoutScripts += html.slice(scriptStart);
                break;
            }

            if (!hasSrc) {
                const content = html.slice(contentStart, endScript);
                scriptsToExecute.push({ type: 'inline', content });
            }
            htmlWithoutScripts += '<script></script>';
        }

        return { scriptsToExecute, htmlWithoutScripts };
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
        if (window.MetronicCore && typeof window.MetronicCore.initMetronicCore === 'function') {
            window.MetronicCore.initMetronicCore();
        } else if (window.MetronicCore) {
            window.MetronicCore.initDrawers && window.MetronicCore.initDrawers();
            window.MetronicCore.initMenus && window.MetronicCore.initMenus();
            window.MetronicCore.initTabs && window.MetronicCore.initTabs();
        }

        this.reinitializeButtonEvents();
        this.reinitializeMaps();

        // 1. Init pages (désactive datatable si vide, fix colspan…)
        this.reinitializePageFunctions();
        document.dispatchEvent(new CustomEvent('ajax-content-loaded'));

        // 2. Datatables après init page
        this.reinitializeDatatables();

        // 3. Modals après datatables
        this.reinitializeModals();
    },

    /** Grille vide : colspan + désactiver KTDatatable si aucune ligne de données */
    setupEmptyDatatable(tableId, colspan, isEmpty) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const applyFix = () => {
            const emptyRow = table.querySelector('tbody tr.empty-row');
            if (!emptyRow) return;
            const td = emptyRow.querySelector('td');
            if (td) {
                td.setAttribute('colspan', String(colspan));
                td.style.width = '100%';
                td.style.border = 'none';
            }
            emptyRow.style.display = 'table-row';
        };

        applyFix();

        if (!table._emptyRowObserver) {
            table._emptyRowObserver = new MutationObserver(applyFix);
            table._emptyRowObserver.observe(table, { childList: true, subtree: true });
        }

        if (isEmpty) {
            const wrapper = table.closest('[data-kt-datatable="true"]');
            if (wrapper) {
                wrapper.removeAttribute('data-kt-datatable');
                wrapper.setAttribute('data-kt-datatable-skip', 'true');
            }
        }
    },

    isEmptyDatatableTable(table) {
        if (!table) return false;
        return !!table.querySelector('tbody tr.empty-row')
            && !table.querySelector('tbody tr:not(.empty-row)');
    },
    
    // Réinitialiser les datatables KTUI (pagination, select size, etc.) après chargement AJAX
    reinitializeDatatables() {
        if (!this.contentContainer) return;
        setTimeout(() => {
            const KTDatatable = window.KTDataTable || window.KTDatatable;
            if (typeof KTDatatable === 'undefined') return;
            try {
                const containers = this.contentContainer.querySelectorAll('[data-kt-datatable="true"]');
                containers.forEach(el => {
                    if (el.getAttribute('data-kt-datatable-skip') === 'true') return;
                    const table = el.querySelector('[data-kt-datatable-table="true"]') || el.querySelector('table');
                    if (this.isEmptyDatatableTable(table)) {
                        el.removeAttribute('data-kt-datatable');
                        el.setAttribute('data-kt-datatable-skip', 'true');
                    }
                });

                if (typeof KTDatatable.createInstances === 'function') {
                    KTDatatable.createInstances();
                } else {
                    this.contentContainer.querySelectorAll('[data-kt-datatable="true"]').forEach(el => {
                        if (el.getAttribute('data-kt-datatable-initialized') === 'true') return;
                        if (el.getAttribute('data-kt-datatable-skip') === 'true') return;
                        if (typeof KTDatatable.getOrCreateInstance === 'function') {
                            KTDatatable.getOrCreateInstance(el);
                        }
                    });
                }
            } catch (e) {
                console.debug('[AjaxNav] Datatable init:', e);
            }
        }, 100);
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
    
    // Réinitialiser les modals Metronic après navigation AJAX
    reinitializeModals() {
        setTimeout(() => {
            const root = this.contentContainer || document;

            if (typeof window.portalModalsToBody === 'function') {
                window.portalModalsToBody(root);
            } else if (window.MetronicCore?.portalModalsToBody) {
                window.MetronicCore.portalModalsToBody(root);
            }

            if (typeof KTModal !== 'undefined') {
                root.querySelectorAll('[data-kt-modal="true"]').forEach(modalEl => {
                    const existingInstance = KTModal.getInstance(modalEl);
                    if (existingInstance) {
                        try { existingInstance.destroy(); } catch (e) {}
                    }
                    delete modalEl._ktModalInstance;
                    try {
                        modalEl._ktModalInstance = new KTModal(modalEl);
                    } catch (e) {
                        console.warn('KTModal reinit error:', e);
                    }
                });
            } else if (window.MetronicCore?.initModals) {
                window.MetronicCore.initModals();
            }

            setTimeout(() => {
                if (typeof window.initOperationAgenceModal === 'function'
                    && document.getElementById('modal_nouvelle_operation')) {
                    window.initOperationAgenceModal();
                }
            }, 50);
        }, 150);
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
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/26370817-2ad4-48a9-8621-53fe8856d785',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ajax-navigation.js:initAllMaps',message:'dashboard map check',data:{hasEl:!!dashboardMapElement,hasInstance:!!window.dashboardMonthMapInstance,hasL:typeof L!=='undefined'},timestamp:Date.now(),sessionId:'debug-session',hypothesisId:'H2'})}).catch(()=>{});
        // #endregion
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

/**
 * Système de gestion dynamique des menus selon les permissions utilisateur
 * Inspiré du script PHP checkMenuVisibility
 */

let userPermissions = {
    routes: [],
    profils: [],
    permissions: {}
};

/**
 * Activer/désactiver le mode "loading" (anti flash) sur le sidebar
 */
function setSidebarLoading(isLoading) {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (!sidebarMenu) return;
    if (isLoading) {
        sidebarMenu.classList.add('kt-permissions-loading');
    } else {
        sidebarMenu.classList.remove('kt-permissions-loading');
    }
}

/**
 * Charger les permissions de l'utilisateur connecté
 */
async function loadUserPermissions() {
    try {
        // Anti flash: cacher tant que les permissions ne sont pas appliquées
        setSidebarLoading(true);

        const response = await fetch('/api/my-permissions', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            console.warn('Impossible de charger les permissions utilisateur');
            // En cas d'échec, on ré-affiche (sinon sidebar vide)
            setSidebarLoading(false);
            return;
        }

        const data = await response.json();
        
        if (data.success) {
            userPermissions = {
                routes: data.routes || [],
                profils: data.profils || [],
                permissions: data.permissions || {}
            };
            
            // Debug: afficher les routes autorisées (à retirer en production)
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('Routes autorisées:', userPermissions.routes);
            }
            
            // Appliquer les permissions aux menus
            applyMenuPermissions();
        }
        setSidebarLoading(false);
    } catch (error) {
        console.error('Erreur lors du chargement des permissions:', error);
        setSidebarLoading(false);
    }
}

/**
 * Vérifier si une route est autorisée pour l'utilisateur
 * @param {string} href - L'URL ou route à vérifier
 * @returns {boolean} - true si autorisé, false sinon
 */
function checkMenuVisibility(href) {
    // Si pas de permissions chargées, masquer par sécurité
    if (!userPermissions.routes || userPermissions.routes.length === 0) {
        console.warn('Aucune permission chargée, masquage du menu:', href);
        return false;
    }

    // Si c'est un menu déroulant (#), on vérifie si l'utilisateur a accès à au moins un sous-menu
    if (href === '#' || !href || href.trim() === '') {
        return true; // Les menus parents sont toujours visibles, leurs enfants seront filtrés
    }

    // Normaliser le path (sans query/hash)
    const normalizedHref = normalizeUrl(href);
    const cleanHref = normalizedHref.startsWith('/') ? normalizedHref : '/' + normalizedHref;
    // Extraire la query si présente (pour liens comme /gestion-entreprise?onglet=salaires)
    const hrefQuery = (href && href.indexOf('?') >= 0) ? href.split('?')[1].split('#')[0] : '';
    const fullHref = hrefQuery ? (cleanHref + '?' + hrefQuery) : cleanHref;

    // Vérifier si la route est dans la liste des routes autorisées
    const isAuthorized = userPermissions.routes.some(route => {
        if (!route) return false;
        
        const normalizedRoute = normalizeUrl(route);
        const cleanRoute = normalizedRoute.startsWith('/') ? normalizedRoute : '/' + normalizedRoute;
        const routeQuery = (route && route.indexOf('?') >= 0) ? route.split('?')[1].split('#')[0] : '';
        const fullRoute = routeQuery ? (cleanRoute + '?' + routeQuery) : cleanRoute;
        
        // Si le lien a une query, exiger une correspondance path+query (chaque sous-menu = permission dédiée)
        if (hrefQuery) {
            if (fullHref === fullRoute) return true;
            return false;
        }
        
        // Correspondance exacte path seul (cas le plus courant)
        if (cleanHref === cleanRoute) {
            return true;
        }
        
        // Correspondance partielle (sous-pages)
        if (cleanRoute !== '/' && cleanRoute.length > 1 && cleanHref.startsWith(cleanRoute + '/')) {
            return true;
        }
        
        return false;
    });

    // Debug pour les routes problématiques
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        if (cleanHref === '/utilisateurs' || cleanHref === '/rapports') {
            console.log(`checkMenuVisibility(${cleanHref}):`, {
                isAuthorized,
                routes: userPermissions.routes,
                normalizedHref: cleanHref
            });
        }
    }

    return isAuthorized;
}

/**
 * Normaliser une URL pour la comparaison
 * @param {string} url - L'URL à normaliser
 * @returns {string} - L'URL normalisée (commence toujours par /)
 */
function normalizeUrl(url) {
    if (!url) return '';
    
    try {
        let pathname = '';
        
        // Si c'est une URL complète, extraire le pathname
        if (url.startsWith('http://') || url.startsWith('https://')) {
            const urlObj = new URL(url);
            pathname = urlObj.pathname;
        } else {
            // Si c'est une URL relative, enlever les paramètres de requête et le hash
            pathname = url.split('?')[0].split('#')[0];
        }
        
        // S'assurer que le pathname commence par /
        if (!pathname.startsWith('/')) {
            pathname = '/' + pathname;
        }
        
        // S'assurer qu'il n'y a pas de slash final (sauf pour la racine)
        if (pathname.length > 1 && pathname.endsWith('/')) {
            pathname = pathname.slice(0, -1);
        }
        
        return pathname;
    } catch (e) {
        // Si l'URL est invalide, essayer de nettoyer manuellement
        let path = url.split('?')[0].split('#')[0];
        if (!path.startsWith('/')) {
            path = '/' + path;
        }
        if (path.length > 1 && path.endsWith('/')) {
            path = path.slice(0, -1);
        }
        return path;
    }
}

/**
 * Appliquer les permissions aux menus du sidebar
 */
function applyMenuPermissions() {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (!sidebarMenu) {
        console.warn('Menu sidebar introuvable');
        return;
    }

    // Récupérer tous les liens du menu (pas seulement .kt-menu-link, car certains items utilisent <a class="kt-menu-item">)
    const menuLinks = sidebarMenu.querySelectorAll('a[href]');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;

        const isAuthorized = checkMenuVisibility(href);
        // Le menu item peut être un <div class="kt-menu-item"> ou directement <a class="kt-menu-item">
        const menuItem = link.closest('.kt-menu-item');
        
        if (!menuItem) {
            // Si pas de menu item parent, masquer directement le lien
            if (!isAuthorized) {
                link.style.display = 'none';
                link.setAttribute('data-permission-hidden', 'true');
            }
            return;
        }
        
        // Debug: afficher les décisions de permission (à retirer en production)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const normalizedHref = normalizeUrl(href);
            const cleanHref = normalizedHref.startsWith('/') ? normalizedHref : '/' + normalizedHref;
            if (cleanHref === '/utilisateurs' || cleanHref === '/rapports') {
                console.log(`Menu ${cleanHref}: autorisé=${isAuthorized}, routes disponibles:`, userPermissions.routes);
            }
        }
        
        if (!isAuthorized) {
            // Masquer le menu item avec !important pour s'assurer qu'il est bien caché
            menuItem.style.display = 'none';
            menuItem.style.visibility = 'hidden';
            menuItem.setAttribute('data-permission-hidden', 'true');
            
            // Masquer aussi le lien lui-même au cas où
            link.style.display = 'none';
            link.setAttribute('data-permission-hidden', 'true');
        } else {
            // Afficher le menu item
            menuItem.style.display = '';
            menuItem.style.visibility = '';
            menuItem.removeAttribute('data-permission-hidden');
            
            // Afficher aussi le lien
            link.style.display = '';
            link.removeAttribute('data-permission-hidden');
        }
    });

    // Vérifier les menus parents (accordions)
    // Si tous les enfants sont masqués, masquer aussi le parent
    const accordionItems = sidebarMenu.querySelectorAll('.kt-menu-item[data-kt-menu-item-toggle="accordion"]');
    accordionItems.forEach(accordion => {
        const children = accordion.querySelectorAll('.kt-menu-accordion .kt-menu-item');
        const visibleChildren = Array.from(children).filter(child => {
            return child.style.display !== 'none' && !child.hasAttribute('data-permission-hidden');
        });
        
        if (children.length > 0 && visibleChildren.length === 0) {
            // Tous les enfants sont masqués, masquer le parent aussi
            accordion.style.display = 'none';
            accordion.setAttribute('data-permission-hidden', 'true');
        } else {
            accordion.style.display = '';
            accordion.removeAttribute('data-permission-hidden');
        }
    });

    // Masquer les headings de section si tous les menus suivants sont masqués
    const headings = sidebarMenu.querySelectorAll('.kt-menu-heading');
    headings.forEach(heading => {
        const menuItem = heading.closest('.kt-menu-item');
        if (!menuItem) return;
        
        let nextSibling = menuItem.nextElementSibling;
        let hasVisibleSibling = false;
        
        while (nextSibling) {
            if (nextSibling.classList.contains('kt-menu-item')) {
                if (nextSibling.style.display !== 'none' && !nextSibling.hasAttribute('data-permission-hidden')) {
                    hasVisibleSibling = true;
                    break;
                }
            }
            nextSibling = nextSibling.nextElementSibling;
        }
        
        if (!hasVisibleSibling) {
            menuItem.style.display = 'none';
            menuItem.setAttribute('data-permission-hidden', 'true');
        }
    });
}

/**
 * Initialiser le système de permissions de menu
 */
function initMenuPermissions() {
    // Charger les permissions au chargement de la page
    loadUserPermissions();
    
    // Réappliquer les permissions après navigation AJAX
    document.addEventListener('ajax-content-loaded', function() {
        setTimeout(() => {
            loadUserPermissions();
        }, 100);
    });
}

// Exporter les fonctions pour utilisation globale
window.MenuPermissions = {
    loadUserPermissions,
    checkMenuVisibility,
    applyMenuPermissions,
    initMenuPermissions,
    userPermissions,
    setSidebarLoading
};

// Initialiser automatiquement si le DOM est déjà chargé
// Utiliser un délai pour s'assurer que le sidebar est complètement rendu
function ensureMenuPermissionsInit() {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (sidebarMenu) {
        initMenuPermissions();
    } else {
        // Si le sidebar n'est pas encore là, réessayer après un court délai
        setTimeout(ensureMenuPermissionsInit, 100);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Attendre un peu pour que le sidebar soit complètement rendu
        setTimeout(ensureMenuPermissionsInit, 200);
    });
} else {
    // Le DOM est déjà chargé, mais on attend quand même un peu pour le sidebar
    setTimeout(ensureMenuPermissionsInit, 200);
}

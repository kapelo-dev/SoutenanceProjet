import './bootstrap';
import Alpine from 'alpinejs';
import './ajax-navigation';
// Importer les modules de cartes
import './maps/dashboard-month-map';
import './maps/kiosques-map';
// Importer le système de permissions de menu
import './menu-permissions';
// Importer le système d'initialisation des pages
import './page-init';

// Start Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Metronic Core JavaScript functionality
function initMetronicCore() {
    // Initialize drawer functionality
    initDrawers();

    // Initialize menu functionality
    initMenus();

    // Initialize sticky headers
    initStickyHeaders();

    // Initialize modal functionality
    initModals();

    // Initialize tabs functionality
    initTabs();

    // Initialize agents "soldes" page specific behaviours
    initSoldesPage();
}

document.addEventListener('DOMContentLoaded', function() {
    initMetronicCore();
});

// Drawer functionality
function initDrawers() {
    const drawers = document.querySelectorAll('[data-kt-drawer]');

    drawers.forEach(drawer => {
        const toggles = document.querySelectorAll(`[data-kt-drawer-toggle="#${drawer.id}"]`);

        toggles.forEach(toggle => {
            // Retirer l'ancien listener s'il existe (pour éviter les doublons après AJAX)
            if (toggle._drawerListenerAttached && toggle._drawerClickHandler) {
                toggle.removeEventListener('click', toggle._drawerClickHandler);
                toggle._drawerListenerAttached = false;
            }
            
            // Créer un nouveau handler
            toggle._drawerClickHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                drawer.classList.toggle('hidden');
                drawer.classList.toggle('block');
            };
            
            // Attacher le nouveau listener
            toggle.addEventListener('click', toggle._drawerClickHandler);
            toggle._drawerListenerAttached = true;
        });
    });
}

// Menu functionality
function initMenus() {
    const menus = document.querySelectorAll('[data-kt-menu="true"]');

    menus.forEach(menu => {
        // Réinitialiser le flag pour permettre la réinitialisation après AJAX
        // (après navigation AJAX, les éléments sont nouveaux, donc pas besoin de vérifier)
        menu._menuInitialized = true;

        const items = menu.querySelectorAll('[data-kt-menu-item-toggle="dropdown"]');

        items.forEach(item => {
            const trigger = item.querySelector('[data-kt-menu-item-trigger="click"], [data-kt-menu-item-trigger="click|lg:hover"], .kt-menu-toggle');
            const dropdown = item.querySelector('.kt-menu-dropdown');

            if (trigger && dropdown) {
                // Retirer l'ancien listener s'il existe (pour éviter les doublons après AJAX)
                if (trigger._menuListenerAttached && trigger._menuClickHandler) {
                    trigger.removeEventListener('click', trigger._menuClickHandler);
                }
                
                // Créer un nouveau handler
                trigger._menuClickHandler = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Fermer tous les autres menus ouverts
                    document.querySelectorAll('.kt-menu-dropdown:not(.hidden)').forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.add('hidden');
                        }
                    });
                    
                    // Toggle le menu actuel
                    dropdown.classList.toggle('hidden');
                };
                
                trigger.addEventListener('click', trigger._menuClickHandler);
                trigger._menuListenerAttached = true;
            }
        });
    });
    
    // Gérer la fermeture des menus au clic extérieur (une seule fois au niveau du document)
    if (!document._menuOutsideClickHandler) {
        document._menuOutsideClickHandler = function(e) {
            // Si le clic n'est pas dans un menu, fermer tous les menus ouverts
            if (!e.target.closest('[data-kt-menu="true"]')) {
                document.querySelectorAll('.kt-menu-dropdown:not(.hidden)').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        };
        document.addEventListener('click', document._menuOutsideClickHandler);
    }
}

// Sticky header functionality
function initStickyHeaders() {
    const stickyElements = document.querySelectorAll('[data-kt-sticky="true"]');

    stickyElements.forEach(element => {
        const stickyClass = element.getAttribute('data-kt-sticky-class') || 'kt-sticky';
        const offset = parseInt(element.getAttribute('data-kt-sticky-offset')) || 0;

        window.addEventListener('scroll', function() {
            if (window.scrollY > offset) {
                element.classList.add(...stickyClass.split(' '));
            } else {
                element.classList.remove(...stickyClass.split(' '));
            }
        });
    });
}

// Modal functionality
function initModals() {
    // Utiliser la délégation d'événements pour fonctionner après AJAX
    // Attacher le handler une seule fois au niveau du document pour ouvrir les modals
    if (!document._modalToggleHandlerAttached) {
        document._modalToggleHandlerAttached = true;
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('[data-kt-modal-toggle]');
            if (!toggle) return;
            
            e.preventDefault();
            const modalId = toggle.getAttribute('data-kt-modal-toggle');
            const modal = document.querySelector(modalId);

            if (modal) {
                // Certains modals ont un style inline `display: none;`
                // Donc on ne peut pas se contenter de toggle les classes.
                const isHiddenByClass = modal.classList.contains('hidden');
                const isHiddenByStyle = (modal.style && modal.style.display === 'none');

                const shouldShow = isHiddenByClass || isHiddenByStyle;

                if (shouldShow) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                } else {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                }
            }
        });
    }
    
    // Handler pour fermer les modals (délégation d'événements)
    if (!document._modalDismissHandlerAttached) {
        document._modalDismissHandlerAttached = true;
        document.addEventListener('click', function(e) {
            const dismissBtn = e.target.closest('[data-kt-modal-dismiss="true"]');
            if (!dismissBtn) return;
            
            e.preventDefault();
            const modal = dismissBtn.closest('.kt-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        });
    }
}

// Tabs functionality (for data-kt-tabs groups, e.g. users grid toggle)
function initTabs() {
    const tabGroups = document.querySelectorAll('[data-kt-tabs="true"]');

    tabGroups.forEach(group => {
        const toggles = group.querySelectorAll('[data-kt-tab-toggle]');
        if (!toggles.length) return;

        const targets = Array.from(toggles)
            .map(toggle => document.querySelector(toggle.getAttribute('data-kt-tab-toggle')))
            .filter(Boolean);

        toggles.forEach((toggle, index) => {
            const targetSelector = toggle.getAttribute('data-kt-tab-toggle');
            const target = document.querySelector(targetSelector);
            if (!target) return;

            // État initial : premier onglet actif si aucun autre n'est marqué actif
            if (index === 0 && !group.querySelector('.active')) {
                toggle.classList.add('active');
                target.classList.remove('hidden');
            } else if (!toggle.classList.contains('active')) {
                target.classList.add('hidden');
            }

            toggle.addEventListener('click', function (e) {
                e.preventDefault();

                // Basculer l'état actif sur les boutons
                toggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Afficher uniquement le contenu ciblé dans ce groupe
                targets.forEach(t => t.classList.add('hidden'));
                if (target) {
                    target.classList.remove('hidden');
                }
            });
        });
    });
}

// Agents "soldes" page: dropdown toggle & empty-row layout fix
function initSoldesPage() {
    // Expose dropdown toggle globally so inline onclick works even after AJAX navigation
    if (typeof window.toggleDropdown !== 'function') {
        window.toggleDropdown = function(button) {
            const parentCell = button.closest('td');
            if (!parentCell) return;

            const dropdownContent = parentCell.querySelector('.dropdown-content');
            const icon = button.querySelector('i');

            if (dropdownContent) {
                dropdownContent.classList.toggle('hidden');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        };
    }

    const table = document.getElementById('soldes_table');
    if (!table) {
        return; // Not on the soldes page
    }

    const applyEmptyRowFix = () => {
        const emptyRow = table.querySelector('tbody tr.empty-row');
        if (!emptyRow) return;
        const td = emptyRow.querySelector('td');
        if (td && td.getAttribute('colspan') !== '8') {
            td.setAttribute('colspan', '8');
            td.style.width = '100%';
            td.style.border = 'none';
        }
    };

    // Apply once immediately
    applyEmptyRowFix();

    // Attach a single MutationObserver per table to keep colspan correct
    if (!table._soldesObserver) {
        const observer = new MutationObserver(() => {
            applyEmptyRowFix();
        });
        observer.observe(table, { childList: true, subtree: true });
        table._soldesObserver = observer;
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.kt-modal');

    modals.forEach(modal => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});

// Export functions for use in other modules
window.MetronicCore = {
    initDrawers,
    initMenus,
    initStickyHeaders,
    initModals,
    initTabs,
    initMetronicCore,
    initSoldesPage
};

// Exposer initSoldesPage globalement pour le système de réinitialisation automatique
window.initSoldesPage = initSoldesPage;

// Mettre à jour les classes actives du menu après navigation AJAX
function updateActiveMenuItems() {
    const currentUrl = window.location.pathname;
    const currentSearch = window.location.search;
    
    // Normaliser les URLs (retirer les slashes finaux)
    const normalizePath = (path) => path.replace(/\/+$/, '') || '/';
    const normalizedCurrentPath = normalizePath(currentUrl);
    
    // Retirer toutes les classes actives existantes
    document.querySelectorAll('.kt-menu-item-active').forEach(item => {
        item.classList.remove('kt-menu-item-active');
    });
    
    // Retirer aussi les classes show des accordions
    document.querySelectorAll('.kt-menu-item-show').forEach(item => {
        item.classList.remove('kt-menu-item-show');
    });
    
    // Fonction pour vérifier si un lien correspond exactement à l'URL actuelle
    function matchesUrlExactly(href, currentPath, currentSearch) {
        if (!href) return false;
        
        try {
            const linkUrl = new URL(href, window.location.origin);
            const linkPath = normalizePath(linkUrl.pathname);
            const linkSearch = linkUrl.search;
            
            // Correspondance exacte du path
            if (linkPath !== currentPath) {
                return false;
            }
            
            // Si le lien a des query params, vérifier qu'ils correspondent exactement
            if (linkSearch) {
                const linkParams = new URLSearchParams(linkSearch);
                const currentParams = new URLSearchParams(currentSearch);
                
                // Vérifier que tous les paramètres du lien sont présents dans l'URL actuelle
                for (const [key, value] of linkParams.entries()) {
                    if (currentParams.get(key) !== value) {
                        return false;
                    }
                }
            }
            
            return true;
        } catch (e) {
            // Si l'URL est relative, utiliser une approche simple
            const cleanHref = normalizePath(href);
            return currentPath === cleanHref;
        }
    }
    
    // Collecter tous les éléments de menu avec leurs informations
    const menuItems = [];
    document.querySelectorAll('#sidebar_menu .kt-menu-item').forEach(item => {
        // Certains items (comme \"Rapports\") sont directement des <a class=\"kt-menu-item\" href=\"...\">
        // D'autres ont un wrapper .kt-menu-item et un lien interne .kt-menu-link / .kt-menu-label.
        const link = item.matches('a.kt-menu-item[href]')
            ? item
            : item.querySelector('.kt-menu-link[href], .kt-menu-label[href]');

        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;
        
        // Vérifier si c'est un sous-menu (dans un accordion)
        const isSubMenu = !!item.closest('.kt-menu-accordion');
        
        menuItems.push({
            item: item,
            href: href,
            isSubMenu: isSubMenu
        });
    });
    
    // Trouver la correspondance exacte la plus précise
    // Priorité aux sous-menus car ils sont plus spécifiques
    let activeSubMenu = null;
    let activeMainMenu = null;
    
    menuItems.forEach(({ item, href, isSubMenu }) => {
        if (matchesUrlExactly(href, normalizedCurrentPath, currentSearch)) {
            if (isSubMenu) {
                // Les sous-menus ont la priorité car ils sont plus spécifiques
                activeSubMenu = item;
            } else if (!activeSubMenu) {
                // Menu principal seulement si aucun sous-menu ne correspond
                activeMainMenu = item;
            }
        }
    });
    
    // Utiliser le sous-menu si trouvé, sinon le menu principal
    const itemToActivate = activeSubMenu || activeMainMenu;
    
    if (itemToActivate) {
        itemToActivate.classList.add('kt-menu-item-active');
        
        // Si c'est un sous-menu, ouvrir et activer le parent accordion
        const parentAccordion = itemToActivate.closest('.kt-menu-item[data-kt-menu-item-toggle="accordion"]');
        if (parentAccordion) {
            parentAccordion.classList.add('kt-menu-item-active', 'kt-menu-item-show');
        }
    }
}

// Appeler la fonction au chargement initial
document.addEventListener('DOMContentLoaded', function() {
    updateActiveMenuItems();
});

// Mettre à jour après chaque navigation AJAX
document.addEventListener('ajax-content-loaded', function() {
    updateActiveMenuItems();
});

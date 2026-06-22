import './bootstrap';
import './toast';
import Alpine from 'alpinejs';
import './ajax-navigation';
// Importer les modules de cartes
import './maps/dashboard-month-map';
import './dashboard-evolution-chart';
import './dashboard-technique';
import './dashboard-securite';
import './maps/kiosques-map';
// Importer le système de permissions de menu
import './menu-permissions';
// Importer le système d'initialisation des pages
import './page-init';
import './pdf-preview';
import './grid-export';

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

function parseMenuOffset(value) {
    if (!value) return { x: 0, y: 4 };
    const parts = value.split(',').map(v => parseFloat(v.trim()));
    return { x: Number.isFinite(parts[0]) ? parts[0] : 0, y: Number.isFinite(parts[1]) ? parts[1] : 4 };
}

function restoreMenuDropdown(dropdown) {
    if (dropdown._menuOriginalParent && dropdown.parentElement === document.body) {
        dropdown._menuOriginalParent.appendChild(dropdown);
    }
}

function closeAllMenuDropdowns() {
    document.querySelectorAll('.kt-menu-dropdown.show').forEach(dropdown => {
        dropdown.classList.remove('show');
        dropdown.style.display = '';
        dropdown.style.position = '';
        dropdown.style.top = '';
        dropdown.style.right = '';
        dropdown.style.left = '';
        dropdown.style.width = '';
        dropdown.style.minWidth = '';
        dropdown.style.maxWidth = '';
        dropdown.style.zIndex = '';
        dropdown.style.margin = '';
        restoreMenuDropdown(dropdown);
        const parentItem = dropdown._menuOriginalParent || dropdown.closest('[data-kt-menu-item-toggle="dropdown"]');
        if (parentItem) {
            parentItem.classList.remove('show', 'kt-menu-item-dropdown');
        }
    });
}

function openMenuDropdown(trigger, item, dropdown) {
    dropdown._menuOriginalParent = item;

    item.classList.add('show', 'kt-menu-item-dropdown');

    if (dropdown.parentElement !== document.body) {
        document.body.appendChild(dropdown);
    }

    const rect = trigger.getBoundingClientRect();
    const placement = item.getAttribute('data-kt-menu-item-placement') || 'bottom-end';
    const offset = parseMenuOffset(item.getAttribute('data-kt-menu-item-offset'));

    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '99999';
    dropdown.style.margin = '0';
    dropdown.style.width = '175px';
    dropdown.style.minWidth = '175px';
    dropdown.style.maxWidth = '175px';
    dropdown.classList.add('show');
    dropdown.style.display = 'flex';

    const dropdownWidth = dropdown.offsetWidth || 175;
    const dropdownHeight = dropdown.offsetHeight || 0;

    let top = placement.includes('top')
        ? rect.top - dropdownHeight - offset.y
        : rect.bottom + offset.y;

    let left;
    if (placement.includes('end')) {
        left = rect.right - dropdownWidth + offset.x;
    } else if (placement.includes('start')) {
        left = rect.left + offset.x;
    } else {
        left = rect.left + (rect.width - dropdownWidth) / 2 + offset.x;
    }

    left = Math.max(8, Math.min(left, window.innerWidth - dropdownWidth - 8));
    top = Math.max(8, Math.min(top, window.innerHeight - dropdownHeight - 8));

    dropdown.style.top = `${top}px`;
    dropdown.style.left = `${left}px`;
    dropdown.style.right = 'auto';
    dropdown._menuTrigger = trigger;
    dropdown._menuItem = item;
}

function repositionOpenMenuDropdowns() {
    document.querySelectorAll('.kt-menu-dropdown.show').forEach(dropdown => {
        if (dropdown._menuTrigger && dropdown._menuItem) {
            openMenuDropdown(dropdown._menuTrigger, dropdown._menuItem, dropdown);
        }
    });
}

function cleanupPortaledMenus() {
    closeAllMenuDropdowns();
    document.querySelectorAll('body > .kt-menu-dropdown').forEach(el => el.remove());
}

// Menu functionality — délégation d'événements (compatible navigation AJAX)
function initMenus() {
    if (document._ktMenuDelegationInited) {
        return;
    }
    document._ktMenuDelegationInited = true;

    document.addEventListener('click', function(e) {
        const menuLink = e.target.closest('.kt-menu-dropdown .kt-menu-link');
        if (menuLink) {
            setTimeout(() => closeAllMenuDropdowns(), 0);
            return;
        }

        const trigger = e.target.closest('.kt-menu-toggle');
        if (trigger) {
            const item = trigger.closest('[data-kt-menu-item-toggle="dropdown"]');
            if (!item) return;
            const dropdown = item.querySelector('.kt-menu-dropdown');
            if (!dropdown) return;

            e.preventDefault();
            e.stopPropagation();

            const isOpen = dropdown.classList.contains('show');
            closeAllMenuDropdowns();
            if (!isOpen) {
                openMenuDropdown(trigger, item, dropdown);
            }
            return;
        }

        if (!e.target.closest('.kt-menu-dropdown')) {
            closeAllMenuDropdowns();
        }
    });

    if (!document._menuRepositionHandler) {
        document._menuRepositionHandler = repositionOpenMenuDropdowns;
        window.addEventListener('scroll', document._menuRepositionHandler, true);
        window.addEventListener('resize', document._menuRepositionHandler);
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

// Déplacer les modals dans body (évite le flou / mauvais z-index dans #content)
function portalModalsToBody(root = document) {
    root.querySelectorAll('[data-kt-modal="true"]').forEach(modalEl => {
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
    });
}

function cleanupOrphanModalBackdrops() {
    const hasOpenModal = document.querySelector('.kt-modal.open');
    if (!hasOpenModal) {
        document.querySelectorAll('.kt-modal-backdrop, [data-kt-modal-backdrop="true"]').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    }
}

// Modal functionality
function initModals() {
    portalModalsToBody();

    document.querySelectorAll('[data-kt-modal="true"]').forEach(modalEl => {
        if (modalEl._ktModalInstance) return;
        try {
            if (typeof KTModal !== 'undefined') {
                modalEl._ktModalInstance = new KTModal(modalEl);
            }
        } catch (e) {
            console.warn('KTModal init error:', e);
        }
    });
}

// Nettoyer les backdrops orphelins à la fermeture
if (!document._modalDismissListener) {
    document._modalDismissListener = true;
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-kt-modal-dismiss]')) {
            setTimeout(cleanupOrphanModalBackdrops, 350);
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            setTimeout(cleanupOrphanModalBackdrops, 350);
        }
    });
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
    initSoldesPage,
    portalModalsToBody,
    cleanupOrphanModalBackdrops,
    closeAllMenuDropdowns,
    cleanupPortaledMenus,
};

// Exposer initSoldesPage globalement pour le système de réinitialisation automatique
window.initSoldesPage = initSoldesPage;
window.portalModalsToBody = portalModalsToBody;
window.cleanupOrphanModalBackdrops = cleanupOrphanModalBackdrops;

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

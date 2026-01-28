import './bootstrap';
import Alpine from 'alpinejs';
import './ajax-navigation';
// Importer les modules de cartes
import './maps/dashboard-month-map';
import './maps/kiosques-map';

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
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                drawer.classList.toggle('hidden');
                drawer.classList.toggle('block');
            });
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

            if (trigger && dropdown && !trigger._menuListenerAttached) {
                trigger._menuListenerAttached = true;
                
                trigger.addEventListener('click', function(e) {
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
                });
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
    const modalToggles = document.querySelectorAll('[data-kt-modal-toggle]');

    modalToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-kt-modal-toggle');
            const modal = document.querySelector(modalId);

            if (modal) {
                modal.classList.toggle('hidden');
                modal.classList.toggle('flex');
            }
        });
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
    initSoldesPage
};

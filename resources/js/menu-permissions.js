/**
 * Filtrage du sidebar selon les permissions utilisateur.
 * Les permissions sont injectées côté serveur (zéro flash) ; l'API sert de fallback.
 */

let userPermissions = {
    routes: [],
    profils: [],
    permissions: {},
};

let permissionsReady = false;

function setSidebarLoading(isLoading) {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (!sidebarMenu) return;

    sidebarMenu.classList.toggle('kt-permissions-loading', isLoading);
}

function readEmbeddedPermissions() {
    const el = document.getElementById('menu-permissions-data');
    if (!el?.textContent) return null;

    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function setUserPermissions(data) {
    if (!data?.success) return false;

    userPermissions = {
        routes: data.routes || [],
        profils: data.profils || [],
        permissions: data.permissions || {},
    };

    return true;
}

async function fetchUserPermissions() {
    const response = await fetch('/api/my-permissions', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    });

    if (!response.ok) {
        return false;
    }

    const data = await response.json();
    return setUserPermissions(data);
}

async function loadUserPermissions({ showLoading = true, forceFetch = false } = {}) {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (!sidebarMenu) return;

    if (permissionsReady && !forceFetch) {
        applyMenuPermissions();
        return;
    }

    if (showLoading) {
        setSidebarLoading(true);
    }

    let loaded = false;

    if (!forceFetch) {
        const embedded = readEmbeddedPermissions();
        loaded = setUserPermissions(embedded);
    }

    if (!loaded) {
        try {
            loaded = await fetchUserPermissions();
        } catch (error) {
            console.error('Erreur lors du chargement des permissions:', error);
        }
    }

    if (loaded) {
        applyMenuPermissions();
        permissionsReady = true;
    }

    setSidebarLoading(false);
}

function checkMenuVisibility(href) {
    if (!userPermissions.routes || userPermissions.routes.length === 0) {
        return false;
    }

    if (href === '#' || !href || href.trim() === '') {
        return true;
    }

    const normalizedHref = normalizeUrl(href);
    const cleanHref = normalizedHref.startsWith('/') ? normalizedHref : '/' + normalizedHref;
    const hrefQuery = href.indexOf('?') >= 0 ? href.split('?')[1].split('#')[0] : '';
    const fullHref = hrefQuery ? cleanHref + '?' + hrefQuery : cleanHref;

    return userPermissions.routes.some((route) => {
        if (!route) return false;

        const normalizedRoute = normalizeUrl(route);
        const cleanRoute = normalizedRoute.startsWith('/') ? normalizedRoute : '/' + normalizedRoute;
        const routeQuery = route.indexOf('?') >= 0 ? route.split('?')[1].split('#')[0] : '';
        const fullRoute = routeQuery ? cleanRoute + '?' + routeQuery : cleanRoute;

        if (hrefQuery) {
            return fullHref === fullRoute;
        }

        if (cleanHref === cleanRoute) {
            return true;
        }

        return cleanRoute !== '/' && cleanRoute.length > 1 && cleanHref.startsWith(cleanRoute + '/');
    });
}

function normalizeUrl(url) {
    if (!url) return '';

    try {
        let pathname = '';

        if (url.startsWith('http://') || url.startsWith('https://')) {
            pathname = new URL(url).pathname;
        } else {
            pathname = url.split('?')[0].split('#')[0];
        }

        if (!pathname.startsWith('/')) {
            pathname = '/' + pathname;
        }

        if (pathname.length > 1 && pathname.endsWith('/')) {
            pathname = pathname.slice(0, -1);
        }

        return pathname;
    } catch {
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

function toggleMenuItem(element, visible) {
    if (!element) return;

    if (visible) {
        element.style.removeProperty('display');
        element.style.removeProperty('visibility');
        element.removeAttribute('data-permission-hidden');
        return;
    }

    element.style.display = 'none';
    element.style.visibility = 'hidden';
    element.setAttribute('data-permission-hidden', 'true');
}

function isMenuItemVisible(element) {
    return element && !element.hasAttribute('data-permission-hidden');
}

function isSectionHeadingItem(item) {
    return (
        item?.querySelector('.kt-menu-heading') &&
        !item.querySelector('a[href]') &&
        !item.hasAttribute('data-kt-menu-item-toggle')
    );
}

function getSidebarTopLevelItems(sidebarMenu) {
    return Array.from(sidebarMenu.children).filter((el) => {
        if (el.classList.contains('kt-sidebar-menu-skeleton') || el.tagName === 'SCRIPT') {
            return false;
        }

        return el.classList.contains('kt-menu-item');
    });
}

function hideEmptySectionHeadings(sidebarMenu) {
    const items = getSidebarTopLevelItems(sidebarMenu);

    items.forEach((item, index) => {
        if (!isSectionHeadingItem(item)) return;

        let hasVisibleItem = false;

        for (let i = index + 1; i < items.length; i++) {
            if (isSectionHeadingItem(items[i])) break;

            if (isMenuItemVisible(items[i])) {
                hasVisibleItem = true;
                break;
            }
        }

        toggleMenuItem(item, hasVisibleItem);
    });
}

function applyMenuPermissions() {
    const sidebarMenu = document.getElementById('sidebar_menu');
    if (!sidebarMenu) return;

    const menuLinks = sidebarMenu.querySelectorAll('a[href]');

    menuLinks.forEach((link) => {
        const href = link.getAttribute('href');
        if (!href) return;

        const isAuthorized = checkMenuVisibility(href);
        const menuItem = link.closest('.kt-menu-item');

        if (!menuItem) {
            toggleMenuItem(link, isAuthorized);
            return;
        }

        toggleMenuItem(menuItem, isAuthorized);
        toggleMenuItem(link, isAuthorized);
    });

    sidebarMenu.querySelectorAll('.kt-menu-item[data-kt-menu-item-toggle="accordion"]').forEach((accordion) => {
        const children = accordion.querySelectorAll('.kt-menu-accordion .kt-menu-item');
        const visibleChildren = Array.from(children).filter(
            (child) => !child.hasAttribute('data-permission-hidden'),
        );

        toggleMenuItem(accordion, children.length === 0 || visibleChildren.length > 0);
    });

    hideEmptySectionHeadings(sidebarMenu);
}

function initMenuPermissions() {
    loadUserPermissions({ showLoading: true });

    document.addEventListener('ajax-content-loaded', () => {
        applyMenuPermissions();
    });
}

window.MenuPermissions = {
    loadUserPermissions,
    checkMenuVisibility,
    applyMenuPermissions,
    initMenuPermissions,
    userPermissions,
    setSidebarLoading,
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMenuPermissions);
} else {
    initMenuPermissions();
}

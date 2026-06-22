import { openPdfPreviewModal } from './pdf-preview';

function getSelectedRowIds(tableSelector) {
    const table = document.querySelector(tableSelector);
    if (!table) {
        return [];
    }

    return [...table.querySelectorAll('[data-kt-datatable-row-check]:checked')]
        .map((checkbox) => checkbox.value)
        .filter((value) => value !== undefined && value !== null && String(value).trim() !== '');
}

export function buildExportUrl(baseUrl, ids) {
    const url = new URL(baseUrl, window.location.origin);
    const params = new URLSearchParams(url.search);

    [...params.keys()].forEach((key) => {
        if (key === 'ids' || key === 'ids[]') {
            params.delete(key);
        }
    });

    if (ids.length > 0) {
        ids.forEach((id) => params.append('ids[]', id));
    }

    const query = params.toString();

    return query ? `${url.pathname}?${query}` : url.pathname;
}

function bindGridExportElement(element) {
    if (element._gridExportBound) {
        return;
    }
    element._gridExportBound = true;

    const tableSelector = element.getAttribute('data-export-table');
    if (!tableSelector) {
        return;
    }

    element.addEventListener('click', (event) => {
        const baseUrl = element.getAttribute('data-pdf-url')
            || element.getAttribute('data-export-url')
            || element.getAttribute('href');

        if (!baseUrl) {
            return;
        }

        const ids = getSelectedRowIds(tableSelector);
        const exportUrl = buildExportUrl(baseUrl, ids);

        if (element.hasAttribute('data-pdf-preview')) {
            event.preventDefault();
            openPdfPreviewModal(exportUrl, element.getAttribute('data-pdf-title') || 'Export PDF');
            return;
        }

        if (element.tagName === 'A') {
            event.preventDefault();
            window.location.href = exportUrl;
        }
    });
}

export function initGridExport(root = document) {
    root.querySelectorAll('[data-export-table]').forEach(bindGridExportElement);
}

document.addEventListener('DOMContentLoaded', () => initGridExport());
document.addEventListener('ajax-content-loaded', () => initGridExport());

window.initGridExport = initGridExport;

export default initGridExport;

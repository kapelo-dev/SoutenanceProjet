function appendPreviewParam(url) {
    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}preview=1`;
}

function openPdfPreviewModal(exportUrl, title) {
    const modal = document.getElementById('modal_pdf_preview');
    const frame = document.getElementById('pdf_preview_frame');
    const downloadLink = document.getElementById('pdf_preview_download');
    const titleEl = document.getElementById('pdf_preview_title');

    if (!modal || !frame || !downloadLink) {
        window.open(exportUrl, '_blank');
        return;
    }

    if (titleEl) {
        titleEl.textContent = title ? `Aperçu — ${title}` : 'Aperçu du document PDF';
    }

    frame.src = 'about:blank';
    downloadLink.href = exportUrl;
    downloadLink.setAttribute('download', '');

    const previewUrl = appendPreviewParam(exportUrl);
    frame.src = previewUrl;

    if (typeof KTModal !== 'undefined') {
        let instance = KTModal.getInstance(modal);
        if (!instance) {
            instance = new KTModal(modal);
        }
        instance.show();
    } else {
        modal.classList.add('open');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    }
}

function initPdfPreviewButtons(root = document) {
    root.querySelectorAll('[data-pdf-preview]').forEach((button) => {
        if (button._pdfPreviewBound) {
            return;
        }
        button._pdfPreviewBound = true;

        button.addEventListener('click', (event) => {
            event.preventDefault();
            const url = button.getAttribute('data-pdf-url') || button.getAttribute('href');
            const title = button.getAttribute('data-pdf-title') || 'Export PDF';
            if (url) {
                openPdfPreviewModal(url, title);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => initPdfPreviewButtons());
document.addEventListener('ajax-content-loaded', (event) => {
    initPdfPreviewButtons(event.detail?.container || document);
});

window.openPdfPreviewModal = openPdfPreviewModal;
window.initPdfPreviewButtons = initPdfPreviewButtons;

export { openPdfPreviewModal, initPdfPreviewButtons };

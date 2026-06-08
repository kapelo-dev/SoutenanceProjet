<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_pdf_preview" style="display: none;">
    <div class="kt-modal-content w-full max-w-[960px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="pdf_preview_title">Aperçu du document PDF</h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-0" style="min-height: 70vh;">
            <iframe id="pdf_preview_frame" title="Aperçu PDF" class="w-full border-0" style="height: 70vh; min-height: 480px;"></iframe>
        </div>
        <div class="kt-modal-footer">
            <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Fermer</button>
            <a id="pdf_preview_download" href="#" class="kt-btn kt-btn-primary" download>
                <i class="ki-filled ki-file-down"></i>
                Télécharger le PDF
            </a>
        </div>
    </div>
</div>

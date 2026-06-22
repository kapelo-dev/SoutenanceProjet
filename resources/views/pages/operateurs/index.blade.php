@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Opérateurs Mobile Money
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Gestion des opérateurs de mobile money (YAS, Flooz, Orange Money, etc.).
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            {{-- <button class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouvel_operateur">
                <i class="ki-filled ki-plus"></i>
                Nouvel Opérateur
            </button> --}}
        </div>
    </div>
</div>
<!-- End of Container -->
<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header py-5 flex-wrap gap-2">
            <div class="flex items-center gap-5 ml-auto">
                <label class="kt-input">
                    <i class="ki-filled ki-magnifier"></i>
                    <input data-kt-datatable-search="#operateurs_table" placeholder="Rechercher un opérateur" type="text" value=""/>
                </label>
                
            </div>
        </div>
        <div class="kt-card-content">
            @if($operateurs->count())
                <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="operateurs_table" style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr>
                                    <th class="min-w-[200px]" style="width: 25%;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">
                                                Opérateur
                                            </span>
                                            <span class="kt-table-col-sort"></span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]" style="width: 15%;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">
                                                Code
                                            </span>
                                            <span class="kt-table-col-sort"></span>
                                        </span>
                                    </th>
                                    <th class="min-w-[100px]" style="width: 12%;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">
                                                Ordre
                                            </span>
                                            <span class="kt-table-col-sort"></span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]" style="width: 15%;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">
                                                Statut
                                            </span>
                                            <span class="kt-table-col-sort"></span>
                                        </span>
                                    </th>
                                    <th class="min-w-[160px]" style="width: 18%;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">
                                                Date d'ajout
                                            </span>
                                            <span class="kt-table-col-sort"></span>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($operateurs as $operateur)
                            <tr data-statut="{{ $operateur->statut }}">
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        @if($operateur->logo)
                                            <div class="flex-shrink-0">
                                                <img class="h-9 w-9 rounded-full object-cover" src="{{ asset('storage/' . $operateur->logo) }}" alt="{{ $operateur->libelle }}"/>
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 h-9 w-9 rounded-full flex items-center justify-center text-white font-semibold text-sm" style="background-color: {{ $operateur->couleur ?? '#6366f1' }};">
                                                {{ strtoupper(substr($operateur->code, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-foreground">{{ $operateur->libelle }}</span>
                                            @if($operateur->couleur)
                                                <span class="text-xs text-secondary-foreground" style="color: {{ $operateur->couleur }};">
                                                    {{ $operateur->couleur }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-medium text-foreground">{{ $operateur->code }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-foreground">{{ $operateur->ordre ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-{{ $operateur->statut === 'actif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($operateur->statut) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm text-secondary-foreground">
                                        {{ $operateur->created_at ? $operateur->created_at->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <div class="flex items-center gap-2 order-2 md:order-1">
                        Afficher
                        <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        par page
                    </div>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span data-kt-datatable-info="true">
                        </span>
                        <div class="kt-datatable-pagination" data-kt-datatable-pagination="true">
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="flex flex-col items-center gap-3 max-w-md text-center">
                        <i class="ki-filled ki-information-2 text-4xl text-secondary-foreground"></i>
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-sm font-semibold text-foreground">Aucun opérateur</span>
                            <span class="text-xs text-secondary-foreground">
                                Aucun opérateur mobile money n'a été enregistré pour le moment.
                            </span>
                        </div>
                        {{-- <button class="kt-btn kt-btn-primary kt-btn-sm" data-kt-modal-toggle="#modal_nouvel_operateur">
                            <i class="ki-filled ki-plus"></i>
                            Créer le premier opérateur
                        </button> --}}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- End of Container -->
@endsection

@push('scripts')
<script>
(function() {

    // Exposer la fonction globalement pour qu'elle soit appelée après navigation AJAX
    window.initOperateursPage = function() {
        // Réinitialiser les menus Metronic pour cette page
        setTimeout(() => {
            // Utiliser MetronicCore si disponible
            if (window.MetronicCore && typeof window.MetronicCore.initMenus === 'function') {
                window.MetronicCore.initMenus();
            }
        }, 200);

    // Filtre des opérateurs actifs (éviter les doublons d'event listeners)
    const filterActifs = document.getElementById('filter_actifs');
    if (filterActifs && !filterActifs._operateursListenerAttached) {
        filterActifs._operateursListenerAttached = true;
        filterActifs.addEventListener('change', function() {
            const table = document.getElementById('operateurs_table');
            if (!table) {
                return;
            }
            const rows = table.querySelectorAll('tbody tr[data-statut]');
            
            rows.forEach(row => {
                if (this.checked) {
                    // Afficher uniquement les actifs
                    if (row.getAttribute('data-statut') === 'actif') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    // Afficher tous
                    row.style.display = '';
                }
    });
    }





    // Réinitialiser le formulaire de création (éviter les doublons)
    const createModal = document.getElementById('modal_nouvel_operateur');
    if (createModal && !createModal._operateursListenerAttached) {
        createModal._operateursListenerAttached = true;
        createModal.addEventListener('hidden', function() {
            document.getElementById('form_nouvel_operateur').reset();
            document.getElementById('create_logo_preview').innerHTML = '';
            document.querySelectorAll('#modal_nouvel_operateur .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
        });
    }

    // Prévisualisation du logo lors de la sélection (éviter les doublons)
    const createLogoInput = document.getElementById('create_logo');
    if (createLogoInput && !createLogoInput._operateursListenerAttached) {
        createLogoInput._operateursListenerAttached = true;
        createLogoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('create_logo_preview').innerHTML = `<img class="h-20 w-20 rounded-full object-cover" src="${e.target.result}" alt="Logo preview"/>`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    }; // fin initOperateursPage

    // Appeler immédiatement au chargement initial
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initOperateursPage, 200);
        });
    } else {
        setTimeout(initOperateursPage, 200);
    }
    
    // Réinitialiser après navigation AJAX
    document.addEventListener('ajax-content-loaded', function() {
        if (document.getElementById('operateurs_table')) {
            setTimeout(initOperateursPage, 300);
        }
    });
})();
</script>
@endpush

<!-- Modal Nouvel Opérateur -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_nouvel_operateur" style="display: none;">
    <div class="kt-modal-content max-w-[600px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                Nouvel Opérateur
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="form_nouvel_operateur" enctype="multipart/form-data">
            <div class="kt-modal-body">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Code <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input" type="text" name="code" id="create_code" placeholder="Ex: YAS" required />
                        <span class="text-xs text-destructive hidden" id="error_create_code"></span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Libellé <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input" type="text" name="libelle" id="create_libelle" placeholder="Ex: Mixx by YAS" required />
                        <span class="text-xs text-destructive hidden" id="error_create_libelle"></span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Statut <span class="text-destructive">*</span>
                            </label>
                            <select class="kt-select" name="statut" id="create_statut" required>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                            <span class="text-xs text-destructive hidden" id="error_create_statut"></span>
                        </div>
                        
                        <!-- Ordre généré automatiquement côté serveur -->
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Couleur
                        </label>
                        <div class="flex items-center gap-3">
                            <input class="kt-input flex-1" type="text" name="couleur" id="create_couleur" placeholder="#FF6B00" pattern="^#[0-9A-Fa-f]{6}$" />
                            <input type="color" id="create_couleur_picker" class="h-10 w-20 rounded border border-border" />
                        </div>
                        <span class="text-xs text-secondary-foreground">Format hexadécimal (ex: #FF6B00)</span>
                        <span class="text-xs text-destructive hidden" id="error_create_couleur"></span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Logo
                        </label>
                        <div class="flex items-center gap-5">
                            <div id="create_logo_preview" class="flex-shrink-0"></div>
                            <input class="kt-input flex-1" type="file" name="logo" id="create_logo" accept="image/*" />
                        </div>
                        <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG, GIF (max 2MB)</span>
                        <span class="text-xs text-destructive hidden" id="error_create_logo"></span>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary" id="btn_save_operateur">
                    <i class="ki-filled ki-check"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Synchroniser le color picker avec l'input couleur
document.addEventListener('DOMContentLoaded', function() {
    const createColorInput = document.getElementById('create_couleur');
    const createColorPicker = document.getElementById('create_couleur_picker');
    if (createColorInput && createColorPicker) {
        createColorInput.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                createColorPicker.value = this.value;
            }
        });
        createColorPicker.addEventListener('input', function() {
            createColorInput.value = this.value;
        });
    }

    // Soumission du formulaire de création
    const createForm = document.getElementById('form_nouvel_operateur');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('btn_save_operateur');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Enregistrement...';
            
            // Réinitialiser les erreurs
            document.querySelectorAll('#modal_nouvel_operateur .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            
            fetch('{{ route("operateurs.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    AppToast.reload(data.message || 'Opérateur créé avec succès.', 'success');
                    return;
                } else {
                    // Afficher les erreurs
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorEl = document.getElementById('error_create_' + key);
                            if (errorEl) {
                                errorEl.textContent = data.errors[key][0];
                                errorEl.classList.remove('hidden');
                            }
                        });
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                AppToast.error('Une erreur est survenue lors de l\'enregistrement.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

});
</script>

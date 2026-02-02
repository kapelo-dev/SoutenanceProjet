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
            <button class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouvel_operateur">
                <i class="ki-filled ki-plus"></i>
                Nouvel Opérateur
            </button>
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
                <label class="kt-label whitespace-nowrap">
                    Opérateurs Actifs
                    <input class="kt-switch kt-switch-sm" name="filter_actifs" id="filter_actifs" type="checkbox" value="1"/>
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
                                    <th class="w-[50px] text-center">
                                        <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox"/>
                                    </th>
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
                                    <th class="w-[50px]"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($operateurs as $operateur)
                            <tr data-statut="{{ $operateur->statut }}">
                                <td class="text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $operateur->id }}"/>
                                </td>
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
                                <td>
                                    <div class="kt-menu" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                            data-kt-menu-item-placement="bottom-end"
                                            data-kt-menu-item-placement-rtl="bottom-start"
                                            data-kt-menu-item-toggle="dropdown"
                                            data-kt-menu-item-trigger="click">
                                            <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]"
                                                data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link view-operateur" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $operateur->id }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-eye"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            Voir les détails
                                                        </span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link edit-operateur" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $operateur->id }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-pencil"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            Modifier
                                                        </span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link toggle-status" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $operateur->id }}"
                                                        data-statut="{{ $operateur->statut }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-{{ $operateur->statut === 'actif' ? 'cross' : 'check' }}"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            {{ $operateur->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                                                        </span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-separator"></div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link delete-operateur" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $operateur->id }}"
                                                        data-url="{{ route('operateurs.destroy', $operateur) }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-trash"></i>
                                                        </span>
                                                        <span class="kt-menu-title text-destructive">
                                                            Supprimer
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                        <button class="kt-btn kt-btn-primary kt-btn-sm" data-kt-modal-toggle="#modal_nouvel_operateur">
                            <i class="ki-filled ki-plus"></i>
                            Créer le premier opérateur
                        </button>
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
document.addEventListener('DOMContentLoaded', function() {
    // Filtre des opérateurs actifs
    const filterActifs = document.getElementById('filter_actifs');
    if (filterActifs) {
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

    // Toggle statut depuis le menu déroulant (délégation d'événements)
    document.addEventListener('click', function(e) {
        const toggleLink = e.target.closest('.toggle-status');
        if (toggleLink) {
            e.preventDefault();
            e.stopPropagation();
            const id = toggleLink.getAttribute('data-id');
            
            fetch(`/operateurs/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'icône et le badge de statut
                    const row = toggleLink.closest('tr');
                    if (row) {
                        const statutCell = row.querySelector('td:nth-child(5)');
                        const badge = statutCell ? statutCell.querySelector('.kt-badge') : null;
                        
                        if (badge) {
                            // Mettre à jour le badge
                            badge.className = `kt-badge kt-badge-${data.statut === 'actif' ? 'success' : 'danger'}`;
                            badge.textContent = data.statut.charAt(0).toUpperCase() + data.statut.slice(1);
                        }
                        
                        // Mettre à jour l'attribut data-statut
                        row.setAttribute('data-statut', data.statut);
                    }
                    
                    // Mettre à jour l'icône et le texte du lien dans le menu
                    const icon = toggleLink.querySelector('.kt-menu-icon i');
                    const title = toggleLink.querySelector('.kt-menu-title');
                    if (icon) {
                        icon.className = `ki-filled ki-${data.statut === 'actif' ? 'cross' : 'check'}`;
                    }
                    if (title) {
                        title.textContent = data.statut === 'actif' ? 'Désactiver' : 'Activer';
                    }
                    toggleLink.setAttribute('data-statut', data.statut);
                    
                    // Afficher un message de succès
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la mise à jour du statut.');
            });
        }
    });

    // Gestion de la suppression
    const deleteLinks = document.querySelectorAll('.delete-operateur');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            
            if (confirm('Êtes-vous sûr de vouloir supprimer cet opérateur ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Définir les fonctions AVANT les event listeners
    // Fonction pour charger et afficher les détails d'un opérateur
    window.loadOperateurDetails = function(id) {
        console.log('loadOperateurDetails appelé avec ID:', id);
        const modal = document.getElementById('modal_view_operateur');
        if (!modal) {
            console.error('Modal de visualisation introuvable');
            alert('Modal de visualisation introuvable');
            return;
        }
        
        console.log('Chargement des données pour l\'opérateur:', id);
        
        fetch(`/operateurs/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Réponse reçue:', response.status);
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.success && data.operateur) {
                // Remplir le modal de visualisation
                document.getElementById('view_code').textContent = data.operateur.code || '-';
                document.getElementById('view_libelle').textContent = data.operateur.libelle || '-';
                const statutText = (data.operateur.statut || 'inactif').charAt(0).toUpperCase() + (data.operateur.statut || 'inactif').slice(1);
                const statutEl = document.getElementById('view_statut');
                statutEl.textContent = statutText;
                statutEl.className = `kt-badge kt-badge-${data.operateur.statut === 'actif' ? 'success' : 'danger'}`;
                document.getElementById('view_ordre').textContent = data.operateur.ordre || '-';
                const couleurEl = document.getElementById('view_couleur');
                couleurEl.textContent = data.operateur.couleur || '-';
                if (data.operateur.couleur) {
                    couleurEl.style.color = data.operateur.couleur;
                }
                
                const logoContainer = document.getElementById('view_logo_container');
                if (data.operateur.logo) {
                    logoContainer.innerHTML = `<img class="h-20 w-20 rounded-full object-cover" src="/storage/${data.operateur.logo}" alt="${data.operateur.libelle}"/>`;
                } else {
                    const code = (data.operateur.code || '').substring(0, 2).toUpperCase();
                    const couleur = data.operateur.couleur || '#6366f1';
                    logoContainer.innerHTML = `<div class="h-20 w-20 rounded-full flex items-center justify-center text-white font-semibold text-lg" style="background-color: ${couleur};">${code}</div>`;
                }
                
                // Statistiques
                if (data.stats) {
                    document.getElementById('view_transactions_total').textContent = data.stats.transactions_total || 0;
                    document.getElementById('view_montant_total').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(data.stats.montant_total || 0);
                    document.getElementById('view_transactions_mois').textContent = data.stats.transactions_mois || 0;
                    document.getElementById('view_montant_mois').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(data.stats.montant_mois || 0);
                }
                
                // Ouvrir le modal
                console.log('Ouverture du modal...');
                if (typeof KTModal !== 'undefined') {
                    let modalInstance = KTModal.getInstance(modal);
                    if (!modalInstance) {
                        modalInstance = new KTModal(modal);
                    }
                    modalInstance.show();
                    console.log('Modal ouvert via KTModal');
                } else {
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                    console.log('Modal ouvert via style direct');
                }
            } else {
                console.error('Données invalides:', data);
                throw new Error('Données invalides');
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            alert('Une erreur est survenue lors du chargement des données: ' + error.message);
        });
    }

    // Fonction pour charger et afficher le formulaire d'édition
    window.loadOperateurEdit = function(id) {
        console.log('loadOperateurEdit appelé avec ID:', id);
        const modal = document.getElementById('modal_edit_operateur');
        if (!modal) {
            console.error('Modal d\'édition introuvable');
            alert('Modal d\'édition introuvable');
            return;
        }
        
        // Afficher un indicateur de chargement
        const submitBtn = document.getElementById('btn_update_operateur');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Chargement...';
        
        console.log('Chargement des données pour l\'édition de l\'opérateur:', id);
        
        fetch(`/operateurs/${id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Réponse reçue pour édition:', response.status);
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues pour édition:', data);
            if (data.success && data.operateur) {
                // Remplir le formulaire d'édition
                document.getElementById('edit_operateur_id').value = data.operateur.id;
                document.getElementById('edit_code').value = data.operateur.code || '';
                document.getElementById('edit_libelle').value = data.operateur.libelle || '';
                document.getElementById('edit_statut').value = data.operateur.statut || 'actif';
                document.getElementById('edit_ordre').value = data.operateur.ordre || '';
                document.getElementById('edit_couleur').value = data.operateur.couleur || '';
                
                // Synchroniser le color picker
                const colorPicker = document.getElementById('edit_couleur_picker');
                if (colorPicker && data.operateur.couleur) {
                    colorPicker.value = data.operateur.couleur;
                }
                
                const logoPreview = document.getElementById('edit_logo_preview');
                if (data.operateur.logo) {
                    logoPreview.innerHTML = `<img class="h-20 w-20 rounded-full object-cover" src="/storage/${data.operateur.logo}" alt="${data.operateur.libelle}"/>`;
                } else {
                    const code = (data.operateur.code || '').substring(0, 2).toUpperCase();
                    const couleur = data.operateur.couleur || '#6366f1';
                    logoPreview.innerHTML = `<div class="h-20 w-20 rounded-full flex items-center justify-center text-white font-semibold text-lg" style="background-color: ${couleur};">${code}</div>`;
                }
                
                // Réinitialiser les erreurs
                document.querySelectorAll('#modal_edit_operateur .text-destructive').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Ouvrir le modal
                console.log('Ouverture du modal d\'édition...');
                if (typeof KTModal !== 'undefined') {
                    let modalInstance = KTModal.getInstance(modal);
                    if (!modalInstance) {
                        modalInstance = new KTModal(modal);
                    }
                    modalInstance.show();
                    console.log('Modal d\'édition ouvert via KTModal');
                } else {
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                    console.log('Modal d\'édition ouvert via style direct');
                }
            } else {
                console.error('Données invalides pour édition:', data);
                throw new Error('Données invalides');
            }
        })
        .catch(error => {
            console.error('Erreur complète lors de l\'édition:', error);
            alert('Une erreur est survenue lors du chargement des données: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // Vérifier que les fonctions sont bien définies
    console.log('Fonctions définies:', {
        loadOperateurDetails: typeof window.loadOperateurDetails,
        loadOperateurEdit: typeof window.loadOperateurEdit
    });

    // Event listeners pour voir les détails et modifier (délégation d'événements)
    // Utiliser capture phase pour intercepter avant que le menu ne se ferme
    document.addEventListener('click', function(e) {
        // Voir les détails
        const viewLink = e.target.closest('.view-operateur');
        if (viewLink) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const id = viewLink.getAttribute('data-id');
            console.log('Clic détecté sur voir détails, ID:', id);
            if (id && typeof window.loadOperateurDetails === 'function') {
                console.log('Appel de loadOperateurDetails avec ID:', id);
                try {
                    window.loadOperateurDetails(id);
                } catch (error) {
                    console.error('Erreur lors de l\'appel de loadOperateurDetails:', error);
                    alert('Erreur: ' + error.message);
                }
            } else {
                console.error('ID manquant ou fonction non définie. ID:', id, 'Fonction:', typeof window.loadOperateurDetails);
                alert('Erreur: Fonction loadOperateurDetails non disponible');
            }
            return false;
        }
        
        // Modifier
        const editLink = e.target.closest('.edit-operateur');
        if (editLink) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const id = editLink.getAttribute('data-id');
            console.log('Clic détecté sur modifier, ID:', id);
            if (id && typeof window.loadOperateurEdit === 'function') {
                console.log('Appel de loadOperateurEdit avec ID:', id);
                try {
                    window.loadOperateurEdit(id);
                } catch (error) {
                    console.error('Erreur lors de l\'appel de loadOperateurEdit:', error);
                    alert('Erreur: ' + error.message);
                }
            } else {
                console.error('ID manquant ou fonction non définie. ID:', id, 'Fonction:', typeof window.loadOperateurEdit);
                alert('Erreur: Fonction loadOperateurEdit non disponible');
            }
            return false;
        }
    }, true); // Utiliser capture phase

    // Réinitialiser le formulaire de création
    const createModal = document.getElementById('modal_nouvel_operateur');
    if (createModal) {
        createModal.addEventListener('hidden', function() {
            document.getElementById('form_nouvel_operateur').reset();
            document.getElementById('create_logo_preview').innerHTML = '';
            document.querySelectorAll('#modal_nouvel_operateur .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
        });
    }

    // Prévisualisation du logo lors de la sélection
    const createLogoInput = document.getElementById('create_logo');
    if (createLogoInput) {
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

    const editLogoInput = document.getElementById('edit_logo');
    if (editLogoInput) {
        editLogoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_logo_preview').innerHTML = `<img class="h-20 w-20 rounded-full object-cover" src="${e.target.result}" alt="Logo preview"/>`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
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
                <button type="button" class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">
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

<!-- Modal Voir Opérateur -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_view_operateur" style="display: none;">
    <div class="kt-modal-content max-w-[700px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                Détails de l'Opérateur
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body">
            <div class="flex flex-col gap-5">
                <div class="flex items-center gap-5 pb-5 border-b border-border">
                    <div id="view_logo_container" class="flex-shrink-0"></div>
                    <div class="flex flex-col gap-1">
                        <h4 class="text-lg font-semibold text-foreground" id="view_libelle"></h4>
                        <span class="text-sm text-secondary-foreground" id="view_code"></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-label text-xs text-secondary-foreground">Statut</label>
                        <span id="view_statut" class="kt-badge"></span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-label text-xs text-secondary-foreground">Ordre</label>
                        <span class="text-sm text-foreground" id="view_ordre"></span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-label text-xs text-secondary-foreground">Couleur</label>
                        <span class="text-sm text-foreground" id="view_couleur"></span>
                    </div>
                </div>
                
                <div class="border-t border-border pt-5">
                    <h5 class="text-base font-semibold text-foreground mb-4">Statistiques</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="kt-card">
                            <div class="kt-card-content p-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-secondary-foreground">Transactions Total</span>
                                    <span class="text-xl font-semibold text-foreground" id="view_transactions_total">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card">
                            <div class="kt-card-content p-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-secondary-foreground">Montant Total</span>
                                    <span class="text-xl font-semibold text-foreground" id="view_montant_total">0 XOF</span>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card">
                            <div class="kt-card-content p-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-secondary-foreground">Transactions ce Mois</span>
                                    <span class="text-xl font-semibold text-foreground" id="view_transactions_mois">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card">
                            <div class="kt-card-content p-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-secondary-foreground">Montant ce Mois</span>
                                    <span class="text-xl font-semibold text-foreground" id="view_montant_mois">0 XOF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-modal-footer">
            <button type="button" class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">
                Fermer
            </button>
        </div>
    </div>
</div>

<!-- Modal Modifier Opérateur -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_edit_operateur" style="display: none;">
    <div class="kt-modal-content max-w-[600px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                Modifier l'Opérateur
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="form_edit_operateur" enctype="multipart/form-data">
            <input type="hidden" name="operateur_id" id="edit_operateur_id" />
            <div class="kt-modal-body">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Code <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input" type="text" name="code" id="edit_code" required />
                        <span class="text-xs text-destructive hidden" id="error_edit_code"></span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Libellé <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input" type="text" name="libelle" id="edit_libelle" required />
                        <span class="text-xs text-destructive hidden" id="error_edit_libelle"></span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Statut <span class="text-destructive">*</span>
                            </label>
                            <select class="kt-select" name="statut" id="edit_statut" required>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                            <span class="text-xs text-destructive hidden" id="error_edit_statut"></span>
                        </div>
                        
                        <!-- Ordre affiché uniquement en lecture, géré automatiquement -->
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Couleur
                        </label>
                        <div class="flex items-center gap-3">
                            <input class="kt-input flex-1" type="text" name="couleur" id="edit_couleur" pattern="^#[0-9A-Fa-f]{6}$" />
                            <input type="color" id="edit_couleur_picker" class="h-10 w-20 rounded border border-border" />
                        </div>
                        <span class="text-xs text-secondary-foreground">Format hexadécimal (ex: #FF6B00)</span>
                        <span class="text-xs text-destructive hidden" id="error_edit_couleur"></span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Logo
                        </label>
                        <div class="flex items-center gap-5">
                            <div id="edit_logo_preview" class="flex-shrink-0"></div>
                            <input class="kt-input flex-1" type="file" name="logo" id="edit_logo" accept="image/*" />
                        </div>
                        <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG, GIF (max 2MB)</span>
                        <span class="text-xs text-destructive hidden" id="error_edit_logo"></span>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary" id="btn_update_operateur">
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

    const editColorInput = document.getElementById('edit_couleur');
    const editColorPicker = document.getElementById('edit_couleur_picker');
    if (editColorInput && editColorPicker) {
        editColorInput.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                editColorPicker.value = this.value;
            }
        });
        editColorPicker.addEventListener('input', function() {
            editColorInput.value = this.value;
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
                    window.location.reload();
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
                alert('Une erreur est survenue lors de l\'enregistrement.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Soumission du formulaire d'édition
    const editForm = document.getElementById('form_edit_operateur');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const operateurId = document.getElementById('edit_operateur_id').value;
            formData.append('_method', 'PUT');
            
            const submitBtn = document.getElementById('btn_update_operateur');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Enregistrement...';
            
            // Réinitialiser les erreurs
            document.querySelectorAll('#modal_edit_operateur .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            
            fetch(`/operateurs/${operateurId}`, {
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
                    window.location.reload();
                } else {
                    // Afficher les erreurs
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorEl = document.getElementById('error_edit_' + key);
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
                alert('Une erreur est survenue lors de l\'enregistrement.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>

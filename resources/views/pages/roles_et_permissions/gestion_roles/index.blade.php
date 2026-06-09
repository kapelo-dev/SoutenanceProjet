@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         Roles
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                 </div>
       </div>
       <div class="flex items-center gap-2.5">
        <button class="kt-btn kt-btn-outline" data-kt-modal-toggle="#modal_nouveau_role">
          Nouveau Role
        </button>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <!-- Container -->
     <div class="kt-container-fixed">
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-7.5">
       @forelse($roles as $role)
       @php
           // Déterminer les couleurs et icônes selon le niveau ou le libellé
           $iconClass = 'ki-setting';
           $iconColor = 'text-primary';
           $strokeClass = 'stroke-primary/10 fill-primary/5';
           
           if (stripos($role->libelle, 'admin') !== false || $role->niveau == 0) {
               $iconClass = 'ki-setting';
               $iconColor = 'text-primary';
               $strokeClass = 'stroke-primary/10 fill-primary/5';
           } elseif (stripos($role->libelle, 'viewer') !== false || stripos($role->libelle, 'lecteur') !== false) {
               $iconClass = 'ki-eye';
               $iconColor = 'text-primary';
               $strokeClass = 'stroke-primary/10 fill-primary/5';
           } elseif (stripos($role->libelle, 'superviseur') !== false || $role->niveau == 2) {
               $iconClass = 'ki-chart-line-up-2';
               $iconColor = 'text-violet-500';
               $strokeClass = 'stroke-violet-100 dark:stroke-violet-950 ring-violet-50 dark:ring-violet-950 fill-violet-50 dark:fill-violet-950/30';
           } elseif (stripos($role->libelle, 'comptable') !== false) {
               $iconClass = 'ki-people';
               $iconColor = 'text-green-500';
               $strokeClass = 'stroke-green-200 dark:stroke-green-950 dark:ring-green-950 fill-green-50 dark:fill-green-950/30';
           } elseif (stripos($role->libelle, 'agent') !== false || $role->niveau >= 3) {
               $iconClass = 'ki-face-id';
               $iconColor = 'text-green-500';
               $strokeClass = 'stroke-green-200 dark:stroke-green-950 dark:ring-green-950 fill-green-50 dark:fill-green-950/30';
           }
           
           $isDefault = $role->niveau <= 1;
           $roleType = $isDefault ? 'Default role' : ($role->niveau >= 3 ? 'Remote role' : 'Default role');
           $usersCount = $role->users_count ?? $role->utilisateurs()->count();
       @endphp
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5" data-role-id="{{ $role->id }}">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full {{ $strokeClass }}" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled {{ $iconClass }} text-xl {{ $iconColor }}">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <span class="text-base font-medium text-mono hover:text-primary mb-px">
            {{ $role->libelle }}
           </span>
           <span class="text-sm text-secondary-foreground">
            {{ $roleType }}
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#" onclick="viewRoleDetails({{ $role->id }})">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#" onclick="editRole({{ $role->id }}, '{{ $role->libelle }}', '{{ addslashes($role->description) }}', {{ $role->niveau }})">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-pencil">
               </i>
              </span>
              <span class="kt-menu-title">
               Modifier
              </span>
             </a>
            </div>
            <div class="kt-menu-separator"></div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#" onclick="deleteRole({{ $role->id }}, '{{ $role->libelle }}')">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-trash">
               </i>
              </span>
              <span class="kt-menu-title">
               Supprimer
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         {{ $role->description ?: 'Aucune description disponible.' }}
        </p>
        <span class="text-sm text-foreground">
         {{ $usersCount }} {{ $usersCount <= 1 ? 'person' : 'people' }}
        </span>
       </div>
       @empty
       <div class="col-span-full">
        <div class="kt-card">
         <div class="kt-card-content py-20 text-center">
          <p class="text-secondary-foreground">Aucun rôle trouvé.</p>
         </div>
        </div>
       </div>
       @endforelse
       <style>
        .add-new-bg {
		background-image: url('assets/media/images/2600x1200/bg-4.png');
	}
	.dark .add-new-bg {
		background-image: url('assets/media/images/2600x1200/bg-4-dark.png');
	}
       </style>
       <button class="kt-card border-2 border-dashed border-primary/10 bg-center bg-[length:600px] bg-no-repeat add-new-bg cursor-pointer hover:border-primary/20 transition-colors" data-kt-modal-toggle="#modal_nouveau_role">
        <div class="kt-card-content grid items-center">
         <div class="flex flex-col gap-3">
          <div class="flex justify-center pt-5">
           <div class="relative size-[60px] shrink-0">
            <svg class="w-full h-full stroke-primary/10 fill-primary/5" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
             <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
             </path>
             <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
             </path>
            </svg>
            <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
             <i class="ki-filled ki-rocket text-2xl text-primary">
             </i>
            </div>
           </div>
          </div>
          <div class="flex flex-col text-center">
           <span class="text-lg font-medium text-mono hover:text-primary mb-px">
            Ajouter un Nouveau Role
           </span>
           <span class="text-sm text-secondary-foreground">
            Ajouter un nouveau rôle pour gérer les permissions et les accès des utilisateurs.
           </span>
          </div>
         </div>
        </div>
       </button>
      </div>
     </div>

     <!-- Modal Nouveau/Modifier Rôle -->
     <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_nouveau_role">
      <div class="kt-modal-content max-w-[600px]">
       <div class="kt-modal-header">
        <h3 class="kt-modal-title" id="modal_role_title">
         Nouveau Rôle
        </h3>
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
         <i class="ki-filled ki-cross"></i>
        </button>
       </div>
       <div class="kt-modal-body">
        <form id="form_nouveau_role" class="flex flex-col gap-5">
         <input type="hidden" id="role_id" name="role_id" />
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Nom du rôle <span class="text-destructive">*</span>
          </label>
          <input class="kt-input" type="text" name="libelle" id="role_libelle" placeholder="Ex: Administrateur" required />
          <span class="text-xs text-destructive hidden" id="error_libelle"></span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Description
          </label>
          <textarea class="kt-input" rows="3" name="description" id="role_description" placeholder="Description du rôle..."></textarea>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Niveau hiérarchique <span class="text-destructive">*</span>
          </label>
          <select class="kt-select" name="niveau" id="role_niveau" data-kt-select="true" required>
           <option value="0">0 - Super Admin (Plus haut niveau)</option>
           <option value="1">1 - Admin</option>
           <option value="2">2 - Superviseur/Manager</option>
           <option value="3">3 - Agent/Utilisateur</option>
           <option value="4">4 - Utilisateur standard</option>
           <option value="5">5 - Visiteur</option>
          </select>
          <span class="text-xs text-secondary-foreground">Le niveau détermine la hiérarchie (0 = plus élevé)</span>
         </div>
        </form>
       </div>
       <div class="kt-modal-footer">
        <button class="kt-btn kt-btn-ghost" data-kt-modal-dismiss="true">
         Annuler
        </button>
        <button class="kt-btn kt-btn-primary" id="btn_save_role" onclick="saveRole()">
         <i class="ki-filled ki-check"></i>
         <span id="btn_save_text">Créer le rôle</span>
        </button>
       </div>
      </div>
     </div>
     <!-- End Modal Nouveau/Modifier Rôle -->

<script>
window.currentRoleId = window.currentRoleId ?? null;

function resetModal() {
    // Réinitialiser le formulaire
    document.getElementById('form_nouveau_role').reset();
    document.getElementById('role_id').value = '';
    document.getElementById('error_libelle').classList.add('hidden');
    
    // Réinitialiser le titre et le bouton
    document.getElementById('modal_role_title').textContent = 'Nouveau Rôle';
    document.getElementById('btn_save_text').textContent = 'Créer le rôle';
    document.getElementById('btn_save_role').setAttribute('onclick', 'saveRole()');
}

function editRole(roleId, libelle, description, niveau) {
    // Charger les données dans le formulaire
    document.getElementById('role_id').value = roleId;
    document.getElementById('role_libelle').value = libelle;
    document.getElementById('role_description').value = description || '';
    document.getElementById('role_niveau').value = niveau;
    
    // Mettre à jour le titre et le bouton
    document.getElementById('modal_role_title').textContent = 'Modifier le Rôle';
    document.getElementById('btn_save_text').textContent = 'Enregistrer';
    document.getElementById('btn_save_role').setAttribute('onclick', 'saveRole()');
    
    // Ouvrir le modal en utilisant l'attribut data-kt-modal-toggle
    const modalToggle = document.createElement('button');
    modalToggle.setAttribute('data-kt-modal-toggle', '#modal_nouveau_role');
    modalToggle.style.display = 'none';
    document.body.appendChild(modalToggle);
    modalToggle.click();
    document.body.removeChild(modalToggle);
    
    // Attendre un peu pour que le modal s'ouvre avant de mettre à jour les valeurs
    setTimeout(function() {
        // S'assurer que les valeurs sont bien définies
        document.getElementById('role_id').value = roleId;
        document.getElementById('role_libelle').value = libelle;
        document.getElementById('role_description').value = description || '';
        
        // Mettre à jour le select
        const niveauSelect = document.getElementById('role_niveau');
        niveauSelect.value = niveau;
        
        // Si le framework utilise un plugin pour les selects, déclencher un événement de changement
        if (niveauSelect.dispatchEvent) {
            niveauSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Réinitialiser le select personnalisé si KTSelect est utilisé
        if (typeof KTSelect !== 'undefined' && niveauSelect) {
            const selectInstance = KTSelect.getInstance(niveauSelect);
            if (selectInstance) {
                selectInstance.setValue(niveau);
            }
        }
    }, 200);
}

function saveRole() {
    const form = document.getElementById('form_nouveau_role');
    const formData = new FormData(form);
    const roleId = document.getElementById('role_id').value;
    const data = Object.fromEntries(formData);
    
    // Retirer role_id des données à envoyer
    delete data.role_id;
    
    // Validation côté client
    if (!data.libelle || data.libelle.trim() === '') {
        document.getElementById('error_libelle').textContent = 'Le nom du rôle est requis.';
        document.getElementById('error_libelle').classList.remove('hidden');
        return;
    }
    
    document.getElementById('error_libelle').classList.add('hidden');
    
    // Désactiver le bouton pendant le traitement
    const submitBtn = document.getElementById('btn_save_role');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> ' + (roleId ? 'Enregistrement...' : 'Création...');
    
    // Déterminer l'URL et la méthode selon si c'est une création ou une modification
    const url = roleId 
        ? `{{ route("roles.update", ":id") }}`.replace(':id', roleId)
        : '{{ route("roles.store") }}';
    const method = roleId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fermer le modal
            const modal = document.querySelector('#modal_nouveau_role');
            if (modal) {
                // Utiliser l'API du modal si disponible
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                }
            }
            
            // Réinitialiser le formulaire
            resetModal();
            
            AppToast.reload(
                data.message || (roleId ? 'Rôle modifié avec succès.' : 'Rôle créé avec succès.'),
                'success'
            );
            return;
        } else {
            // Afficher les erreurs
            if (data.errors) {
                if (data.errors.libelle) {
                    document.getElementById('error_libelle').textContent = data.errors.libelle[0];
                    document.getElementById('error_libelle').classList.remove('hidden');
                }
            } else {
                AppToast.error('Erreur : ' + (data.message || 'Une erreur est survenue'));
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        AppToast.error('Une erreur est survenue lors de ' + (roleId ? 'la modification' : 'la création') + ' du rôle.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function deleteRole(roleId, roleName) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le rôle "${roleName}" ?\n\nCette action est irréversible.`)) {
        return;
    }
    
    fetch(`{{ route("roles.destroy", ":id") }}`.replace(':id', roleId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            AppToast.reload(data.message || 'Rôle supprimé avec succès.', 'success');
            return;
        } else {
            AppToast.error('Erreur : ' + (data.message || 'Impossible de supprimer ce rôle.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        AppToast.error('Une erreur est survenue lors de la suppression.');
    });
}

function viewRoleDetails(roleId) {
    fetch(`{{ route("roles.show", ":id") }}`.replace(':id', roleId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.role) {
            const role = data.role;
            const details = `
Nom: ${role.libelle}
Description: ${role.description || 'Aucune description'}
Niveau: ${role.niveau}
Utilisateurs: ${role.users_count || 0}
            `;
            AppToast.info(details);
        } else {
            AppToast.error('Erreur lors du chargement des détails.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        AppToast.error('Une erreur est survenue.');
    });
}

// Réinitialiser le formulaire quand le modal se ferme
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.querySelector('#modal_nouveau_role');
    if (modal) {
        modal.addEventListener('kt-modal-dismiss', function() {
            document.getElementById('form_nouveau_role').reset();
            document.getElementById('error_libelle').classList.add('hidden');
        });
    }
    
    const editModal = document.querySelector('#modal_edit_role');
    if (editModal) {
        editModal.addEventListener('kt-modal-dismiss', function() {
            document.getElementById('form_edit_role').reset();
            document.getElementById('error_edit_libelle').classList.add('hidden');
        });
    }
});

window.initGestionRolesPage = function() {
    if (window.MetronicCore?.initMenus) {
        window.MetronicCore.initMenus();
    }
    if (window.MetronicCore?.initModals) {
        window.MetronicCore.initModals();
    }
};

if (document.querySelector('[data-role-id]')) {
    window.initGestionRolesPage();
}
</script>
@endsection

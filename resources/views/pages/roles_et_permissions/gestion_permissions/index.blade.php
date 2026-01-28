@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         Permissions
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Gestion des permissions : assigner les routes aux rôles.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <button class="kt-btn kt-btn-primary" onclick="saveAllPermissions()">
         <i class="ki-filled ki-check"></i>
         Enregistrer les modifications
        </button>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <div class="kt-container-fixed">
      <!-- begin: grid -->
      <div class="grid grid-cols-1 xl:grid-cols-1 gap-5 lg:gap-7.5">
       <div class="col-span-2">
        <div class="flex flex-col gap-5 lg:gap-7.5">
         <div class="kt-card">
          <div class="kt-card-header gap-2">
           <h3 class="kt-card-title">
            Matrice des Permissions
           </h3>
           <div class="flex gap-5">
            <button class="kt-btn kt-btn-outline shrink-0" onclick="selectAllPermissions()">
             Tout sélectionner
            </button>
            <button class="kt-btn kt-btn-outline shrink-0" onclick="deselectAllPermissions()">
             Tout désélectionner
            </button>
           </div>
          </div>
          <div class="kt-card-table kt-scrollable-x-auto">
           <table class="kt-table" style="table-layout: fixed; width: 100%;">
            <thead>
             <tr>
              <th class="text-left text-muted-foreground font-normal min-w-[250px] sticky left-0 bg-background z-10" style="width: 25%;">
               Route / Lien
              </th>
              @foreach($roles as $role)
              <th class="min-w-[120px] text-secondary-foreground font-normal text-center" style="width: {{ 75 / $roles->count() }}%;">
               <span class="text-sm font-medium">{{ $role->libelle }}</span>
              </th>
              @endforeach
             </tr>
            </thead>
            <tbody class="text-mono font-medium">
             @forelse($liens as $lien)
             <tr>
              <td class="py-5.5! sticky left-0 bg-background z-10">
               <span class="font-medium text-sm">{{ $lien->libelle }}</span>
              </td>
              @foreach($roles as $role)
              @php
                  $permissionKey = $role->id . '_' . $lien->id;
                  $hasPermission = in_array($permissionKey, $permissions);
              @endphp
              <td class="py-5.5! text-center">
               <input 
                class="kt-checkbox kt-checkbox-sm permission-checkbox" 
                type="checkbox" 
                data-profil-id="{{ $role->id }}" 
                data-lien-id="{{ $lien->id }}"
                data-permission-key="{{ $permissionKey }}"
                {{ $hasPermission ? 'checked' : '' }}
                onchange="togglePermission({{ $role->id }}, {{ $lien->id }}, this.checked)"
               />
              </td>
              @endforeach
             </tr>
             @empty
             <tr>
              <td colspan="{{ $roles->count() + 1 }}" class="text-center py-10 text-muted-foreground">
               Aucun lien/route trouvé.
              </td>
             </tr>
             @endforelse
            </tbody>
           </table>
          </div>
          <div class="kt-card-footer justify-end py-7.5 gap-2.5">
           <button class="kt-btn kt-btn-outline" onclick="deselectAllPermissions()">
            Réinitialiser
           </button>
           <button class="kt-btn kt-btn-primary" onclick="saveAllPermissions()">
            <i class="ki-filled ki-check"></i>
            Enregistrer les modifications
           </button>
          </div>
         </div>
       </div>
      </div>
      <!-- end: grid -->
     </div>

<script>
let permissionsChanged = false;

function togglePermission(profilId, lienId, granted) {
    permissionsChanged = true;
    
    // Optionnel: sauvegarder immédiatement (décommentez si vous voulez une sauvegarde automatique)
    // savePermission(profilId, lienId, granted);
}

function savePermission(profilId, lienId, granted) {
    fetch('{{ route("permissions.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            profil_id: profilId,
            lien_id: lienId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Erreur:', data.message);
            // Revenir à l'état précédent en cas d'erreur
            const checkbox = document.querySelector(`[data-profil-id="${profilId}"][data-lien-id="${lienId}"]`);
            if (checkbox) {
                checkbox.checked = !granted;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revenir à l'état précédent en cas d'erreur
        const checkbox = document.querySelector(`[data-profil-id="${profilId}"][data-lien-id="${lienId}"]`);
        if (checkbox) {
            checkbox.checked = !granted;
        }
    });
}

function saveAllPermissions() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const permissions = [];
    
    checkboxes.forEach(checkbox => {
        permissions.push({
            profil_id: parseInt(checkbox.getAttribute('data-profil-id')),
            lien_id: parseInt(checkbox.getAttribute('data-lien-id')),
            granted: checkbox.checked
        });
    });
    
    // Désactiver le bouton pendant le traitement
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Enregistrement...';
    
    fetch('{{ route("permissions.save-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ permissions: permissions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            permissionsChanged = false;
            // Afficher un message de succès
            alert('Permissions enregistrées avec succès !');
            // Optionnel: recharger la page pour s'assurer de la synchronisation
            // window.location.reload();
        } else {
            alert('Erreur: ' + (data.message || 'Une erreur est survenue lors de l\'enregistrement.'));
        }
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de l\'enregistrement.');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    permissionsChanged = true;
}

function deselectAllPermissions() {
    if (!confirm('Êtes-vous sûr de vouloir désélectionner toutes les permissions ?')) {
        return;
    }
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    permissionsChanged = true;
}

// Avertir avant de quitter si des modifications non sauvegardées
window.addEventListener('beforeunload', function(e) {
    if (permissionsChanged) {
        e.preventDefault();
        e.returnValue = 'Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter ?';
    }
});
</script>

<style>
/* Assurer que la première colonne reste fixe lors du scroll horizontal */
.kt-scrollable-x-auto {
    position: relative;
}

.kt-table thead th.sticky,
.kt-table tbody td.sticky {
    position: sticky;
    left: 0;
    background-color: var(--background);
    z-index: 10;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
}

.kt-table tbody td.sticky {
    z-index: 5;
}
</style>
@endsection

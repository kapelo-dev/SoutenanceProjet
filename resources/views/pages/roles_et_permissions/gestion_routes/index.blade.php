@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         Routes
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Vue d'ensemble de toutes les routes et liens disponibles dans le système.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <button class="kt-btn kt-btn-outline" data-kt-modal-toggle="#modal_nouvelle_route">
         Nouvelle route
        </button>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <div class="kt-container-fixed">
      <div class="grid gap-5 lg:gap-7.5">
       <div class="kt-card">
        <div class="kt-card-header">
         <h3 class="kt-card-title">
          Liste des Routes
         </h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 lg:grid-cols-2 gap-5 py-5 lg:py-7.5">
         @forelse($liens as $lien)
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="{{ $lien->icone ?? 'ki-filled ki-category' }} text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             {{ $lien->libelle }}
             @if($lien->parent)
              <span class="text-xs text-muted-foreground">({{ $lien->parent->libelle }})</span>
             @endif
            </span>
            <span class="text-sm text-secondary-foreground">
             @if($lien->route)
              Route: <code class="text-xs bg-muted px-1 py-0.5 rounded">{{ $lien->route }}</code>
             @elseif($lien->url)
              URL: <code class="text-xs bg-muted px-1 py-0.5 rounded">{{ $lien->url }}</code>
             @else
              Menu parent (sans route directe)
             @endif
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="lien_{{ $lien->id }}" type="checkbox" value="{{ $lien->id }}" {{ $lien->visible ? 'checked' : '' }}/>
         </div>
         @empty
         <div class="col-span-full text-center py-10">
          <p class="text-muted-foreground">Aucune route disponible.</p>
         </div>
         @endforelse
        </div>
        <div class="kt-card-footer justify-center">
         <button class="kt-btn kt-btn-outline" data-kt-modal-toggle="#modal_nouvelle_route">
          Nouvelle Route
         </button>
        </div>
       </div>
     
      </div>
     </div>

     <!-- Modal Nouvelle Route -->
     <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_nouvelle_route">
      <div class="kt-modal-content max-w-[600px]">
       <div class="kt-modal-header">
        <h3 class="kt-modal-title">
         Nouvelle Route
        </h3>
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
         <i class="ki-filled ki-cross"></i>
        </button>
       </div>
       <div class="kt-modal-body">
        <form id="form_nouvelle_route" class="flex flex-col gap-5">
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Libellé <span class="text-destructive">*</span>
          </label>
          <input class="kt-input" type="text" name="libelle" id="route_libelle" placeholder="Ex: Dashboard" required />
          <span class="text-xs text-destructive hidden" id="error_libelle"></span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Route Laravel
          </label>
          <input class="kt-input" type="text" name="route" id="route_route" placeholder="Ex: dashboard ou transactions.index" />
          <span class="text-xs text-secondary-foreground">Nom de la route Laravel (optionnel si URL est fournie)</span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           URL
          </label>
          <input class="kt-input" type="text" name="url" id="route_url" placeholder="Ex: https://example.com" />
          <span class="text-xs text-secondary-foreground">URL complète pour les liens externes (optionnel si route est fournie)</span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Icône
          </label>
          <input class="kt-input" type="text" name="icone" id="route_icone" placeholder="Ex: ki-filled ki-home-3" />
          <span class="text-xs text-secondary-foreground">Classe CSS de l'icône Metronic (ex: ki-filled ki-home-3)</span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Menu Parent
          </label>
          <select class="kt-select" name="parent_id" id="route_parent_id" data-kt-select="true">
           <option value="">Aucun (Menu principal)</option>
           @foreach($menusParents as $parent)
            <option value="{{ $parent->id }}">{{ $parent->libelle }}</option>
           @endforeach
          </select>
          <span class="text-xs text-secondary-foreground">Sélectionner un menu parent si c'est un sous-menu</span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Ordre d'affichage
          </label>
          <input class="kt-input" type="number" name="ordre" id="route_ordre" value="0" min="0" />
          <span class="text-xs text-secondary-foreground">Ordre d'affichage dans le menu (plus petit = affiché en premier)</span>
         </div>
         <div class="flex items-center gap-2">
          <input class="kt-switch" type="checkbox" name="visible" id="route_visible" checked />
          <label class="kt-label" for="route_visible">
           Visible dans le menu
          </label>
         </div>
        </form>
       </div>
       <div class="kt-modal-footer">
        <button class="kt-btn kt-btn-ghost" data-kt-modal-dismiss="true">
         Annuler
        </button>
        <button class="kt-btn kt-btn-primary" id="btn_save_route" onclick="saveRoute()">
         <i class="ki-filled ki-check"></i>
         <span>Créer la route</span>
        </button>
       </div>
      </div>
     </div>
     <!-- End Modal Nouvelle Route -->

<script>
function resetRouteModal() {
    // Réinitialiser le formulaire
    document.getElementById('form_nouvelle_route').reset();
    document.getElementById('route_visible').checked = true;
    document.getElementById('route_ordre').value = '0';
    document.getElementById('error_libelle').classList.add('hidden');
}

function saveRoute() {
    const form = document.getElementById('form_nouvelle_route');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Convertir visible en boolean
    data.visible = document.getElementById('route_visible').checked ? 1 : 0;
    
    // Convertir parent_id en null si vide
    if (!data.parent_id || data.parent_id === '') {
        data.parent_id = null;
    }
    
    // Convertir ordre en integer
    data.ordre = parseInt(data.ordre) || 0;
    
    // Validation côté client
    if (!data.libelle || data.libelle.trim() === '') {
        document.getElementById('error_libelle').textContent = 'Le libellé est requis.';
        document.getElementById('error_libelle').classList.remove('hidden');
        return;
    }
    
    // Vérifier qu'au moins route ou url est fourni
    if ((!data.route || data.route.trim() === '') && (!data.url || data.url.trim() === '')) {
        document.getElementById('error_libelle').textContent = 'Vous devez fournir soit une route Laravel, soit une URL.';
        document.getElementById('error_libelle').classList.remove('hidden');
        return;
    }
    
    // Nettoyer les champs vides
    if (!data.route || data.route.trim() === '') {
        delete data.route;
    }
    if (!data.url || data.url.trim() === '') {
        delete data.url;
    }
    if (!data.icone || data.icone.trim() === '') {
        delete data.icone;
    }
    
    document.getElementById('error_libelle').classList.add('hidden');
    
    // Désactiver le bouton pendant le traitement
    const submitBtn = document.getElementById('btn_save_route');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Création...';
    
    fetch('{{ route("routes.store") }}', {
        method: 'POST',
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
            const modal = document.querySelector('#modal_nouvelle_route');
            if (modal) {
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
            resetRouteModal();
            
            // Recharger la page pour afficher la nouvelle route
            window.location.reload();
        } else {
            // Afficher les erreurs
            if (data.errors) {
                if (data.errors.libelle) {
                    document.getElementById('error_libelle').textContent = data.errors.libelle[0];
                    document.getElementById('error_libelle').classList.remove('hidden');
                } else if (data.errors.route) {
                    document.getElementById('error_libelle').textContent = data.errors.route[0];
                    document.getElementById('error_libelle').classList.remove('hidden');
                } else {
                    // Afficher la première erreur trouvée
                    const firstError = Object.values(data.errors)[0];
                    if (firstError && firstError.length > 0) {
                        document.getElementById('error_libelle').textContent = firstError[0];
                        document.getElementById('error_libelle').classList.remove('hidden');
                    }
                }
            } else if (data.message) {
                alert('Erreur: ' + data.message);
            }
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de la création de la route.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Réinitialiser le formulaire quand le modal se ferme
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.querySelector('#modal_nouvelle_route');
    if (modal) {
        modal.addEventListener('kt-modal-dismiss', function() {
            resetRouteModal();
        });
    }
});
</script>
@endsection
